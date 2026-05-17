# config/ — YouTube upload credentials

These files hold secrets and are **gitignored**. This README is the committed
template/reference so the structure isn't lost.

## Files (one per YouTube account)

| File | Target account |
|------|----------------|
| `marble_track_vids_robs_main_YT_account.json` | Rob's MAIN YouTube account |
| `post_mt3_vids_to_YT_mt3_account.json` | dedicated MT3 YouTube account |

Each is self-contained: it carries the OAuth client creds **and** the
per-account refresh token, so the upload script can run standalone.

## File structure

```json
{
  "account_label": "robs_main",
  "oauth_client": {
    "client_id": "<from Google Cloud OAuth client>",
    "client_secret": "<from Google Cloud OAuth client>",
    "token_uri": "https://oauth2.googleapis.com/token"
  },
  "refresh_token": "<filled by scripts/youtube_auth.py>",
  "scopes": ["https://www.googleapis.com/auth/youtube.upload"]
}
```

## Where each value comes from

1. **Google Cloud Console** → create project → enable *YouTube Data API v3*.
2. **OAuth consent screen**: User type *External*, **Publish** (status =
   Production), scope `.../auth/youtube.upload`.
3. **Credentials → Create OAuth client ID → Desktop app** → download JSON.
   Copy its `client_id` and `client_secret` into the `oauth_client` block of
   **both** account files (same client serves both accounts).
4. `refresh_token`: leave the placeholder. Running
   `python scripts/youtube_auth.py robs_main` (and `... mt3_account`) opens a
   browser; log into the matching account; the script writes the
   `refresh_token` back into that file. Production status keeps it long-lived.

## Notes

- Upload quota is per Cloud project: ~6 uploads/day on the default 10,000-unit
  quota, shared across both accounts. Revisit if it becomes a bottleneck.
- Never commit the populated `*.json` files. `.gitignore` already excludes
  both account files.
