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
- **Dreamhost shared hosting**: no root (SSH shell yes), no test tools on the server. Pure PHP,
  no frameworks; custom autoloader at `classes/Mlaphp/Autoloader.php`. Composer
  installs go in the user dir via SSH.
- `DBPersistaroo` (`classes/Database/DBPersistaroo.php`) auto-backs-up production
  hourly and syncs prod → test DB.

## Deploy
`./deploy_to_dh.sh` deploys the current branch: `git push`, then `git pull origin
<branch>` over ssh in `db.mt3:db.marbletrack3.com`. It runs `git restore .` on the
server first, so an edit made there is thrown away; `classes/Config.php`, `.env`, the
DB backups and the generated static pages are gitignored and survive. Bubble branches
are deployed straight from the branch, not from `master`.

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
Codeception 5, in `Tests/` (namespace `Tests`), with dev tools in a gitignored `vendor/`:
`docker run --rm -e COMPOSER_HOME=/tmp/composer -v "$PWD":/app -w /app composer:2 composer install --no-interaction`
- `~/work/rob/standards-mcp/run-unit.sh .` — the Unit suite (`Tests/Unit/`), in Docker
  with no network and no database
- `docker compose up -d`, then `docker compose exec php vendor/bin/codecept run
  Integration` — `Tests/Integration/`, against the MySQL of `docker-compose.yml`
  (schema from `dbmt3.sql`). `Tests/Functional/` is in no suite and does not run.
- `php scripts/setup_test_database.php sync` — refresh test DB (`dbmt3_test`, created
  manually via Dreamhost panel) from DBPersistaroo backups
- `php scripts/run_all_tests.php` — the older validation scripts, which need mysqli
- `~/work/rob/standards-mcp/run-phpstan.sh .` — PHPStan level 5 in Docker, analysed
  as PHP 8.5, with no baseline: fix a finding, or `// @phpstan-ignore <identifier>
  (reason)` on its line. A new entry script names what it takes from `prepend.php` in
  a `@var` block under the include; a new template opens with a `@var` block for what
  its page script sets.
- `~/work/rob/standards-mcp/run-phpcs.sh .` — PSR-12 (`phpcs.xml`), less the sniffs
  the code failed on 2026-11-11, listed there as a backlog
- **Every commit is checked**: `.githooks/pre-commit` runs phpcs on the staged PHP and
  PHPStan over the staged tree. Enable once per clone: `git config core.hooksPath
  .githooks`. It does not run the suites; run those yourself.
Tests validate: form-field `name` attrs match repository params, SQL placeholder/param
counts, AJAX input validation, schema/connectivity.

## Gotchas
- Form field `name` attributes must exactly match repository method parameters; watch
  for duplicate `name` attrs (Issue #57).
- SQL: prepared statements only; placeholder count must equal param count.
- DB errors use `classes/Database/EDatabaseExceptions.php`.

## Photos from badmint (mg #709)

Photos of Parts, Tracks and Workers are uploaded, straightened and cropped on
`https://badmint.robnugen.com/mt3/`, which files them on b.robnugen.com and attaches
the `_1000` URL here through the keyed API (`PATCH /api/v1/{parts,tracks,workers,moments}/<id>`
with `photo_urls`; append-only, deduped). The Part, Track and Worker edit pages link there
with `?part=`, `?track=`, `?worker=`. The Moment form opens it with `?for=moment` and the
Part and Workers from the Notes; `wwwroot/admin/js/mt3-photos.js` takes each saved photo back
by `postMessage` (origin `https://badmint.robnugen.com` only) as an Image URL row, and the
form's own Save links it. Nothing in MT3 posts to badmin's `bullet.php` any more
(`Tests/Unit/NothingPostsToBadminTest.php`).

## Reel rendering (video output)

Turning MT3 source frames into a `.mov` (a single Moment, a cross-Take Reel, or the Final Movie) is **owned by dbmt3k**. Other agents should not reimplement it — request a render via jikan inbox to dbmt3k (aiu_id 28) with either a Moment ID, or `(take_id, vframe_start, vframe_end)`. The pipeline lives in dbmt3k's private memory.
