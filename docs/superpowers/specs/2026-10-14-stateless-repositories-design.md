# Stateless Repositories — design

Date: 2026-10-14. Branch chain starts at `stateless-repositories` (BEGIN `8d5b39a`).

## Problem

Seven repositories (Notebook, Page, Episode, Moment, Workers, Parts, Marbles) remember a
"current row" (`setNotebookId()`, `$this->notebook_id = …` inside `findAll()`), and the
`HasPhotos` trait reads that hidden ID through `getId()`. That allows:

1. **Wrong-row save.** After a `find…()` that walks several rows, the repository still holds
   the last row's ID; a following photo save lands there.
2. **Form Save wipes API-added photos.** `savePhotos()` deletes every link and re-adds only
   the form's list, so a photo attached through the API while the form is open disappears.
3. **Half-done save.** Nothing wraps that delete and re-insert together; a failure partway
   leaves the row with no photos.
4. **Queries per row.** `hydrate()` calls `loadPhotos()` (2 queries) per row, and for
   Workers/Parts/Marbles also `loadMoments()` (2 more). `WorkersRepository::findAll()` is ~4N.

Findings that shaped scope:

- `HasMoments::saveMoments()` has no callers — dead code, deleted rather than fixed.
  `loadMoments($entity)` already takes the row explicitly; its only state is the
  `$this->moments` buffer. It contributes to problem 4 only.
- `save_moment_from_realtime.php` attaches a photo only to a Moment it has just inserted,
  so it cannot wipe anything. Not a bug; it is converted as a caller.
- `is_primary` is never written as 1 anywhere; "primary" means first in sort order. Keep that.
- The API only ever adds photos; admin forms always replace. The diff approach relies on this.
- Some callers already open a transaction (`save_moment_from_realtime.php`,
  `admin/parts/images/save_photos.php`). In MySQL a nested `BEGIN` silently commits the outer
  transaction, so new code must join an open transaction, never start a second one.

## Non-goals

- No schema changes. No change to `Photo` / `Moment` objects.
- No change to template output or to REST API JSON (the August `scripts/mt3_*` tools read it).
- Raw `INSERT`/`UPDATE` SQL inside admin handlers (e.g. `notebook.php`) stays as is.

## Step 0 — prove each problem first

Run in the existing Docker stack (`docker-compose.yml`, `phpunit-docker.xml`). Tests live in
`tests/Integration/RepositoryState/` and must be red on today's code.

First, refresh `dbmt3.sql` (structure only, from the current test DB): the 2025-08-08 dump
lacks `marbles_2_photos` and the `*_2_moments` tables. Own commit.

| # | Test | Dropped from scope if |
|---|---|---|
| 1 | Notebooks A, B; `findAll()`; save photo meaning A; assert it lands on A | no admin/API path ever runs a multi-row find before a photo save — then it is reported as a latent trap, not a bug |
| 2 | Part has photos {1,2}; API adds 3; form saves [1,2]; assert 3 survives | — |
| 3 | DB wrapper throws on the 2nd INSERT in `savePhotos()`; assert original links remain | — |
| 4 | Count queries during `findAll()` over N rows; assert independent of N | production row counts (read-only) show N too small to matter |

Deliverable: a short report marking each problem **proven** or **dropped (why)** before any
production code changes. Later work shrinks to match.

## Characterisation tests

Written before any production change; green at every later commit. Per kind, in Docker:

- `findById` / `findAll` return the same photos in the same order (`photo_sort`, `photo_id`);
  first photo is primary.
- The add-only path skips duplicates and appends after the current max `photo_sort`.
- Worker/Part/Marble moments: same fields, order, `is_significant`, and attached photos.
- `/api/v1/{kind}` JSON byte-identical for a fixed set of fixture rows.

## New classes

### `Database\PhotoLinks` (stateless)

```php
new PhotoLinks(DbInterface $db, string $table, string $keyColumn)
forIds(int[] $ids): array            // [id => Photo[]], 2 queries total
forId(int $id): array                // Photo[]
add(int $id, Photo[] $photos): void  // skip duplicates, append after max sort
saveFromForm(int $id, int[] $loadedIds, int[] $submittedIds): void
```

`saveFromForm`:

- remove = `loaded − submitted`
- insert = `submitted − currently linked`
- order = submitted list first, then any remaining links not in it (API-added extras) in
  their existing order
- all in one transaction; if `$db->inTransaction()` is already true, join it instead of
  beginning one
- unknown `$id` throws

Requires a new `DbInterface::inTransaction(): bool` (implemented in `Database`).

### `Database\PerspectiveMoments` (stateless, replaces `HasMoments`)

```php
forPerspectives(string $type, int[] $ids): array  // [id => Moment[]]
```

Same SQL as `loadMoments()` with `perspective_entity_id IN (…)`, plus one batched photo query:
2 queries total regardless of row count.

## Repositories

- Remove the current-row field, `set…Id()`, `getId()`, the abstract trait getters, and
  `use HasPhotos` / `use HasMoments`.
- Hold a `PhotoLinks` (and `PerspectiveMoments` for Workers/Parts/Marbles) built in the
  constructor.
- Multi-row finds fetch rows, then batch-fill photos (and moments) by ID. `hydrate()` becomes
  a pure function of its row — no queries.
- Public photo methods take the ID explicitly:
  - `addPhotosFromUrls(int $id, array $urls)` — API, parts image uploader
  - `savePhotosFromForm(int $id, array $loadedIds, array $urls)` — admin forms

  Both resolve URLs with `PhotoRepository::findOrCreateByUrls()`, then pass IDs to `PhotoLinks`.

## Forms

Notebook, Page, Episode, Moment, Part, Worker, Marble.

- Template: one `<input type="hidden" name="loaded_photo_ids[]" value="<?= $photo->photo_id ?>">`
  per photo shown at load. URL inputs unchanged.
- Handler: `$repo->savePhotosFromForm($id, $request->getIntArray('loaded_photo_ids'), $image_urls)`.
  Add `RobRequest::getIntArray()`.
- New row: loaded list is empty, so nothing is removed.
- Marble form (textarea `photo_urls`) gets the same hidden field; its `savePhotos([])` branch
  goes away.
- **Hidden field missing** (tab opened before deploy, saved after): remove nothing, only add and
  reorder.

Other callers:

- `admin/parts/images/save_photos.php`: the `setWorkerId()` loop becomes
  `addPhotosFromUrls($worker_id, …)` / `add()`, joining its existing transaction.
- `admin/ajax/save_moment_from_realtime.php`: single `add($moment_id, …)`.
- `api/v1/{parts,workers,moments,marbles}`: `addPhotosFromUrls($id, …)`; responses unchanged.

## Rollout

A chain of merge bubbles, each branched from the previous bubble's closed tip, each checked by
Claude plus the Docker test suite. Rob merges master to the final tip once all are done.

1. `stateless-repositories` — Step 0, schema dump, characterisation tests, `inTransaction()`,
   `RobRequest::getIntArray()`, `PhotoLinks`, `PerspectiveMoments`
2. Notebooks
3. Pages (its JS reordering exercises "form order wins")
4. Episodes
5. Moments (realtime endpoint, two API endpoints)
6. Workers (+ `PerspectiveMoments`)
7. Parts (+ `PerspectiveMoments`, `save_photos.php`)
8. Marbles (+ `PerspectiveMoments`), then delete `HasPhotos`, `HasMoments`, and dead
   `saveMoments` / `getMomentLinkingTable`

Within a kind's bubble: repository switch → form template + handler → API/other callers, each
an atomic commit. Characterisation tests stay green throughout.

Per-bubble done: Docker suite green, including that kind's Step 0 tests now passing.

## Production check (once, around Rob's deploy)

`scripts/photo_links_snapshot.php`, run on Dreamhost over SSH, SELECT-only:

- writes `{table: {id: [photo_ids in sort order]}}` for every `*_2_photos` table
- `--diff before.json after.json` prints rows whose list changed

Procedure: snapshot before Rob deploys; snapshot after; diff must be empty. Then fetch each
kind's admin list page and 3 sample detail pages (admin cookie from Rob, or API key where an
endpoint exists): 200 and photo counts matching the snapshot. Finally, one real reorder-only
form edit on one row; the diff must show exactly that change.
