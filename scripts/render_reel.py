#!/usr/bin/env python3
"""Render a Reel block from Dragonframe sources to .mov via hardlinks + ffmpeg.

Reads the Dragonframe take.xml EDL, resolves the VirtualFrame range to JPEG paths,
hardlinks them into a staging dir under ~/mt3/reels/YYYY/MM/DD-slug/, and invokes
ffmpeg to produce the output .mov.
"""

import argparse
import json
import os
import re
import subprocess
import sys
import xml.etree.ElementTree as ET
from pathlib import Path

DGN_ROOT = Path.home() / "mt3" / "00010_002.dgn"
REELS_ROOT = Path.home() / "mt3" / "reels"
PROJECT_CODE = "00010_002"

# Dragonframe encodes "hidden / deleted" vframes by setting the high bit of the
# file attribute (value >= 2^30). The JPEG stays on disk but Dragonframe's
# playback engine skips it. We mirror that behaviour.
HIDDEN_FLAG = 1 << 30


def parse_edl(take_xml_path: Path) -> list[tuple[int, int]]:
    """Return ordered [(vframe, file), ...] from <scen:edl>/<scen:vframe>."""
    tree = ET.parse(take_xml_path)
    root = tree.getroot()
    ns_match = re.match(r"\{(.+)\}", root.tag)
    if not ns_match:
        raise ValueError(f"No namespace on root element in {take_xml_path}")
    ns = {"scen": ns_match.group(1)}
    vframes = root.findall(".//scen:edl/scen:vframe", ns)
    if not vframes:
        raise ValueError(f"No <scen:vframe> entries in {take_xml_path}")
    return [(int(v.get("vframe")), int(v.get("file"))) for v in vframes]


def take_folder(take_id: int) -> Path:
    return DGN_ROOT / f"{PROJECT_CODE}_Take_{take_id:02d}"


def jpeg_path(take_id: int, exposure: str, file_index: int) -> Path:
    exposure_folder = take_folder(take_id) / f"{PROJECT_CODE}_{take_id:02d}_{exposure}"
    return exposure_folder / f"{PROJECT_CODE}_{take_id:02d}_{exposure}_{file_index:04d}.jpg"


def resolve_block(
    take_id: int,
    vframe_start: int,
    vframe_end: int,
    exposure: str,
    edl: list[tuple[int, int]],
) -> tuple[list[Path], int]:
    """Slice the EDL by vframe range; return (paths, hidden_count).

    Hidden vframes (file & HIDDEN_FLAG) are skipped — they correspond to
    Dragonframe "deleted" frames that stay on disk but never play.
    """
    by_vframe = dict(edl)
    paths = []
    missing_vframes = []
    hidden_count = 0
    for vf in range(vframe_start, vframe_end + 1):
        if vf not in by_vframe:
            missing_vframes.append(vf)
            continue
        file_idx = by_vframe[vf]
        if file_idx & HIDDEN_FLAG:
            hidden_count += 1
            continue
        paths.append(jpeg_path(take_id, exposure, file_idx))
    if missing_vframes:
        raise ValueError(
            f"Vframes not in EDL for take {take_id}: {missing_vframes[:10]}"
            f"{'...' if len(missing_vframes) > 10 else ''}"
        )
    return paths, hidden_count


def stage(jpeg_paths: list[Path], staging_dir: Path) -> None:
    """Hardlink each JPEG into staging_dir/frameNNN.jpg (1-indexed)."""
    staging_dir.mkdir(parents=True, exist_ok=True)
    for i, src in enumerate(jpeg_paths, start=1):
        if not src.exists():
            raise FileNotFoundError(f"Source JPEG missing: {src}")
        dst = staging_dir / f"frame{i:03d}.jpg"
        if dst.exists() or dst.is_symlink():
            dst.unlink()
        os.link(src, dst)


def render(staging_dir: Path, output_mov: Path, fps: int = 12) -> None:
    """Invoke ffmpeg over the staging dir."""
    cmd = [
        "ffmpeg", "-y",
        "-framerate", str(fps),
        "-i", str(staging_dir / "frame%03d.jpg"),
        "-c:v", "libx264",
        "-pix_fmt", "yuv420p",
        str(output_mov),
    ]
    subprocess.run(cmd, check=True)


def build_paths(blocks: list[dict], edl_cache: dict[int, list[tuple[int, int]]]) -> tuple[list[Path], int]:
    """Resolve a sequence of blocks to a flat ordered JPEG list. Returns (paths, total_hidden)."""
    all_paths: list[Path] = []
    total_hidden = 0
    for i, b in enumerate(blocks, 1):
        take = b["take"]
        if take not in edl_cache:
            edl_cache[take] = parse_edl(take_folder(take) / "take.xml")
        paths, hidden = resolve_block(
            take, b["vframe_start"], b["vframe_end"], b.get("exposure", "X1"), edl_cache[take]
        )
        size = b["vframe_end"] - b["vframe_start"] + 1
        print(f"  Block {i}: take {take}, vframes {b['vframe_start']}-{b['vframe_end']} {b.get('exposure', 'X1')} "
              f"→ {len(paths)} played ({hidden} hidden of {size})")
        all_paths.extend(paths)
        total_hidden += hidden
    return all_paths, total_hidden


def main() -> int:
    p = argparse.ArgumentParser(description=__doc__)
    p.add_argument("--spec", help="Path to JSON spec (overrides CLI block args)")
    p.add_argument("--take", type=int, help="Take id (single-block mode)")
    p.add_argument("--vframe-start", type=int)
    p.add_argument("--vframe-end", type=int)
    p.add_argument("--exposure", default="X1", choices=["X1", "X2", "X3"])
    p.add_argument("--slug")
    p.add_argument("--date", help="YYYY-MM-DD")
    p.add_argument("--fps", type=int, default=12)
    p.add_argument("--stage-only", action="store_true",
                   help="Hardlink staging only; skip ffmpeg render")
    args = p.parse_args()

    if args.spec:
        spec = json.loads(Path(args.spec).read_text())
        slug = spec["slug"]
        date = spec["date"]
        fps = spec.get("fps", 12)
        blocks = spec["blocks"]
    else:
        for required in ("take", "vframe_start", "vframe_end", "slug", "date"):
            if getattr(args, required) is None:
                p.error(f"--{required.replace('_', '-')} required when --spec not given")
        slug = args.slug
        date = args.date
        fps = args.fps
        blocks = [{
            "take": args.take,
            "vframe_start": args.vframe_start,
            "vframe_end": args.vframe_end,
            "exposure": args.exposure,
        }]

    y, m, d = date.split("-")
    staging_dir = REELS_ROOT / y / m / f"{d}-{slug}"
    output_mov = staging_dir.parent / f"{d}-{slug}.mov"

    print(f"Resolving {len(blocks)} block(s):")
    paths, hidden = build_paths(blocks, edl_cache={})
    print(f"Total: {len(paths)} JPEGs ({hidden} hidden skipped)")

    print(f"Staging hardlinks in: {staging_dir}")
    stage(paths, staging_dir)
    print(f"Staged {len(paths)} hardlinks.")

    if args.stage_only:
        print("(--stage-only set; skipping ffmpeg)")
        return 0

    print(f"Rendering: {output_mov}")
    render(staging_dir, output_mov, fps=fps)
    print(f"Done: {output_mov}")
    return 0


if __name__ == "__main__":
    sys.exit(main())
