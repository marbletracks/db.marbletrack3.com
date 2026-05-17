#!/usr/bin/env python3
"""One-time OAuth bootstrap for a YouTube upload account.

Run once per account. Opens a browser, asks you to log into the matching
YouTube account, and writes the resulting long-lived refresh_token back into
that account's config JSON. The upload script then runs unattended.

    python3 scripts/youtube_auth.py robs_main
    python3 scripts/youtube_auth.py mt3_account

Requires (install once, user dir is fine on Dreamhost):
    pip install --user google-auth-oauthlib google-api-python-client

Audience MUST be "In production" in Google Auth Platform first, or the
refresh token expires after 7 days (Testing mode) and uploads break weekly.
"""

import argparse
import json
import sys
from pathlib import Path

from google_auth_oauthlib.flow import InstalledAppFlow
from googleapiclient.discovery import build

CONFIG_ROOT = Path(__file__).resolve().parent.parent / "config"

# Explicit label -> credential file. Each file is self-contained and carries
# its own refresh_token, so the two accounts can never cross-post.
ACCOUNTS = {
    "robs_main": CONFIG_ROOT / "marble_track_vids_robs_main_YT_account.json",
    "mt3_account": CONFIG_ROOT / "post_mt3_vids_to_YT_mt3_account.json",
}


def load_config(label: str) -> tuple[Path, dict]:
    """Return (path, parsed config) for an account label, or exit with help."""
    if label not in ACCOUNTS:
        sys.exit(f"Unknown account '{label}'. Choose one of: {', '.join(ACCOUNTS)}")
    path = ACCOUNTS[label]
    if not path.exists():
        sys.exit(f"Missing config file: {path}")
    return path, json.loads(path.read_text())


def client_config(cfg: dict) -> dict:
    """Shape the oauth_client block into the dict InstalledAppFlow expects."""
    oc = cfg["oauth_client"]
    for key in ("client_id", "client_secret"):
        if not oc.get(key) or oc[key].startswith("<"):
            sys.exit(f"oauth_client.{key} is not filled in — paste it from the "
                     f"Google Cloud OAuth client first (see config/README.md).")
    return {
        "installed": {
            "client_id": oc["client_id"],
            "client_secret": oc["client_secret"],
            "auth_uri": "https://accounts.google.com/o/oauth2/auth",
            "token_uri": oc.get("token_uri", "https://oauth2.googleapis.com/token"),
            "redirect_uris": ["http://localhost"],
        }
    }


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("account", choices=sorted(ACCOUNTS),
                        help="Which YouTube account to authorize.")
    args = parser.parse_args()

    path, cfg = load_config(args.account)
    scopes = cfg.get("scopes") or ["https://www.googleapis.com/auth/youtube.upload"]

    flow = InstalledAppFlow.from_client_config(client_config(cfg), scopes=scopes)
    # access_type=offline + prompt=consent guarantees a refresh_token is
    # returned even on re-auth (Google omits it otherwise).
    creds = flow.run_local_server(
        port=0,
        access_type="offline",
        prompt="consent",
        authorization_prompt_message=(
            f"\n>>> Log into the YouTube account for '{args.account}' "
            f"in the browser window. <<<\n"),
        success_message="Authorized. You can close this tab and return to the terminal.",
    )

    if not creds.refresh_token:
        sys.exit("No refresh_token returned. Re-run; ensure you fully approve "
                 "the consent screen.")

    # Confirm WHICH channel was authorized so a wrong login is caught now,
    # not after a video lands on the wrong account.
    yt = build("youtube", "v3", credentials=creds)
    resp = yt.channels().list(part="snippet", mine=True).execute()
    items = resp.get("items", [])
    channel = items[0]["snippet"]["title"] if items else "(no channel on this account)"

    cfg["refresh_token"] = creds.refresh_token
    path.write_text(json.dumps(cfg, indent=2) + "\n")

    print(f"\n✓ refresh_token written to {path.name}")
    print(f"  Authorized channel: {channel}")
    print(f"  If that is NOT the intended '{args.account}' channel, delete the "
          f"refresh_token in that file and re-run logged into the right account.")


if __name__ == "__main__":
    main()
