#!/usr/bin/env python3
"""Upload a video to one specific YouTube account, unattended.

The account label selects a self-contained credential file (client creds +
that account's own refresh_token), so there is no way to cross-post: each
label can only reach its own channel.

    python3 scripts/youtube_upload.py mt3_account \\
        --file ~/mt3/reels/2026/05/17-take8.mov \\
        --title "MT3 — Take 8 outer spiral" \\
        --description "Workers gluing the outer spiral." \\
        --privacy unlisted

Run scripts/youtube_auth.py for the account first (fills refresh_token).

Requires:
    pip install --user google-auth-oauthlib google-api-python-client

Quota: videos.insert costs ~1600 units; the default 10,000/day project quota
is ~6 uploads/day shared across BOTH accounts (quota is per Cloud project).
"""

import argparse
import json
import sys
from pathlib import Path

from google.auth.transport.requests import Request
from google.oauth2.credentials import Credentials
from googleapiclient.discovery import build
from googleapiclient.errors import HttpError
from googleapiclient.http import MediaFileUpload

CONFIG_ROOT = Path(__file__).resolve().parent.parent / "config"

ACCOUNTS = {
    "robs_main": CONFIG_ROOT / "marble_track_vids_robs_main_YT_account.json",
    "mt3_account": CONFIG_ROOT / "post_mt3_vids_to_YT_mt3_account.json",
}

PRIVACY = ("private", "unlisted", "public")


def credentials_for(label: str) -> tuple[Credentials, dict]:
    """Build refreshed Credentials for an account label."""
    if label not in ACCOUNTS:
        sys.exit(f"Unknown account '{label}'. Choose one of: {', '.join(ACCOUNTS)}")
    path = ACCOUNTS[label]
    if not path.exists():
        sys.exit(f"Missing config file: {path}")
    cfg = json.loads(path.read_text())

    rt = cfg.get("refresh_token", "")
    if not rt or rt.startswith("<"):
        sys.exit(f"No refresh_token in {path.name} — run "
                 f"`python3 scripts/youtube_auth.py {label}` first.")

    oc = cfg["oauth_client"]
    creds = Credentials(
        token=None,
        refresh_token=rt,
        client_id=oc["client_id"],
        client_secret=oc["client_secret"],
        token_uri=oc.get("token_uri", "https://oauth2.googleapis.com/token"),
        scopes=cfg.get("scopes") or ["https://www.googleapis.com/auth/youtube.upload"],
    )
    creds.refresh(Request())
    return creds, cfg


def upload(args: argparse.Namespace) -> None:
    video = Path(args.file).expanduser()
    if not video.is_file():
        sys.exit(f"Video not found: {video}")

    creds, _ = credentials_for(args.account)
    yt = build("youtube", "v3", credentials=creds)

    body = {
        "snippet": {
            "title": args.title,
            "description": args.description,
            "categoryId": args.category,
            "tags": [t.strip() for t in args.tags.split(",") if t.strip()]
            if args.tags else [],
        },
        "status": {
            "privacyStatus": args.privacy,
            "selfDeclaredMadeForKids": False,
        },
    }

    media = MediaFileUpload(str(video), chunksize=8 * 1024 * 1024, resumable=True)
    request = yt.videos().insert(part="snippet,status", body=body, media_body=media)

    print(f"Uploading {video.name} → '{args.account}' ({args.privacy}) ...")
    response = None
    while response is None:
        status, response = request.next_chunk()
        if status:
            print(f"  {int(status.progress() * 100)}%")

    video_id = response["id"]
    print(f"\n✓ Uploaded: https://youtu.be/{video_id}")
    print(f"  Studio:   https://studio.youtube.com/video/{video_id}/edit")


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("account", choices=sorted(ACCOUNTS),
                        help="Which YouTube account to upload to.")
    parser.add_argument("--file", required=True, help="Path to the video file.")
    parser.add_argument("--title", required=True)
    parser.add_argument("--description", default="")
    parser.add_argument("--privacy", choices=PRIVACY, default="private",
                        help="Default 'private' — flip up deliberately.")
    parser.add_argument("--tags", default="", help="Comma-separated tags.")
    parser.add_argument("--category", default="1",
                        help="YouTube categoryId; default 1 = Film & Animation.")
    args = parser.parse_args()

    try:
        upload(args)
    except HttpError as e:
        sys.exit(f"YouTube API error {e.resp.status}: {e}")


if __name__ == "__main__":
    main()
