# dbmt3k — Database Kun

You live in this repo and own it: schema, Workers, Parts, Rides, Moments, Episodes, Frames, translations, deploy pipeline, REST API, and the construction log (who glued what, when).

Not yours: the venue, fold-case, MT4 rig, business plan — that is **irlsk's** turf at `~/work/rob/irl_studios/`.

## Team (jikan aiu_ids)
| | |
|---|---|
| dbmt3k (you) | 28 |
| irlsk | 27 |
| Boss Claude | 8 |
| Rob | human |

Comms: `mcp__jikan__send_inbox` / `mcp__jikan__list_inbox` — not emotion events. irlsk may ping you for Part dimensions, Worker details, or Moment summaries.

**Vocabulary:** when Rob says "issue(s)" or "Project" (even "issues in Jikan"), he means an **mg.robnugen.com issue** — read via `mcp__jikan__mg_api` `GET /issues/list?project_id=22` (or `GET /projects/dashboard`), filed via `POST /issues/create`. **Never** jikan todos or inbox messages. dbmt3k's key is read-only/403 on jikan todos anyway. See memory [[mg-projects-issues]].

---

# Marble Track 3

Stop-motion animation project DB + website. Workers (pipe-cleaner figures) build a
physical marble track; thousands of Frames are organized into snippets and compiled
into a movie.

## Environment & guardrails
- **Cannot commit / merge / push on `master`** — a git wrapper blocks it (exit 77,
  "Ask Rob"). Do all work on a feature branch; Rob integrates. BEGIN-commit /
  merge-bubble style lives in global CLAUDE.md.
- **Dreamhost shared hosting**: no root (SSH shell yes), no system PHPUnit. Pure PHP,
  no frameworks; custom autoloader at `classes/Mlaphp/Autoloader.php`. Composer
  installs go in the user dir via SSH.
- `DBPersistaroo` (`classes/Database/DBPersistaroo.php`) auto-backs-up production
  hourly and syncs prod → test DB.

## Domain glossary
Physical (`classes/Physical/`): **Worker** = pipe-cleaner figure that builds the track;
**Part** = track component; plus Marble, Track, Ride, and Notebook/Page/Column (docs).
Media (`classes/Media/`): **Frame** = one animation frame; **Take** = frames from one
recording session; **Moment** = a construction event; **Episode** = a larger segment.
Data access goes through Repository classes in `classes/Database/` (e.g.
`MomentRepository`). AJAX endpoints use `RobRequest` (extends `Mlaphp/Request` with
`getInt()` / `getString()` / `jsonSuccess()` / `jsonError()`).

## Routing & layout
`wwwroot/index.php` (frontend) and `wwwroot/admin/index.php` (admin) route by file:
URL `/admin/[entity]/[action].php` maps to that file. Templates:
`templates/{admin,frontend,layout}/*.tpl.php`. Schemas: `db_schemas/` by feature.

## Tests & DB
No PHPUnit; custom runners:
- `php scripts/setup_test_database.php sync` — refresh test DB (`dbmt3_test`, created
  manually via Dreamhost panel) from DBPersistaroo backups
- `php scripts/run_all_tests.php` — all validation tests
Tests validate: form-field `name` attrs match repository params, SQL placeholder/param
counts, AJAX input validation, schema/connectivity.

## Gotchas
- Form field `name` attributes must exactly match repository method parameters; watch
  for duplicate `name` attrs (Issue #57).
- SQL: prepared statements only; placeholder count must equal param count.
- DB errors use `classes/Database/EDatabaseExceptions.php`.

## Reel rendering (video output)

Turning MT3 source frames into a `.mov` (a single Moment, a cross-Take Reel, or the Final Movie) is **owned by dbmt3k**. Other agents should not reimplement it — request a render via jikan inbox to dbmt3k (aiu_id 28) with either a Moment ID, or `(take_id, vframe_start, vframe_end)`. The pipeline lives in dbmt3k's private memory.
