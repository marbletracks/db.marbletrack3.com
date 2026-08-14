# Patreon posting — decisions and process

Campaign: `https://www.patreon.com/marbletrack3`

## Hard constraint

**The Patreon API cannot create posts.** API v2 exposes only
`GET /campaigns/{id}/posts` and `GET /posts/{id}`; the only post-related OAuth scope
is `campaigns.posts`, which is read access. Patreon staff, on their developer forum:
*"the public api does not provide write operations, so that's not possible."* The
official `patreon/patreon-php` SDK does OAuth and identity only.

Every post is therefore created **in the browser**. The read API is still useful for
verifying what went out (`published_at`, `is_public`).

## Decisions

| | |
|---|---|
| Audience | Friends supporting Rob. Cadence and warmth over polish. |
| **MT3 content** | **Public. Non-negotiable** — Rob, 2026-08-14: *"I insist Patreon mt3 stuff be public; otherwise it makes no sense."* |
| Everything else | Possibly public too. Open. |
| If anything is patron-only | Artifacts, not writing — deleted frames, early snippets, all frames. Rob promised exactly these in 2018. Lore recruits; artifacts reward. |
| Scope | ~10–20 milestone posts, richer than the DB currently holds. |
| Rollout | **Write all 10–20 first, then drip them out over about a year** (Rob, 2026-08-14). At 15 posts that's roughly one every three and a half weeks. Supersedes the earlier "one every few days". |
| Dates | **Backdated correctly** to when the milestone actually happened. |
| Notifications | **On.** |

## The patron perk is NOT out-takes (Rob, 2026-08-14)

An earlier draft of this file claimed deleted frames constitute a standing supply of
bonus footage. Rob corrected it:

> "Outtakes are not piling up... Even if I deleted six frames that took me 2 days to
> build, that's only one half of one second of video deleted, so it's nothing like
> an 'outtake'."

At 12fps, deletions are fractions of a second. There is no reel. The 2018 promise in
`content/episode/2018/11/01mr-greene-ghosts.md` was about three images, and that was
the right scale for it.

What *is* available:

> "We could possibly extract info by carefully analyzing the deleted frames in
> Dragonframe."

Technically feasible — Dragonframe flags deleted vframes as hidden in the EDL
(`file & 2^30`) so playback skips them, but **the JPEG stays on disk**. The material
exists and is recoverable; it's just unindexed. The value would be the *story* each
deletion tells (what went wrong that day), not the footage.

**Open: what the patron perk actually is.** Not decided. Candidates: early snippet
access, all frames, the museum party invitation (see below).

## Backdating

Supported natively in the post editor: toggle **Set publish date** and pick a past
date and time. A separate control, **Allow members to be notified when this post is
published**, governs notification — we leave it ON.

Consequence to remember: a post dated 2019 lands in the feed *at* 2019, below
everything newer. The notification fires, but the campaign's front page won't
visibly change as the drip goes out. Use a **Collection** to make the series
browsable as one thing.

## Per-post checklist

1. Draft exists at `docs/patreon/YYYY-MM-DD-slug.md` and Rob has approved it.
2. New post → paste title and body.
3. Attach media.
4. Audience → **Public**.
5. **Set publish date** → the historical date/time from the draft's front matter.
6. Confirm "Allow members to be notified…" is **on**.
7. Add to the Collection.
8. Publish.
9. Paste the resulting URL back into the draft's `patreon_url` front matter.
10. Verify while logged out that the post is publicly visible and the date reads correctly.

**Do the first post together, with Rob watching**, before automating any of it.

## Sequencing

1. Write all 10–20 posts first. Nothing publishes until the set is drafted and Rob
   has approved it.
2. Then release over roughly a year — about one every three and a half weeks at 15
   posts.

Because each post is backdated to when its events happened, the drip order controls
only what the notifications say and in what order friends encounter the story. The
archive fills in behind them at historical positions regardless.

A year-long release also means **the campaign stays alive without Rob having to
film** — which matters, since the track has been under plastic for at least nine
months.

## Draft front matter

```yaml
publish_date: 2017-07-07T14:00:00+09:00   # what to backdate to
title: In the beginning, there was a field
visibility: public
notify: true
collection: null
source_moments: [1]
source_episodes: []
media: []
patreon_url: null                          # filled in after posting
status: draft                              # draft | ready | posted
```

The field names deliberately mirror what an `episodes` table would need if this
prose is later migrated into the DB.
