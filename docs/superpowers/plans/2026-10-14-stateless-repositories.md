# Stateless Repositories Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Stop the seven photo-owning repositories from remembering a "current row", so photo saves can't land on the wrong row, can't wipe links the API added, can't half-finish, and lists stop costing queries per row.

**Architecture:** Two new stateless classes take every ID explicitly: `Database\PhotoLinks` (one per kind, built with that kind's parent table, link table and key column) and `Database\PerspectiveMoments`. Repositories hold them, batch-load photos and moments for whole lists, and forward photo writes with an explicit ID. Admin forms post back the photo IDs they loaded, so a save removes only what was deleted on screen.

**Tech Stack:** PHP 8.1 (Docker) / Dreamhost PHP, mysqli via `Database\Database`, PHPUnit 9.6 in Docker (`docker-compose.yml`, `phpunit-docker.xml`), no framework.

**Spec:** `docs/superpowers/specs/2026-10-14-stateless-repositories-design.md`

## Global Constraints

- Never commit, merge or check out on `master`; never touch master at all. Each bubble branches from the previous bubble's closed tip.
- BEGIN commit of each bubble = only the `SENTIMENTAL_VERSION` bump in `prepend.php`. Close with `git close-bubble "DONE …"` (`--dry-run` first) per the `close-bubble` skill.
- Commits: subject ≤50 chars, imperative, capitalised, no period; body explains why; use multiple `-m` flags, never a HEREDOC; last `-m` is `Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>`.
- Commits are atomic and split by layer: repository / template / handler / API never share a commit.
- No schema changes. No change to `Photo` / `Moment` objects. No change to rendered template output or REST API JSON.
- Prepared statements only; placeholder count equals parameter count.
- `is_primary` keeps today's meaning: `Photo->isPrimary` is the column's value; "primary photo" = last photo flagged primary, else the first.
- Code that writes links joins a transaction the caller already opened (`DbInterface::inTransaction()`), never begins a second one.
- Production work is read-only except `/deploy` and the one reorder-only form edit per gate (approved by Rob, 2026-10-14).
- Spec deviation, decided while planning: the posted-ID helper is `\Utilities::postedIds()` because admin handlers read `$_POST` directly and have no `RobRequest`. And because a missing `loaded_photo_ids[]` field reads as `[]`, "hidden field missing → remove nothing" needs no special case.

## Review Focus

1. **Coded photos.** A photo with `code` set displays its CloudFront URL (`getUrl()`), not its `url` column. Saving the form unchanged must keep the same `photo_id`, not create a duplicate photo row and swap the link. Pinned in Task 5 (`testUnchangedFormKeepsCodedPhoto`).
2. **The same URL typed twice** in one form produces one link, not two. Pinned in Task 5 (`testDuplicateUrlLinksOnce`).
3. **Blank URL rows** (every form renders one empty input) are ignored. Pinned in Task 5 (`testBlankUrlsIgnored`).
4. **Clearing every photo in a form** removes everything the form loaded but keeps photos the API added meanwhile. Pinned in Task 5 (`testClearingFormKeepsApiExtras`).
5. **A form posted without `loaded_photo_ids[]`** (tab opened before deploy) removes nothing, only adds and reorders. Pinned in Task 5 (`testMissingLoadedListRemovesNothing`).

---

## How to run things

- Docker suite: `docker compose exec php vendor/bin/phpunit -c phpunit-docker.xml`
- One file: `docker compose exec php vendor/bin/phpunit -c phpunit-docker.xml tests/Integration/RepositoryState/PhotoLinksTest.php`
- `$SCRATCH` = this session's scratchpad directory (`/tmp/claude-1000/…/scratchpad`).
- `$APP` = the app directory on the server, recorded in Task 9.

## Bubble Gate (procedure used by Tasks 9, 12, 15, 18, 22, 26, 30, 35)

Each gate task gives: **branch**, **DONE message**, **expected red Step 0 tests**, **admin list URL**, **edit URL pattern**, **API path** (or none).

- [ ] **G1. Suite.** Run the Docker suite. Pass = every test green except (a) the pre-existing failures recorded in Task 1 and (b) the gate's expected red Step 0 tests.
- [ ] **G2. Close.** `git close-bubble "<DONE message>" --dry-run`, read it, then run it for real.
- [ ] **G3. Before.** `ssh db.mt3 "cd $APP && php scripts/photo_links_snapshot.php" > $SCRATCH/before_<branch>.json`. If an API path is given: `curl -s -H "X-API-Key: $MT3_API_KEY" https://db.marbletrack3.com/api/v1/<api path> > $SCRATCH/api_before_<branch>.json`, with `MT3_API_KEY` sourced from `.env`.
- [ ] **G4. Deploy.** Invoke the `deploy` skill from the closed branch (pushes the branch, runs `ssh db_mt3_deploy <branch>`).
- [ ] **G5. After.** Take the same snapshot into `after_<branch>.json`, then `php scripts/photo_links_snapshot.php --diff $SCRATCH/before_<branch>.json $SCRATCH/after_<branch>.json`. Expected: no output, exit 0. If an API path is given, fetch `api_after_<branch>.json` and `cmp` it with the before file. Expected: identical.
- [ ] **G6. Repositories.** `ssh db.mt3 "cd $APP && php scripts/photo_links_snapshot.php --repos"`. Expected: every table prints `0 mismatches`.
- [ ] **G7. Pages load.** `curl -s -o /dev/null -w '%{http_code}\n' -H "Cookie: $(cat $SCRATCH/admin_cookie.txt)" <url>` for the admin list URL and 3 edit URLs (IDs taken from the after snapshot, including one row with ≥2 photos). Expected: `200` each, and `grep -c 'name="loaded_photo_ids\[\]"'` on the edit page equals that row's photo count in the snapshot. That second check applies once the kind's form is converted.
- [ ] **G8. One real edit** (only for gates whose kind's form changed). `python3 $SCRATCH/form_reorder.py '<edit URL for the ≥2-photo row>' $SCRATCH/admin_cookie.txt`, then snapshot into `edited_<branch>.json` and diff it against `after_<branch>.json`. Expected: exactly that row, with its first two photo IDs swapped. Run `form_reorder.py` again to swap them back, then diff a fresh snapshot against `after_<branch>.json`. Expected: no output.
- [ ] **G9. Stop on failure.** If any check fails: deploy the previous bubble's branch with the `deploy` skill, re-run G5 against the last good snapshot, and report to Rob before fixing anything.

---

# Bubble 1 — `stateless-repositories` (already BEGUN at `8d5b39a`)

### Task 1: Docker suite runs on today's production schema

**Files:**
- Modify: `dbmt3.sql` (replace with structure-only dump of production)
- Modify: `docker-compose.yml:33` (remove the `01-init.sql` mount)
- Delete: `docker/init-db.sql` (it `SOURCE`s `/docker-entrypoint-initdb.d/dbmt3.sql`, a path that is never mounted)

**Interfaces:**
- Produces: a running `mt3_php` + `mt3_mysql` pair whose `dbmt3_test` has every current table, and the list of pre-existing test failures that later gates compare against.

- [ ] **Step 1: Find the newest production backup**

Run: `ssh db.mt3 'ls -t ~/*/db_backups/db_backup_*.sql | head -1'`
Expected: one path, e.g. `/home/dh_fbrdk3/db.marbletrack3.com/db_backups/db_backup_2026-10-14_….sql`. The directory above `db_backups` is `$APP`; write it down for Task 9.

- [ ] **Step 2: Copy it down and strip the data**

```bash
scp db.mt3:<path from step 1> $SCRATCH/prod_backup.sql
grep -v -E '^(INSERT INTO|USE |CREATE DATABASE|LOCK TABLES|UNLOCK TABLES)' $SCRATCH/prod_backup.sql > dbmt3.sql
rm $SCRATCH/prod_backup.sql
grep -c '^CREATE TABLE' dbmt3.sql
grep -o 'CREATE TABLE `[a-z_]*_2_photos`' dbmt3.sql
```
Expected: the table count is higher than before, and the photo-link tables listed include `marbles_2_photos`. Also run `grep -A3 'perspective_entity_type' dbmt3.sql | head -3` and note whether the enum includes `'marble'`; Task 3 needs to know.

- [ ] **Step 3: Remove the broken init script**

In `docker-compose.yml` delete the line `      - ./docker/init-db.sql:/docker-entrypoint-initdb.d/01-init.sql`, then `git rm docker/init-db.sql`.

- [ ] **Step 4: Rebuild the stack from scratch**

```bash
ss -ltn | grep -q ':3307 ' && echo "3307 busy - stop and ask Rob"
docker compose down -v
docker compose up -d --build
docker compose exec mysql mysqladmin --wait=60 -uroot -pmt3_test_pass ping
docker compose exec mysql mysql -uroot -pmt3_test_pass dbmt3_test -e "SHOW TABLES LIKE '%\_2\_photos'"
```
Expected: `mysqld is alive`, then 7 rows (episodes, marbles, moments, notebooks, pages, parts, workers). If the table list is empty, the init is still running: re-run the `SHOW TABLES` after `docker compose logs mysql | tail` shows `ready for connections` twice.

- [ ] **Step 5: Record the baseline**

Run: `docker compose exec php vendor/bin/phpunit -c phpunit-docker.xml 2>&1 | tail -40`
Expected: the suite runs to completion. Copy the names of any failing or erroring tests. They are **pre-existing failures**: do not fix them, list them in the commit body.

- [ ] **Step 6: Commit**

```bash
git add dbmt3.sql docker-compose.yml docker/init-db.sql
git commit -m "Refresh the Docker schema from production" \
  -m "The 2025-08-08 dump predates marbles_2_photos and the moment perspective changes, and init-db.sql sourced a path that was never mounted, so the Docker suite could not exercise any photo code. The dump is structure only." \
  -m "Pre-existing failures on this schema: <list from step 5, or 'none'>." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

### Task 2: Step 0 — prove each problem with a failing test

**Files:**
- Create: `tests/Integration/RepositoryState/Support/PhotoFixtures.php`
- Create: `tests/Integration/RepositoryState/Support/CountingDb.php`
- Create: `tests/Integration/RepositoryState/Support/FailingDb.php`
- Create: `tests/Integration/RepositoryState/Step0WrongRowTest.php`
- Create: `tests/Integration/RepositoryState/Step0ApiPhotoSurvivesFormTest.php`
- Create: `tests/Integration/RepositoryState/Step0AtomicSaveTest.php`
- Create: `tests/Integration/RepositoryState/Step0ListQueryCountTest.php`

**Interfaces:**
- Produces: the `PhotoFixtures` trait (`fixturesUp()`, `cleanupFixtures()`, `db()`, `makeRow(string $kind): int`, `makeMoment(int $start, int $end, string $notes): int`, `makePhoto(string $name): int`, `link(string $table, string $key, int $id, int $photoId, int $sort, int $isPrimary = 0): void`, `linkedIds(string $table, string $key, int $id): int[]`, `photoIds(array $photos): int[]`, `url(string $name): string`), plus `CountingDb` (public `int $queries`) and `FailingDb` (throws on the Nth write). Every later test uses these.

- [ ] **Step 1: Write the support files**

`tests/Integration/RepositoryState/Support/CountingDb.php`:
```php
<?php

use Database\DbInterface;

/** Passes everything to a real connection and counts the statements sent. */
class CountingDb implements DbInterface
{
    public int $queries = 0;

    public function __construct(protected DbInterface $inner)
    {
    }

    public function insertFromRecord(string $tablename, string $paramtypes, $record)
    {
        $this->queries++;
        return $this->inner->insertFromRecord($tablename, $paramtypes, $record);
    }

    public function updateFromRecord(string $tablename, string $paramtypes, $record, string $where)
    {
        $this->queries++;
        return $this->inner->updateFromRecord($tablename, $paramtypes, $record, $where);
    }

    public function fetchResults($sql, $paramtypes = null, $var1 = null)
    {
        $this->queries++;
        return $this->inner->fetchResults($sql, $paramtypes, $var1);
    }

    public function executeSQL($sql, $paramtypes = null, $var1 = null)
    {
        $this->queries++;
        return $this->inner->executeSQL($sql, $paramtypes, $var1);
    }

    public function insertId(): ?int
    {
        return $this->inner->insertId();
    }

    public function getAffectedRows(): int
    {
        return $this->inner->getAffectedRows();
    }

    public function beginTransaction(): void
    {
        $this->inner->beginTransaction();
    }

    public function commit(): void
    {
        $this->inner->commit();
    }

    public function rollBack(): void
    {
        $this->inner->rollBack();
    }
}
```

`tests/Integration/RepositoryState/Support/FailingDb.php`:
```php
<?php

require_once __DIR__ . '/CountingDb.php';

use Database\DbInterface;

/** A real connection whose Nth INSERT/UPDATE/DELETE throws, to simulate a save dying partway. */
class FailingDb extends CountingDb
{
    private int $writes = 0;

    public function __construct(DbInterface $inner, private int $failOnWrite)
    {
        parent::__construct($inner);
    }

    public function executeSQL($sql, $paramtypes = null, $var1 = null)
    {
        if (preg_match('/^\s*(INSERT|UPDATE|DELETE)\b/i', $sql) && ++$this->writes === $this->failOnWrite) {
            throw new \Database\EDatabaseException("Injected failure on write #{$this->writes}");
        }
        return parent::executeSQL($sql, $paramtypes, $var1);
    }
}
```

`tests/Integration/RepositoryState/Support/PhotoFixtures.php`:
```php
<?php

use Database\EpisodeRepository;
use Database\MarblesRepository;
use Database\MomentRepository;
use Database\NotebookRepository;
use Database\PageRepository;
use Database\PartsRepository;
use Database\PhotoRepository;
use Database\WorkersRepository;

/** Creates throwaway rows in dbmt3_test and removes exactly those rows afterwards. */
trait PhotoFixtures
{
    private string $prefix;
    private array $tracked = [];

    protected function fixturesUp(): void
    {
        $this->prefix = 'rs' . substr((string) time(), -4) . random_int(10, 99) . '_';
        $this->tracked = [];
    }

    protected function db(): \Database\Database
    {
        return getTestDatabase();
    }

    protected function url(string $name): string
    {
        return "https://example.com/{$this->prefix}{$name}.jpg";
    }

    protected function makeRow(string $kind): int
    {
        $db = $this->db();
        $p = $this->prefix;
        $n = random_int(100, 999);
        switch ($kind) {
            case 'notebook':
                $id = (new NotebookRepository($db))->insert("{$p}nb{$n}", date('Y-m-d H:i:s'));
                $this->track('notebooks', 'notebook_id', $id);
                return $id;
            case 'page':
                $notebookId = $this->makeRow('notebook');
                $id = (new PageRepository($db))->insert($notebookId, "{$p}pg{$n}", date('Y-m-d H:i:s'));
                $this->track('pages', 'page_id', $id);
                return $id;
            case 'episode':
                $id = (new EpisodeRepository($db))->insert("{$p}ep{$n}", 'fixture', '');
                $this->track('episodes', 'episode_id', $id);
                return $id;
            case 'moment':
                return $this->makeMoment(10, 20, "{$p}moment{$n}");
            case 'worker':
                $id = (new WorkersRepository($db, 'en'))->insert("{$p}w{$n}", "{$p}Worker {$n}", 'fixture');
                $this->track('workers', 'worker_id', $id);
                return $id;
            case 'part':
                $id = (new PartsRepository($db, 'en'))->insert("{$p}p{$n}", "{$p}Part {$n}", 'fixture');
                $this->track('parts', 'part_id', $id);
                return $id;
            case 'marble':
                $id = (new MarblesRepository($db))->insert("{$p}m{$n}", "{$p}Marble {$n}", 'small', 'blue');
                $this->track('marbles', 'marble_id', $id);
                return $id;
        }
        throw new \InvalidArgumentException("Unknown kind {$kind}");
    }

    protected function makeMoment(int $start, int $end, string $notes): int
    {
        $id = (new MomentRepository($this->db()))->insert($start, $end, null, $notes, null);
        $this->track('moments', 'moment_id', $id);
        return $id;
    }

    protected function makePhoto(string $name): int
    {
        $photo = (new PhotoRepository($this->db()))->findOrCreateByUrls([$this->url($name)])[0];
        $this->track('photos', 'photo_id', $photo->photo_id);
        return $photo->photo_id;
    }

    protected function link(string $table, string $key, int $id, int $photoId, int $sort, int $isPrimary = 0): void
    {
        $this->db()->executeSQL(
            "INSERT INTO {$table} ({$key}, photo_id, photo_sort, is_primary) VALUES (?, ?, ?, ?)",
            'iiii',
            [$id, $photoId, $sort, $isPrimary]
        );
    }

    /** @return int[] in display order */
    protected function linkedIds(string $table, string $key, int $id): array
    {
        $results = $this->db()->fetchResults(
            "SELECT photo_id FROM {$table} WHERE {$key} = ? ORDER BY photo_sort ASC, photo_id ASC",
            'i',
            [$id]
        );
        return array_map(fn (array $row) => (int) $row['photo_id'], $results->toArray());
    }

    /** @return int[] */
    protected function photoIds(array $photos): array
    {
        return array_map(fn ($photo) => $photo->photo_id, $photos);
    }

    protected function cleanupFixtures(): void
    {
        $db = $this->db();
        $linkTables = [
            'notebooks_2_photos' => 'notebook_id', 'pages_2_photos' => 'page_id',
            'episodes_2_photos' => 'episode_id', 'moments_2_photos' => 'moment_id',
            'workers_2_photos' => 'worker_id', 'parts_2_photos' => 'part_id',
            'marbles_2_photos' => 'marble_id',
        ];
        $childTables = [
            'moments' => ['moment_translations'],
            'parts' => ['part_translations'],
            'workers' => ['worker_names'],
        ];
        foreach (array_reverse($this->tracked) as [$table, $key, $id]) {
            foreach ($linkTables as $linkTable => $linkKey) {
                if ($linkKey === $key || $key === 'photo_id') {
                    $db->executeSQL("DELETE FROM {$linkTable} WHERE {$key} = ?", 'i', [$id]);
                }
            }
            if ($table === 'workers' || $table === 'parts') {
                $type = rtrim($table, 's');
                $db->executeSQL(
                    "DELETE FROM moment_translations WHERE perspective_entity_type = ? AND perspective_entity_id = ?",
                    'si',
                    [$type, $id]
                );
            }
            foreach ($childTables[$table] ?? [] as $child) {
                $db->executeSQL("DELETE FROM {$child} WHERE {$key} = ?", 'i', [$id]);
            }
            $db->executeSQL("DELETE FROM {$table} WHERE {$key} = ?", 'i', [$id]);
        }
        $this->tracked = [];
    }

    private function track(string $table, string $key, int $id): void
    {
        $this->tracked[] = [$table, $key, $id];
    }
}
```

- [ ] **Step 2: Write the four Step 0 tests**

Each test class starts with the same header:
```php
<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Support/PhotoFixtures.php';
require_once __DIR__ . '/Support/FailingDb.php';

/** @group step0 */
```
and the same body prologue:
```php
    use PhotoFixtures;

    protected function setUp(): void
    {
        $this->fixturesUp();
    }

    protected function tearDown(): void
    {
        $this->cleanupFixtures();
    }
```

`Step0WrongRowTest.php`, class `Step0WrongRowTest extends TestCase`:
```php
    public function testPhotoSaveAfterAListLandsOnTheIntendedNotebook(): void
    {
        $a = $this->makeRow('notebook');
        $this->makeRow('notebook');                 // a later row: findAll() ends on it or after it
        $this->makePhoto('meant-for-a');            // tracked, so cleanup removes it
        $repo = new \Database\NotebookRepository($this->db());

        $repo->setNotebookId($a);                   // the caller means Notebook A...
        $repo->findAll();                           // ...renders a list on the way...
        $repo->savePhotosFromUrls([$this->url('meant-for-a')]);

        $this->assertCount(1, $this->linkedIds('notebooks_2_photos', 'notebook_id', $a),
            'The photo landed on whichever row findAll() walked last, not on Notebook A');
    }
```

`Step0ApiPhotoSurvivesFormTest.php`, class `Step0ApiPhotoSurvivesFormTest extends TestCase`:
```php
    public function testFormSaveKeepsAPhotoTheApiAddedWhileItWasOpen(): void
    {
        $part = $this->makeRow('part');
        $one = $this->makePhoto('one');
        $two = $this->makePhoto('two');
        $three = $this->makePhoto('three');
        $this->link('parts_2_photos', 'part_id', $part, $one, 1);
        $this->link('parts_2_photos', 'part_id', $part, $two, 2);

        $form = new \Database\PartsRepository($this->db(), 'en');
        $loaded = $form->findById($part);            // Rob opens the edit form

        $api = new \Database\PartsRepository($this->db(), 'en');
        $api->setPartId($part);                       // the API attaches a photo meanwhile
        $api->addPhotosFromUrls([$this->url('three')]);

        $form->setPartId($part);                      // Rob saves without touching photos
        $form->savePhotosFromUrls(array_map(fn ($p) => $p->getUrl(), $loaded->photos));

        $this->assertContains($three, $this->linkedIds('parts_2_photos', 'part_id', $part));
    }
```

`Step0AtomicSaveTest.php`, class `Step0AtomicSaveTest extends TestCase`:
```php
    public function testAFailedSaveLeavesTheOriginalLinks(): void
    {
        $notebook = $this->makeRow('notebook');
        $one = $this->makePhoto('one');
        $two = $this->makePhoto('two');
        $this->makePhoto('three');                    // exists already, so no INSERT INTO photos
        $this->link('notebooks_2_photos', 'notebook_id', $notebook, $one, 1);
        $this->link('notebooks_2_photos', 'notebook_id', $notebook, $two, 2);

        $repo = new \Database\NotebookRepository(new FailingDb($this->db(), 2));
        $repo->setNotebookId($notebook);
        try {
            // Rob removed photo one and added photo three; the second write dies.
            $repo->savePhotosFromUrls([$this->url('two'), $this->url('three')]);
        } catch (\Database\EDatabaseException $e) {
        }

        $this->assertSame([$one, $two], $this->linkedIds('notebooks_2_photos', 'notebook_id', $notebook));
    }
```

`Step0ListQueryCountTest.php`, class `Step0ListQueryCountTest extends TestCase`:
```php
    /** @dataProvider lists */
    public function testListQueryCountDoesNotGrowWithRows(string $kind, callable $findAll): void
    {
        $before = $this->countQueries($findAll);
        foreach ([1, 2, 3] as $n) {
            $id = $this->makeRow($kind);
            $table = $kind . 's_2_photos';
            $this->link($table, $kind . '_id', $id, $this->makePhoto("{$kind}{$n}"), 1);
        }
        $after = $this->countQueries($findAll);

        $this->assertSame($before, $after, "Three more {$kind}s cost " . ($after - $before) . ' more queries');
    }

    public function lists(): array
    {
        return [
            'notebooks' => ['notebook', fn ($db) => (new \Database\NotebookRepository($db))->findAll()],
            'workers' => ['worker', fn ($db) => (new \Database\WorkersRepository($db, 'en'))->findAll()],
        ];
    }

    private function countQueries(callable $findAll): int
    {
        $db = new CountingDb($this->db());
        $findAll($db);
        return $db->queries;
    }
```

- [ ] **Step 3: Run them and record which fail**

Run: `docker compose exec php vendor/bin/phpunit -c phpunit-docker.xml --group step0`
Expected on today's code: all four classes FAIL (wrong row; photo three missing; links `[]` or `[two]` instead of `[one, two]`; notebooks +6 and workers +12 queries). If one passes, that problem is not reproducible: keep the test, but mark it **dropped** in the report.

- [ ] **Step 4: Measure production list sizes (read-only)**

```bash
set -a; . ./.env; set +a
for k in parts workers moments marbles; do
  printf '%s ' $k; curl -s -H "X-API-Key: $MT3_API_KEY" https://db.marbletrack3.com/api/v1/$k | python3 -c 'import json,sys; d=json.load(sys.stdin); print(len(d if isinstance(d,list) else d.get("data", d.get(next(iter(d)), []))))'
done
```
Expected: one count per kind. Problem 4 is **proven** if any list is above ~50 rows (≥100 queries per page view); otherwise **dropped (lists are small)**.

- [ ] **Step 5: Commit the tests**

```bash
git add tests/Integration/RepositoryState
git commit -m "Prove the current-row problems with failing tests" \
  -m "Each test reproduces one way a repository's remembered row ID goes wrong: a save after a list, a form save wiping an API-added photo, a save dying between DELETE and INSERT, and photo queries per listed row. They are grouped step0 and stay red until their kind is converted." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

- [ ] **Step 6: Report to Rob and wait**

Tell Rob, one line per problem: **proven** or **dropped (why)**. Also mention that every current save path calls `set…Id()` right before saving, so problem 1 is a latent trap rather than a live bug. If anything is dropped, ask whether to shrink the remaining tasks before continuing.

### Task 3: Characterisation tests for today's photo and moment loading

**Files:**
- Create: `tests/Integration/RepositoryState/PhotoLoadingCharacterisationTest.php`
- Create: `tests/Integration/RepositoryState/PerspectiveMomentsCharacterisationTest.php`

**Interfaces:**
- Consumes: `PhotoFixtures` (Task 2).
- Produces: tests that must stay green through every later task without being edited, except that the `repoFor()` factory never changes.

- [ ] **Step 1: Write the photo characterisation test**

```php
<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Support/PhotoFixtures.php';

class PhotoLoadingCharacterisationTest extends TestCase
{
    use PhotoFixtures;

    protected function setUp(): void
    {
        $this->fixturesUp();
    }

    protected function tearDown(): void
    {
        $this->cleanupFixtures();
    }

    public function kinds(): array
    {
        return [
            'notebook' => ['notebook', 'notebooks_2_photos', 'notebook_id'],
            'page' => ['page', 'pages_2_photos', 'page_id'],
            'episode' => ['episode', 'episodes_2_photos', 'episode_id'],
            'moment' => ['moment', 'moments_2_photos', 'moment_id'],
            'worker' => ['worker', 'workers_2_photos', 'worker_id'],
            'part' => ['part', 'parts_2_photos', 'part_id'],
            'marble' => ['marble', 'marbles_2_photos', 'marble_id'],
        ];
    }

    private function repoFor(string $kind): object
    {
        $db = $this->db();
        return match ($kind) {
            'notebook' => new \Database\NotebookRepository($db),
            'page' => new \Database\PageRepository($db),
            'episode' => new \Database\EpisodeRepository($db),
            'moment' => new \Database\MomentRepository($db),
            'worker' => new \Database\WorkersRepository($db, 'en'),
            'part' => new \Database\PartsRepository($db, 'en'),
            'marble' => new \Database\MarblesRepository($db),
        };
    }

    /** @dataProvider kinds */
    public function testFindByIdReturnsPhotosInSortThenIdOrder(string $kind, string $table, string $key): void
    {
        $id = $this->makeRow($kind);
        $first = $this->makePhoto('first');
        $second = $this->makePhoto('second');
        $third = $this->makePhoto('third');
        // second and third share sort 2, so photo_id breaks the tie
        $this->link($table, $key, $id, $third, 2);
        $this->link($table, $key, $id, $first, 1);
        $this->link($table, $key, $id, $second, 2);

        $entity = $this->repoFor($kind)->findById($id);

        $this->assertSame([$first, $second, $third], $this->photoIds($entity->photos));
        foreach ($entity->photos as $photo) {
            $this->assertFalse($photo->isPrimary);
        }
    }

    /** @dataProvider kinds */
    public function testFindAllGivesEachRowItsOwnPhotos(string $kind, string $table, string $key): void
    {
        $withPhoto = $this->makeRow($kind);
        $without = $this->makeRow($kind);
        $photo = $this->makePhoto('only');
        $this->link($table, $key, $withPhoto, $photo, 1);

        $byId = [];
        foreach ($this->repoFor($kind)->findAll() as $entity) {
            $byId[$entity->{$key}] = $entity;
        }

        $this->assertSame([$photo], $this->photoIds($byId[$withPhoto]->photos));
        $this->assertSame([], $this->photoIds($byId[$without]->photos));
    }

    public function testIsPrimaryComesFromTheColumn(): void
    {
        $id = $this->makeRow('notebook');
        $plain = $this->makePhoto('plain');
        $flagged = $this->makePhoto('flagged');
        $this->link('notebooks_2_photos', 'notebook_id', $id, $plain, 1);
        $this->link('notebooks_2_photos', 'notebook_id', $id, $flagged, 2, 1);

        $photos = $this->repoFor('notebook')->findById($id)->photos;

        $this->assertFalse($photos[0]->isPrimary);
        $this->assertTrue($photos[1]->isPrimary);
    }

    public function testSharedPhotoCarriesEachRowsOwnPrimaryFlag(): void
    {
        $a = $this->makeRow('notebook');
        $b = $this->makeRow('notebook');
        $shared = $this->makePhoto('shared');
        $this->link('notebooks_2_photos', 'notebook_id', $a, $shared, 1, 1);
        $this->link('notebooks_2_photos', 'notebook_id', $b, $shared, 1, 0);

        $byId = [];
        foreach ($this->repoFor('notebook')->findAll() as $notebook) {
            $byId[$notebook->notebook_id] = $notebook;
        }

        $this->assertTrue($byId[$a]->photos[0]->isPrimary);
        $this->assertFalse($byId[$b]->photos[0]->isPrimary);
    }

    public function testPagePrimaryPhotoIsLastFlaggedElseFirst(): void
    {
        $unflagged = $this->makeRow('page');
        $flaggedPage = $this->makeRow('page');
        $one = $this->makePhoto('one');
        $two = $this->makePhoto('two');
        $this->link('pages_2_photos', 'page_id', $unflagged, $one, 1);
        $this->link('pages_2_photos', 'page_id', $unflagged, $two, 2);
        $this->link('pages_2_photos', 'page_id', $flaggedPage, $one, 1);
        $this->link('pages_2_photos', 'page_id', $flaggedPage, $two, 2, 1);

        $repo = $this->repoFor('page');

        $this->assertSame($one, $repo->findById($unflagged)->primaryPhoto->photo_id);
        $this->assertSame($two, $repo->findById($flaggedPage)->primaryPhoto->photo_id);
    }
}
```

- [ ] **Step 2: Write the perspective-moments characterisation test**

If Task 1 step 2 showed that `perspective_entity_type` has no `'marble'`, remove the `'marble'` row from `kinds()` and add a comment saying why.

```php
<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Support/PhotoFixtures.php';

class PerspectiveMomentsCharacterisationTest extends TestCase
{
    use PhotoFixtures;

    protected function setUp(): void
    {
        $this->fixturesUp();
    }

    protected function tearDown(): void
    {
        $this->cleanupFixtures();
    }

    public function kinds(): array
    {
        return [
            'worker' => ['worker', fn ($db) => new \Database\WorkersRepository($db, 'en')],
            'part' => ['part', fn ($db) => new \Database\PartsRepository($db, 'en')],
            'marble' => ['marble', fn ($db) => new \Database\MarblesRepository($db)],
        ];
    }

    /** @dataProvider kinds */
    public function testMomentsComeInTakeFrameOrderWithFlagsAndPhotos(string $kind, callable $repo): void
    {
        $owner = $this->makeRow($kind);
        $other = $this->makeRow($kind);
        $later = $this->makeMoment(30, 40, 'later');
        $earlier = $this->makeMoment(10, 20, 'earlier');
        $moments = new \Database\MomentRepository($this->db());
        $moments->createTranslationIfNotExists($later, $owner, $kind);
        $moments->createTranslationIfNotExists($earlier, $owner, $kind);
        $moments->createTranslationIfNotExists($earlier, $other, $kind);
        $moments->updateSignificance($later, $owner, $kind, true);
        $photo = $this->makePhoto('moment-photo');
        $this->link('moments_2_photos', 'moment_id', $later, $photo, 1);

        $byId = [];
        foreach ($repo($this->db())->findAll() as $entity) {
            $byId[$entity->{$kind . '_id'}] = $entity;
        }
        $mine = $byId[$owner]->moments;

        $this->assertSame([$earlier, $later], array_map(fn ($m) => $m->moment_id, $mine));
        $this->assertSame([10, 30], array_map(fn ($m) => $m->frame_start, $mine));
        $this->assertSame([false, true], array_map(fn ($m) => $m->is_significant, $mine));
        $this->assertSame([], $this->photoIds($mine[0]->photos));
        $this->assertSame([$photo], $this->photoIds($mine[1]->photos));
        $this->assertSame([$earlier], array_map(fn ($m) => $m->moment_id, $byId[$other]->moments));
        $this->assertSame(
            array_map(fn ($m) => $m->moment_id, $mine),
            array_map(fn ($m) => $m->moment_id, $repo($this->db())->findById($owner)->moments)
        );
    }
}
```

- [ ] **Step 3: Run them**

Run: `docker compose exec php vendor/bin/phpunit -c phpunit-docker.xml tests/Integration/RepositoryState --exclude-group step0`
Expected: PASS. These describe today's behaviour. If one fails, the test is wrong about today's behaviour, not the code: fix the test until it describes what the code does, and note any surprise in the commit body.

- [ ] **Step 4: Commit**

```bash
git add tests/Integration/RepositoryState/PhotoLoadingCharacterisationTest.php tests/Integration/RepositoryState/PerspectiveMomentsCharacterisationTest.php
git commit -m "Pin today's photo and moment loading in tests" \
  -m "The rewrite must not change what any page shows: photo order, the is_primary flag, Page's primary photo, and each Worker, Part and Marble's moments with their flags and photos. These pass now and must keep passing through every bubble." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

### Task 4: `DbInterface::inTransaction()`

**Files:**
- Modify: `classes/Database/DbInterface.php`
- Modify: `classes/Database/Database.php:309-323`
- Modify: `tests/Integration/RepositoryState/Support/CountingDb.php`
- Test: `tests/Integration/RepositoryState/InTransactionTest.php`

**Interfaces:**
- Produces: `DbInterface::inTransaction(): bool`. It is true between `beginTransaction()` and `commit()`/`rollBack()` on the same object.

- [ ] **Step 1: Write the failing test**

```php
<?php

use PHPUnit\Framework\TestCase;

class InTransactionTest extends TestCase
{
    public function testTracksBeginCommitAndRollBack(): void
    {
        $db = getTestDatabase();
        $this->assertFalse($db->inTransaction());
        $db->beginTransaction();
        $this->assertTrue($db->inTransaction());
        $db->commit();
        $this->assertFalse($db->inTransaction());
        $db->beginTransaction();
        $db->rollBack();
        $this->assertFalse($db->inTransaction());
    }
}
```

- [ ] **Step 2: Run it**

Run: `docker compose exec php vendor/bin/phpunit -c phpunit-docker.xml tests/Integration/RepositoryState/InTransactionTest.php`
Expected: FAIL with `Call to undefined method Database\Database::inTransaction()`.

- [ ] **Step 3: Implement**

In `DbInterface.php`, after `public function rollBack(): void;` add:
```php
    public function inTransaction(): bool;
```
In `Database.php`, add the field `private bool $inTransaction = false;` next to `private $affected_rows;`, and replace the three transaction methods with:
```php
    public function beginTransaction(): void
    {
        $this->connect();
        $this->dbObj->begin_transaction();
        $this->inTransaction = true;
    }

    public function commit(): void
    {
        $this->dbObj->commit();
        $this->inTransaction = false;
    }

    public function rollBack(): void
    {
        $this->dbObj->rollback();
        $this->inTransaction = false;
    }

    /** MySQL silently commits an open transaction on a second BEGIN, so writers check this first. */
    public function inTransaction(): bool
    {
        return $this->inTransaction;
    }
```
In `CountingDb.php`, after `rollBack()` add:
```php
    public function inTransaction(): bool
    {
        return $this->inner->inTransaction();
    }
```

- [ ] **Step 4: Run the test and the suite**

Run: the file, then the suite with `--exclude-group step0`.
Expected: PASS; nothing new red.

- [ ] **Step 5: Commit**

```bash
git add classes/Database/DbInterface.php classes/Database/Database.php tests/Integration/RepositoryState/Support/CountingDb.php tests/Integration/RepositoryState/InTransactionTest.php
git commit -m "Let callers ask whether a transaction is open" \
  -m "A second BEGIN in MySQL silently commits the first. Photo link writes will run inside handlers that already opened a transaction, so they need to join it rather than start their own." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

### Task 5: `Database\PhotoLinks`

**Files:**
- Create: `classes/Database/PhotoLinks.php`
- Test: `tests/Integration/RepositoryState/PhotoLinksTest.php`

**Interfaces:**
- Consumes: `DbInterface::inTransaction()` (Task 4), `PhotoRepository::findByIds(int[]): Photo[]`, `PhotoRepository::findOrCreateByUrls(string[]): Photo[]` (one result per input URL, in input order).
- Produces:
  - `new PhotoLinks(DbInterface $db, string $parentTable, string $linkTable, string $keyColumn)`
  - `forIds(int[] $ids): array<int, Photo[]>`
  - `forId(int $id): Photo[]`
  - `static primaryOf(Photo[] $photos): ?Photo`
  - `add(int $id, Photo[] $photos): void`
  - `addUrls(int $id, string[] $urls): void`
  - `saveFromForm(int $id, int[] $loadedIds, int[] $submittedIds): void`
  - `saveFormUrls(int $id, int[] $loadedIds, string[] $urls): void`
  - throws `\Database\EDatabaseException` when `$id` is not a row of `$parentTable`.

- [ ] **Step 1: Write the failing tests**

```php
<?php

use Database\PhotoLinks;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Support/PhotoFixtures.php';
require_once __DIR__ . '/Support/FailingDb.php';

class PhotoLinksTest extends TestCase
{
    use PhotoFixtures;

    protected function setUp(): void
    {
        $this->fixturesUp();
    }

    protected function tearDown(): void
    {
        if ($this->db()->inTransaction()) {
            $this->db()->rollBack();
        }
        $this->cleanupFixtures();
    }

    private function links($db = null): PhotoLinks
    {
        return new PhotoLinks($db ?? $this->db(), 'notebooks', 'notebooks_2_photos', 'notebook_id');
    }

    private function linked(int $id): array
    {
        return $this->linkedIds('notebooks_2_photos', 'notebook_id', $id);
    }

    public function testForIdsLoadsEveryRowInTwoQueries(): void
    {
        $a = $this->makeRow('notebook');
        $b = $this->makeRow('notebook');
        $empty = $this->makeRow('notebook');
        $p1 = $this->makePhoto('p1');
        $p2 = $this->makePhoto('p2');
        $this->link('notebooks_2_photos', 'notebook_id', $a, $p2, 2);
        $this->link('notebooks_2_photos', 'notebook_id', $a, $p1, 1);
        $this->link('notebooks_2_photos', 'notebook_id', $b, $p1, 1, 1);
        $counting = new CountingDb($this->db());

        $byId = $this->links($counting)->forIds([$a, $b, $empty]);

        $this->assertSame(2, $counting->queries);
        $this->assertSame([$p1, $p2], $this->photoIds($byId[$a]));
        $this->assertSame([$p1], $this->photoIds($byId[$b]));
        $this->assertArrayNotHasKey($empty, $byId);
        $this->assertFalse($byId[$a][0]->isPrimary);
        $this->assertTrue($byId[$b][0]->isPrimary);
        $this->assertSame([], $this->links()->forIds([]));
    }

    public function testPrimaryOfIsLastFlaggedElseFirst(): void
    {
        $one = new \Media\Photo(1, null, 'a');
        $two = new \Media\Photo(2, null, 'b');
        $this->assertNull(PhotoLinks::primaryOf([]));
        $this->assertSame($one, PhotoLinks::primaryOf([$one, $two]));
        $two->isPrimary = true;
        $this->assertSame($two, PhotoLinks::primaryOf([$one, $two]));
    }

    public function testAddAppendsAndSkipsPhotosAlreadyLinked(): void
    {
        $id = $this->makeRow('notebook');
        $p1 = $this->makePhoto('p1');
        $this->link('notebooks_2_photos', 'notebook_id', $id, $p1, 5);
        $this->makePhoto('new');   // tracked, so cleanup removes it

        $this->links()->addUrls($id, [$this->url('p1'), $this->url('new')]);

        $linked = $this->linked($id);
        $this->assertCount(2, $linked);
        $this->assertSame($p1, $linked[0]);
        $sort = $this->db()->fetchResults(
            'SELECT photo_sort FROM notebooks_2_photos WHERE notebook_id = ? AND photo_id = ?', 'ii', [$id, $linked[1]]
        )->toArray()[0]['photo_sort'];
        $this->assertSame(6, (int) $sort);
    }

    public function testFormSaveRemovesOnlyWhatTheFormDeleted(): void
    {
        $id = $this->makeRow('notebook');
        [$p1, $p2, $api] = [$this->makePhoto('p1'), $this->makePhoto('p2'), $this->makePhoto('api')];
        $this->link('notebooks_2_photos', 'notebook_id', $id, $p1, 1);
        $this->link('notebooks_2_photos', 'notebook_id', $id, $p2, 2);
        $this->link('notebooks_2_photos', 'notebook_id', $id, $api, 3);   // added after the form loaded

        $this->links()->saveFromForm($id, [$p1, $p2], [$p2]);

        $this->assertSame([$p2, $api], $this->linked($id));
    }

    public function testFormOrderWinsAndApiExtrasGoLast(): void
    {
        $id = $this->makeRow('notebook');
        [$p1, $p2, $api] = [$this->makePhoto('p1'), $this->makePhoto('p2'), $this->makePhoto('api')];
        $this->link('notebooks_2_photos', 'notebook_id', $id, $api, 1);
        $this->link('notebooks_2_photos', 'notebook_id', $id, $p1, 2);
        $this->link('notebooks_2_photos', 'notebook_id', $id, $p2, 3);
        $new = $this->makePhoto('new');

        $this->links()->saveFromForm($id, [$p1, $p2], [$new, $p2, $p1]);

        $this->assertSame([$new, $p2, $p1, $api], $this->linked($id));
    }

    public function testClearingFormKeepsApiExtras(): void
    {
        $id = $this->makeRow('notebook');
        [$p1, $api] = [$this->makePhoto('p1'), $this->makePhoto('api')];
        $this->link('notebooks_2_photos', 'notebook_id', $id, $p1, 1);
        $this->link('notebooks_2_photos', 'notebook_id', $id, $api, 2);

        $this->links()->saveFormUrls($id, [$p1], ['', '']);

        $this->assertSame([$api], $this->linked($id));
    }

    public function testMissingLoadedListRemovesNothing(): void
    {
        $id = $this->makeRow('notebook');
        [$p1, $p2] = [$this->makePhoto('p1'), $this->makePhoto('p2')];
        $this->link('notebooks_2_photos', 'notebook_id', $id, $p1, 1);
        $this->link('notebooks_2_photos', 'notebook_id', $id, $p2, 2);
        $new = $this->makePhoto('new');

        $this->links()->saveFormUrls($id, [], [$this->url('p2'), $this->url('new')]);

        $this->assertSame([$p2, $new, $p1], $this->linked($id));
    }

    public function testUnchangedFormKeepsCodedPhoto(): void
    {
        $id = $this->makeRow('notebook');
        $this->db()->executeSQL('INSERT INTO photos (code, url) VALUES (?, ?)', 'ss', [$this->prefix . 'code', $this->url('raw')]);
        $coded = $this->db()->insertId();
        $this->link('notebooks_2_photos', 'notebook_id', $id, $coded, 1);
        $shown = (new \Database\PhotoRepository($this->db()))->findById($coded)->getUrl();   // the CloudFront URL
        $photosBefore = (int) $this->db()->fetchResults('SELECT COUNT(*) AS n FROM photos')->toArray()[0]['n'];

        $this->links()->saveFormUrls($id, [$coded], [$shown]);

        $this->assertSame([$coded], $this->linked($id));
        $this->assertSame($photosBefore, (int) $this->db()->fetchResults('SELECT COUNT(*) AS n FROM photos')->toArray()[0]['n']);
        $this->db()->executeSQL('DELETE FROM photos WHERE photo_id = ?', 'i', [$coded]);
    }

    public function testDuplicateUrlLinksOnce(): void
    {
        $id = $this->makeRow('notebook');
        $this->makePhoto('dup');

        $this->links()->saveFormUrls($id, [], [$this->url('dup'), $this->url('dup')]);

        $this->assertCount(1, $this->linked($id));
    }

    public function testBlankUrlsIgnored(): void
    {
        $id = $this->makeRow('notebook');
        $p1 = $this->makePhoto('p1');

        $this->links()->saveFormUrls($id, [], ['', '  ', $this->url('p1'), '']);

        $this->assertSame([$p1], $this->linked($id));
    }

    public function testFailedFormSaveRollsBackEverything(): void
    {
        $id = $this->makeRow('notebook');
        [$p1, $p2, $p3] = [$this->makePhoto('p1'), $this->makePhoto('p2'), $this->makePhoto('p3')];
        $this->link('notebooks_2_photos', 'notebook_id', $id, $p1, 1);
        $this->link('notebooks_2_photos', 'notebook_id', $id, $p2, 2);

        try {
            // write 1 = DELETE p1, write 2 = UPDATE p2's sort -> dies
            $this->links(new FailingDb($this->db(), 2))->saveFromForm($id, [$p1, $p2], [$p2, $p3]);
            $this->fail('Expected the injected failure');
        } catch (\Database\EDatabaseException $e) {
        }

        $this->assertSame([$p1, $p2], $this->linked($id));
        $this->assertFalse($this->db()->inTransaction());
    }

    public function testJoinsTheCallersTransaction(): void
    {
        $id = $this->makeRow('notebook');
        $p1 = $this->makePhoto('p1');

        $this->db()->beginTransaction();
        $this->links()->saveFromForm($id, [], [$p1]);
        $this->assertTrue($this->db()->inTransaction(), 'PhotoLinks must not commit the caller\'s transaction');
        $this->db()->rollBack();

        $this->assertSame([], $this->linked($id));
    }

    public function testUnknownRowThrows(): void
    {
        $this->expectException(\Database\EDatabaseException::class);
        $this->links()->saveFromForm(2147483000, [], [$this->makePhoto('p1')]);
    }
}
```

- [ ] **Step 2: Run the tests**

Run: `docker compose exec php vendor/bin/phpunit -c phpunit-docker.xml tests/Integration/RepositoryState/PhotoLinksTest.php`
Expected: FAIL, `Class "Database\PhotoLinks" not found`.

- [ ] **Step 3: Implement `classes/Database/PhotoLinks.php`**

```php
<?php
namespace Database;

use Media\Photo;

/**
 * Photo links for one kind of row (notebooks, parts, ...).
 *
 * Stateless: every method takes the row's ID, so a save can never land on
 * whichever row a repository happened to load last.
 */
class PhotoLinks
{
    public function __construct(
        private DbInterface $db,
        private string $parentTable,
        private string $linkTable,
        private string $keyColumn
    ) {
    }

    /**
     * @param int[] $ids
     * @return array<int, Photo[]> rows with no photos are absent
     */
    public function forIds(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $links = $this->db->fetchResults(
            "SELECT {$this->keyColumn} AS row_id, photo_id, is_primary
             FROM {$this->linkTable}
             WHERE {$this->keyColumn} IN ($placeholders)
             ORDER BY {$this->keyColumn} ASC, photo_sort ASC, photo_id ASC",
            str_repeat('i', count($ids)),
            $ids
        )->toArray();

        $photos = [];
        $photoIds = array_values(array_unique(array_map(fn (array $link) => (int) $link['photo_id'], $links)));
        foreach ((new PhotoRepository($this->db))->findByIds($photoIds) as $photo) {
            $photos[$photo->photo_id] = $photo;
        }

        $byRow = [];
        foreach ($links as $link) {
            $photoId = (int) $link['photo_id'];
            if (!isset($photos[$photoId])) {
                continue;   // link to a deleted photo; today's loader skips these too
            }
            // is_primary belongs to the link, so each row gets its own copy
            $photo = clone $photos[$photoId];
            $photo->isPrimary = !empty($link['is_primary']);
            $byRow[(int) $link['row_id']][] = $photo;
        }
        return $byRow;
    }

    /** @return Photo[] */
    public function forId(int $id): array
    {
        return $this->forIds([$id])[$id] ?? [];
    }

    /** The last photo flagged primary, else the first: what HasPhotos::getPrimaryPhoto() returned. */
    public static function primaryOf(array $photos): ?Photo
    {
        $primary = null;
        foreach ($photos as $photo) {
            if ($photo->isPrimary) {
                $primary = $photo;
            }
        }
        return $primary ?? ($photos[0] ?? null);
    }

    /**
     * Append photos after the row's current last one, skipping any already linked.
     * Used by the API and uploaders, which only ever add.
     *
     * @param Photo[] $photos
     */
    public function add(int $id, array $photos): void
    {
        $this->atomically(function () use ($id, $photos) {
            $this->assertRowExists($id);
            $current = $this->currentSorts($id);
            $sort = empty($current) ? 1 : max($current) + 1;
            foreach ($photos as $photo) {
                if (isset($current[$photo->photo_id])) {
                    continue;
                }
                $this->db->executeSQL(
                    "INSERT INTO {$this->linkTable} ({$this->keyColumn}, photo_id, photo_sort, is_primary) VALUES (?, ?, ?, ?)",
                    'iiii',
                    [$id, $photo->photo_id, $sort, $photo->isPrimary ? 1 : 0]
                );
                $current[$photo->photo_id] = $sort++;
            }
        });
    }

    /** @param string[] $urls */
    public function addUrls(int $id, array $urls): void
    {
        $this->add($id, (new PhotoRepository($this->db))->findOrCreateByUrls($urls));
    }

    /**
     * Save an edit form's photo list.
     *
     * Removes only photos the form loaded and no longer lists, so a photo
     * attached elsewhere while the form was open survives. The form's order
     * wins; photos the form never saw keep their relative order after it.
     *
     * @param int[] $loadedIds    photo IDs the form showed when it loaded
     * @param int[] $submittedIds photo IDs the form lists now, in screen order
     */
    public function saveFromForm(int $id, array $loadedIds, array $submittedIds): void
    {
        $loaded = array_map('intval', $loadedIds);
        $submitted = array_values(array_unique(array_map('intval', $submittedIds)));

        $this->atomically(function () use ($id, $loaded, $submitted) {
            $this->assertRowExists($id);
            $current = $this->currentSorts($id);

            foreach (array_diff($loaded, $submitted) as $photoId) {
                if (!isset($current[$photoId])) {
                    continue;
                }
                $this->db->executeSQL(
                    "DELETE FROM {$this->linkTable} WHERE {$this->keyColumn} = ? AND photo_id = ?",
                    'ii',
                    [$id, $photoId]
                );
                unset($current[$photoId]);
            }

            $extras = array_values(array_diff(array_keys($current), $submitted));
            $sort = 1;
            foreach (array_merge($submitted, $extras) as $photoId) {
                if (!isset($current[$photoId])) {
                    $this->db->executeSQL(
                        "INSERT INTO {$this->linkTable} ({$this->keyColumn}, photo_id, photo_sort, is_primary) VALUES (?, ?, ?, 0)",
                        'iii',
                        [$id, $photoId, $sort]
                    );
                } elseif ($current[$photoId] !== $sort) {
                    $this->db->executeSQL(
                        "UPDATE {$this->linkTable} SET photo_sort = ? WHERE {$this->keyColumn} = ? AND photo_id = ?",
                        'iii',
                        [$sort, $id, $photoId]
                    );
                }
                $sort++;
            }
        });
    }

    /**
     * saveFromForm() for forms that post URLs. A URL the form displayed for a
     * loaded photo maps back to that photo, even when it is a CDN URL built
     * from the photo's code rather than its url column.
     *
     * @param int[]    $loadedIds
     * @param string[] $urls
     */
    public function saveFormUrls(int $id, array $loadedIds, array $urls): void
    {
        $this->saveFromForm($id, $loadedIds, $this->idsForUrls($loadedIds, $urls));
    }

    /** @return int[] one photo ID per non-blank URL, in order */
    private function idsForUrls(array $loadedIds, array $urls): array
    {
        $urls = array_values(array_filter(array_map('trim', $urls), fn (string $url) => $url !== ''));
        if (empty($urls)) {
            return [];
        }

        $photoRepo = new PhotoRepository($this->db);
        $resolved = [];
        foreach ($photoRepo->findByIds(array_map('intval', $loadedIds)) as $photo) {
            $resolved[$photo->getUrl()] = $photo->photo_id;
        }

        $unknown = array_values(array_unique(array_filter($urls, fn (string $url) => !isset($resolved[$url]))));
        foreach ($photoRepo->findOrCreateByUrls($unknown) as $i => $photo) {
            $resolved[$unknown[$i]] = $photo->photo_id;
        }

        return array_map(fn (string $url) => $resolved[$url], $urls);
    }

    /** @return array<int, int> photo_id => photo_sort, in display order */
    private function currentSorts(int $id): array
    {
        $current = [];
        $rows = $this->db->fetchResults(
            "SELECT photo_id, photo_sort FROM {$this->linkTable} WHERE {$this->keyColumn} = ? ORDER BY photo_sort ASC, photo_id ASC",
            'i',
            [$id]
        )->toArray();
        foreach ($rows as $row) {
            $current[(int) $row['photo_id']] = (int) $row['photo_sort'];
        }
        return $current;
    }

    private function assertRowExists(int $id): void
    {
        $found = $this->db->fetchResults(
            "SELECT 1 FROM {$this->parentTable} WHERE {$this->keyColumn} = ?",
            'i',
            [$id]
        );
        if ($found->numRows() === 0) {
            throw new EDatabaseException("No {$this->parentTable} row with {$this->keyColumn} = {$id}");
        }
    }

    /** Run $work in a transaction, joining the caller's if one is already open. */
    private function atomically(callable $work): void
    {
        if ($this->db->inTransaction()) {
            $work();
            return;
        }
        $this->db->beginTransaction();
        try {
            $work();
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
```

- [ ] **Step 4: Run the tests**

Run: the `PhotoLinksTest.php` file.
Expected: PASS (13 tests). If `testFailedFormSaveRollsBackEverything` fails because the injected failure lands on a different write, print the writes FailingDb sees and adjust `failOnWrite` so it hits the UPDATE. Do not change `PhotoLinks` to suit the test.

- [ ] **Step 5: Commit**

```bash
git add classes/Database/PhotoLinks.php tests/Integration/RepositoryState/PhotoLinksTest.php
git commit -m "Add PhotoLinks, which takes every row ID explicitly" \
  -m "Photo links move out of the HasPhotos trait, which read whatever ID its repository last remembered. Form saves now remove only what the form deleted, so photos the API attached meanwhile survive, and the whole save is one transaction that joins the caller's when there is one. Lists load every row's photos in two queries." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

### Task 6: `Database\PerspectiveMoments`

**Files:**
- Create: `classes/Database/PerspectiveMoments.php`
- Test: `tests/Integration/RepositoryState/PerspectiveMomentsTest.php`

**Interfaces:**
- Produces: `new PerspectiveMoments(DbInterface $db)`, `forPerspectives(string $type, int[] $ids): array<int, Moment[]>` (perspectives with no moments are absent).

- [ ] **Step 1: Write the failing test**

```php
<?php

use Database\PerspectiveMoments;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Support/PhotoFixtures.php';

class PerspectiveMomentsTest extends TestCase
{
    use PhotoFixtures;

    protected function setUp(): void
    {
        $this->fixturesUp();
    }

    protected function tearDown(): void
    {
        $this->cleanupFixtures();
    }

    public function testLoadsManyPerspectivesInTwoQueries(): void
    {
        $a = $this->makeRow('worker');
        $b = $this->makeRow('worker');
        $none = $this->makeRow('worker');
        $late = $this->makeMoment(50, 60, 'late');
        $early = $this->makeMoment(5, 6, 'early');
        $moments = new \Database\MomentRepository($this->db());
        $moments->createTranslationIfNotExists($late, $a, 'worker');
        $moments->createTranslationIfNotExists($early, $a, 'worker');
        $moments->createTranslationIfNotExists($late, $b, 'worker');
        $moments->updateSignificance($late, $b, 'worker', true);
        $photo = $this->makePhoto('m');
        $this->link('moments_2_photos', 'moment_id', $late, $photo, 1);
        $counting = new CountingDb($this->db());

        $byWorker = (new PerspectiveMoments($counting))->forPerspectives('worker', [$a, $b, $none]);

        $this->assertSame(2, $counting->queries);
        $this->assertSame([$early, $late], array_map(fn ($m) => $m->moment_id, $byWorker[$a]));
        $this->assertSame([$late], array_map(fn ($m) => $m->moment_id, $byWorker[$b]));
        $this->assertFalse($byWorker[$a][1]->is_significant);
        $this->assertTrue($byWorker[$b][0]->is_significant);
        $this->assertSame([$photo], $this->photoIds($byWorker[$b][0]->photos));
        $this->assertArrayNotHasKey($none, $byWorker);
        $this->assertSame([], (new PerspectiveMoments($this->db()))->forPerspectives('worker', []));
    }
}
```
`CountingDb` must be loaded: add `require_once __DIR__ . '/Support/CountingDb.php';` under the other `require_once`.

- [ ] **Step 2: Run it**

Expected: FAIL, `Class "Database\PerspectiveMoments" not found`.

- [ ] **Step 3: Implement `classes/Database/PerspectiveMoments.php`**

```php
<?php
namespace Database;

use Media\Moment;
use Media\Photo;

/**
 * Moments seen from Workers', Parts' or Marbles' perspectives, for many of
 * them at once. A moment belongs to a perspective when it has a translation
 * for it; the *_2_moments linking tables are not used.
 */
class PerspectiveMoments
{
    public function __construct(private DbInterface $db)
    {
    }

    /**
     * @param string $type 'worker', 'part' or 'marble'
     * @param int[]  $ids
     * @return array<int, Moment[]> keyed by perspective ID; ordered by take, then frame
     */
    public function forPerspectives(string $type, array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = $this->db->fetchResults(
            "SELECT mt.perspective_entity_id AS perspective_id, m.moment_id, m.frame_start, m.frame_end, m.take_id,
                    COALESCE(mt.translated_note, m.notes) AS notes, m.moment_date, mt.is_significant
             FROM moments m
             JOIN moment_translations mt ON m.moment_id = mt.moment_id
             WHERE mt.perspective_entity_type = ? AND mt.perspective_entity_id IN ($placeholders)
             ORDER BY m.take_id ASC, m.frame_start ASC",
            's' . str_repeat('i', count($ids)),
            array_merge([$type], $ids)
        )->toArray();
        if (empty($rows)) {
            return [];
        }

        $photosByMoment = $this->photosFor(array_map(fn (array $row) => (int) $row['moment_id'], $rows));

        $byPerspective = [];
        foreach ($rows as $row) {
            $moment = new Moment(
                moment_id: (int) $row['moment_id'],
                frame_start: isset($row['frame_start']) ? (int) $row['frame_start'] : null,
                frame_end: isset($row['frame_end']) ? (int) $row['frame_end'] : null,
                take_id: isset($row['take_id']) ? (int) $row['take_id'] : null,
                notes: $row['notes'] ?? null,
                moment_date: $row['moment_date'] ?? null
            );
            $moment->is_significant = (bool) ($row['is_significant'] ?? false);
            $moment->photos = $photosByMoment[$moment->moment_id] ?? [];
            $byPerspective[(int) $row['perspective_id']][] = $moment;
        }
        return $byPerspective;
    }

    /** @return array<int, Photo[]> */
    private function photosFor(array $momentIds): array
    {
        $momentIds = array_values(array_unique($momentIds));
        $placeholders = implode(',', array_fill(0, count($momentIds), '?'));
        $rows = $this->db->fetchResults(
            "SELECT p.photo_id, p.code, p.url, m2p.moment_id
             FROM photos p
             JOIN moments_2_photos m2p ON p.photo_id = m2p.photo_id
             WHERE m2p.moment_id IN ($placeholders)",
            str_repeat('i', count($momentIds)),
            $momentIds
        )->toArray();

        $photos = [];
        foreach ($rows as $row) {
            $photos[(int) $row['moment_id']][] = new Photo(
                photo_id: (int) $row['photo_id'],
                code: $row['code'] ?? null,
                url: $row['url'] ?? null
            );
        }
        return $photos;
    }
}
```

- [ ] **Step 4: Run it**

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add classes/Database/PerspectiveMoments.php tests/Integration/RepositoryState/PerspectiveMomentsTest.php
git commit -m "Add PerspectiveMoments for loading many rows at once" \
  -m "HasMoments ran two queries per Worker, Part or Marble because hydrate() loaded one row's moments at a time. This is the same query with IN (...), so a whole list costs two." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

### Task 7: `\Utilities::postedIds()`

**Files:**
- Modify: `classes/Utilities.php` (add method after `slugify()`)
- Test: `tests/Unit/PostedIdsTest.php`

**Interfaces:**
- Produces: `\Utilities::postedIds(string $field): int[]` reads `$_POST[$field]` as a list. It drops anything that is not a positive integer, and returns `[]` when the field is missing or is not an array.

- [ ] **Step 1: Write the failing test**

```php
<?php

use PHPUnit\Framework\TestCase;

class PostedIdsTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_POST['loaded_photo_ids']);
    }

    public function testReadsPositiveIntegersInOrder(): void
    {
        $_POST['loaded_photo_ids'] = ['12', '7', 'x', '-3', '0', '7'];
        $this->assertSame([12, 7, 7], \Utilities::postedIds('loaded_photo_ids'));
    }

    public function testMissingOrScalarFieldIsEmpty(): void
    {
        $this->assertSame([], \Utilities::postedIds('loaded_photo_ids'));
        $_POST['loaded_photo_ids'] = '12';
        $this->assertSame([], \Utilities::postedIds('loaded_photo_ids'));
    }
}
```

- [ ] **Step 2: Run it**

Run: `docker compose exec php vendor/bin/phpunit -c phpunit-docker.xml tests/Unit/PostedIdsTest.php`
Expected: FAIL, `Call to undefined method Utilities::postedIds()`.

- [ ] **Step 3: Implement**

```php
    /**
     * IDs posted as name="field[]". Anything that is not a positive integer is
     * dropped, and a missing field reads as no IDs.
     *
     * @return int[]
     */
    public static function postedIds(string $field): array
    {
        $raw = $_POST[$field] ?? [];
        if (!is_array($raw)) {
            return [];
        }
        return array_values(array_filter(array_map('intval', $raw), fn (int $id) => $id > 0));
    }
```

- [ ] **Step 4: Run it**

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add classes/Utilities.php tests/Unit/PostedIdsTest.php
git commit -m "Read posted ID lists in one place" \
  -m "Seven admin forms will post back the photo IDs they loaded. They read \$_POST directly rather than through RobRequest, so the parsing lives on Utilities." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

### Task 8: Read-only production snapshot script

**Files:**
- Create: `scripts/photo_links_snapshot.php`
- Test: `tests/Integration/RepositoryState/SnapshotScriptTest.php`

**Interfaces:**
- Produces: the CLI used by every Bubble Gate:
  - no args → JSON `{link_table: {row_id: [photo_id, …]}}` on stdout
  - `--diff A.json B.json` → one line per changed row; exit 1 if any changed
  - `--repos` → `<table>: N rows checked, M mismatches` per table, plus details; exit 1 on any mismatch

- [ ] **Step 1: Write the failing test**

```php
<?php

use PHPUnit\Framework\TestCase;

class SnapshotScriptTest extends TestCase
{
    private function run(string $args): array
    {
        exec('php /app/scripts/photo_links_snapshot.php ' . $args . ' 2>&1', $out, $code);
        return [implode("\n", $out), $code];
    }

    public function testDiffReportsOnlyChangedRows(): void
    {
        $dir = sys_get_temp_dir();
        file_put_contents("$dir/a.json", json_encode(['parts_2_photos' => ['1' => [5, 6], '2' => [7]]]));
        file_put_contents("$dir/b.json", json_encode(['parts_2_photos' => ['1' => [6, 5], '2' => [7]]]));

        [$out, $code] = $this->run("--diff $dir/a.json $dir/b.json");

        $this->assertSame(1, $code);
        $this->assertStringContainsString('parts_2_photos 1: [5,6] -> [6,5]', $out);
        $this->assertStringNotContainsString('parts_2_photos 2', $out);
        [$same, $sameCode] = $this->run("--diff $dir/a.json $dir/a.json");
        $this->assertSame(['', 0], [$same, $sameCode]);
    }

    public function testSnapshotAndReposRunAgainstTheTestDatabase(): void
    {
        [$json, $code] = $this->run('');
        $this->assertSame(0, $code);
        $this->assertArrayHasKey('notebooks_2_photos', json_decode($json, true));

        [$out, $reposCode] = $this->run('--repos');
        $this->assertSame(0, $reposCode, $out);
        $this->assertStringContainsString('marbles_2_photos:', $out);
    }
}
```

- [ ] **Step 2: Run it**

Expected: FAIL (the script does not exist; exit code 1 with "Could not open input file").

- [ ] **Step 3: Implement `scripts/photo_links_snapshot.php`**

```php
<?php
/**
 * Read-only check that photo links survive a deploy. Issues SELECTs only.
 *
 *   php scripts/photo_links_snapshot.php                     JSON of every *_2_photos table
 *   php scripts/photo_links_snapshot.php --diff A.json B.json rows whose photo list changed
 *   php scripts/photo_links_snapshot.php --repos              repository findAll() vs the tables
 *
 * Bootstraps without prepend.php so it never starts a session or renders HTML.
 */
require_once __DIR__ . '/../classes/Mlaphp/Autoloader.php';
spl_autoload_register([new \Mlaphp\Autoloader(), 'load']);

const LINKS = [
    'notebooks_2_photos' => 'notebook_id',
    'pages_2_photos' => 'page_id',
    'episodes_2_photos' => 'episode_id',
    'moments_2_photos' => 'moment_id',
    'workers_2_photos' => 'worker_id',
    'parts_2_photos' => 'part_id',
    'marbles_2_photos' => 'marble_id',
];

function snapshot(\Database\DbInterface $db): array
{
    $out = [];
    foreach (LINKS as $table => $key) {
        $out[$table] = [];
        $rows = $db->fetchResults(
            "SELECT {$key} AS row_id, photo_id FROM {$table} ORDER BY {$key} ASC, photo_sort ASC, photo_id ASC"
        )->toArray();
        foreach ($rows as $row) {
            $out[$table][(string) $row['row_id']][] = (int) $row['photo_id'];
        }
    }
    return $out;
}

function diff(array $before, array $after): array
{
    $lines = [];
    foreach (array_unique(array_merge(array_keys($before), array_keys($after))) as $table) {
        $a = $before[$table] ?? [];
        $b = $after[$table] ?? [];
        foreach (array_unique(array_merge(array_keys($a), array_keys($b))) as $rowId) {
            $was = $a[$rowId] ?? [];
            $now = $b[$rowId] ?? [];
            if ($was !== $now) {
                $lines[] = "{$table} {$rowId}: " . json_encode($was) . ' -> ' . json_encode($now);
            }
        }
    }
    return $lines;
}

function checkRepos(\Database\DbInterface $db): bool
{
    $snap = snapshot($db);
    $existing = [];
    foreach ($db->fetchResults('SELECT photo_id FROM photos')->toArray() as $row) {
        $existing[(int) $row['photo_id']] = true;
    }
    $finders = [
        'notebooks_2_photos' => fn () => (new \Database\NotebookRepository($db))->findAll(),
        'pages_2_photos' => fn () => (new \Database\PageRepository($db))->findAll(),
        'episodes_2_photos' => fn () => (new \Database\EpisodeRepository($db))->findAll(),
        'moments_2_photos' => fn () => (new \Database\MomentRepository($db))->findAll(),
        'workers_2_photos' => fn () => (new \Database\WorkersRepository($db, 'en'))->findAll(),
        'parts_2_photos' => fn () => (new \Database\PartsRepository($db, 'en'))->findAll(),
        'marbles_2_photos' => fn () => (new \Database\MarblesRepository($db))->findAll(),
    ];
    $ok = true;
    foreach ($finders as $table => $findAll) {
        $key = LINKS[$table];
        $checked = 0;
        $bad = [];
        foreach ($findAll() as $entity) {
            $rowId = (string) $entity->{$key};
            // links to deleted photos are skipped by every loader, old and new
            $expected = array_values(array_filter($snap[$table][$rowId] ?? [], fn (int $id) => isset($existing[$id])));
            $actual = array_map(fn ($photo) => $photo->photo_id, $entity->photos);
            if ($expected !== $actual) {
                $bad[] = "  {$rowId}: table " . json_encode($expected) . ' repo ' . json_encode($actual);
            }
            $checked++;
        }
        echo "{$table}: {$checked} rows checked, " . count($bad) . " mismatches\n";
        foreach ($bad as $line) {
            echo $line, "\n";
        }
        $ok = $ok && empty($bad);
    }
    return $ok;
}

$args = array_slice($argv, 1);
if (($args[0] ?? '') === '--diff') {
    $lines = diff(
        json_decode(file_get_contents($args[1]), true),
        json_decode(file_get_contents($args[2]), true)
    );
    foreach ($lines as $line) {
        echo $line, "\n";
    }
    exit(empty($lines) ? 0 : 1);
}

$db = \Database\Base::getDB(new \Config());
if (($args[0] ?? '') === '--repos') {
    exit(checkRepos($db) ? 0 : 1);
}
echo json_encode(snapshot($db), JSON_PRETTY_PRINT), "\n";
```

- [ ] **Step 4: Run it**

Expected: PASS. Note: in Docker, `Config` points `dbName` at `dbmt3_test`, so the script reads the test DB.

- [ ] **Step 5: Commit**

```bash
git add scripts/photo_links_snapshot.php tests/Integration/RepositoryState/SnapshotScriptTest.php
git commit -m "Add a read-only photo link snapshot for deploys" \
  -m "Each bubble is deployed straight to production, so production needs a check that only reads: every link table before and after, a diff, and a pass that compares what the repositories return with what the tables hold." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

### Task 9: Gate for bubble 1 (baseline)

- [ ] **Step 1: Write the form-reorder helper** (scratch tool, not committed) to `$SCRATCH/form_reorder.py`:

```python
#!/usr/bin/env python3
"""Load an admin edit form, swap its first two photos, submit it. Usage: form_reorder.py URL COOKIE_FILE"""
import sys, urllib.request, urllib.parse
from html.parser import HTMLParser

url, cookie = sys.argv[1], open(sys.argv[2]).read().strip()

def fetch(u, data=None):
    req = urllib.request.Request(u, data=data, headers={'Cookie': cookie})
    with urllib.request.urlopen(req) as r:
        return r.status, r.geturl(), r.read().decode('utf-8', 'replace')

class Form(HTMLParser):
    def __init__(self):
        super().__init__(); self.fields = []; self.action = None; self.depth = 0
        self.textarea = None; self.select = None
    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        if tag == 'form' and self.action is None and a.get('method', '').lower() == 'post':
            self.action = a.get('action') or url; self.depth = 1; return
        if not self.depth: return
        if tag == 'input' and a.get('name'):
            t = a.get('type', 'text').lower()
            if t in ('checkbox', 'radio') and 'checked' not in a: return
            if t in ('submit', 'button', 'file'): return
            self.fields.append([a['name'], a.get('value', '')])
        elif tag == 'textarea' and a.get('name'):
            self.textarea = [a['name'], '']; self.fields.append(self.textarea)
        elif tag == 'select' and a.get('name'):
            self.select = a['name']
        elif tag == 'option' and self.select and 'selected' in a:
            self.fields.append([self.select, a.get('value', '')])
    def handle_data(self, data):
        if self.textarea is not None: self.textarea[1] += data
    def handle_endtag(self, tag):
        if tag == 'textarea': self.textarea = None
        if tag == 'select': self.select = None
        if tag == 'form' and self.depth: self.depth = 0

status, final, html = fetch(url)
f = Form(); f.feed(html)
urls = [i for i, (n, v) in enumerate(f.fields) if n in ('image_urls[]',) and v.strip()]
if urls and len(urls) >= 2:
    i, j = urls[0], urls[1]
    f.fields[i][1], f.fields[j][1] = f.fields[j][1], f.fields[i][1]
else:
    ta = [x for x in f.fields if x[0] == 'photo_urls']
    lines = [l for l in ta[0][1].strip().split('\n') if l.strip()] if ta else []
    if len(lines) < 2: sys.exit('fewer than two photos on this form')
    lines[0], lines[1] = lines[1], lines[0]; ta[0][1] = '\n'.join(lines)
body = urllib.parse.urlencode(f.fields).encode()
status, final, _ = fetch(urllib.parse.urljoin(url, f.action), body)
print(status, final)
```

- [ ] **Step 2: Ask Rob for an admin session cookie.** Ask him to copy the `Cookie` request header from DevTools on any `/admin/` page into `$SCRATCH/admin_cookie.txt`, or paste it in chat. It is never committed. Write down `$APP` from Task 1 step 1.

- [ ] **Step 3: Run the Bubble Gate** with:
  - branch `stateless-repositories`
  - DONE message `DONE stateless Repositories foundation`
  - expected red Step 0 tests: all four classes
  - admin list URL `https://db.marbletrack3.com/admin/notebooks/index.php`
  - edit URL `https://db.marbletrack3.com/admin/notebooks/notebook.php?id=<id>`
  - API path `parts`
  - **G3 is skipped**: the snapshot script ships in this bubble. Take G5's snapshot as `$SCRATCH/baseline.json`; it is the "before" for bubble 2.
  - **G8 is skipped**: no form changed.
  - G6 is the important check here: it proves the script agrees with **today's** repositories on production data before any of them change.

---

# Bubble 2 — `stateless-notebooks`

### Task 10: NotebookRepository holds no current row

Start the bubble from the closed bubble-1 branch:
```bash
git switch -c stateless-notebooks
sed -i 's/^const SENTIMENTAL_VERSION = .*/const SENTIMENTAL_VERSION = "Stateless Notebooks";/' prepend.php
git commit -am "BEGIN stateless Notebooks" -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

**Files:**
- Modify: `classes/Database/NotebookRepository.php` (whole file)
- Modify: `tests/Integration/RepositoryState/Step0WrongRowTest.php`, `Step0AtomicSaveTest.php`, `Step0ListQueryCountTest.php` (Notebook calls move to the new API)

**Interfaces:**
- Consumes: `PhotoLinks` (Task 5).
- Produces: `NotebookRepository::savePhotosFromForm(int $notebook_id, int[] $loadedPhotoIds, string[] $urls): void`. Removed: `setNotebookId`, `getId`, `getDb`, `getPhotoLinkingTable`, `getPrimaryKeyColumn`, `savePhotosFromUrls`, and all other `HasPhotos` methods.

- [ ] **Step 1: Move the Notebook Step 0 tests to the new API** (the scenarios stay the same)

In `Step0WrongRowTest`, replace the three lines from `$repo->setNotebookId($a);` with:
```php
        $repo->findAll();                           // a list render on the way...
        $repo->savePhotosFromForm($a, [], [$this->url('meant-for-a')]);   // ...then a save that names its row
```
In `Step0AtomicSaveTest`, replace from `$repo->setNotebookId($notebook);` through the `try` body with:
```php
        try {
            // Rob removed photo one and added photo three; the second write dies.
            $repo->savePhotosFromForm($notebook, [$one, $two], [$this->url('two'), $this->url('three')]);
        } catch (\Database\EDatabaseException $e) {
        }
```

- [ ] **Step 2: Run them**

Run: `--group step0`
Expected: WrongRow and AtomicSave FAIL with `Call to undefined method NotebookRepository::savePhotosFromForm()`. The notebooks case of ListQueryCount still FAILS on the count.

- [ ] **Step 3: Rewrite `classes/Database/NotebookRepository.php`**

```php
<?php
namespace Database;

use Physical\Notebook;

class NotebookRepository
{
    private DbInterface $db;
    private PhotoLinks $photoLinks;

    public function __construct(DbInterface $db)
    {
        $this->db = $db;
        $this->photoLinks = new PhotoLinks($db, 'notebooks', 'notebooks_2_photos', 'notebook_id');
    }

    public function findById(int $notebook_id): ?Notebook
    {
        $results = $this->db->fetchResults(
            "SELECT notebook_id, title, created_at FROM notebooks WHERE notebook_id = ?",
            'i',
            [$notebook_id]
        );

        if ($results->numRows() === 0) {
            return null;
        }

        return $this->hydrateAll($results->toArray())[0];
    }

    public function findAll(): array
    {
        $results = $this->db->fetchResults(
            "SELECT notebook_id, title, created_at FROM notebooks ORDER BY notebook_id ASC"
        );

        return $this->hydrateAll($results->toArray());
    }

    public function insert(string $title = null, string $created_at = null): int
    {
        return $this->db->insertFromRecord(
            'notebooks',
            'ss',
            [
                'title' => $title,
                'created_at' => $created_at
            ]
        );
    }

    /**
     * @param int[]    $loadedPhotoIds photo IDs the edit form showed when it loaded
     * @param string[] $urls           the form's photo URLs, in screen order
     */
    public function savePhotosFromForm(int $notebook_id, array $loadedPhotoIds, array $urls): void
    {
        $this->photoLinks->saveFormUrls($notebook_id, $loadedPhotoIds, $urls);
    }

    /** @return Notebook[] */
    private function hydrateAll(array $rows): array
    {
        $photos = $this->photoLinks->forIds(array_column($rows, 'notebook_id'));
        $notebooks = [];
        foreach ($rows as $row) {
            $notebook = $this->hydrate($row);
            $notebook->photos = $photos[$notebook->notebook_id] ?? [];
            $notebooks[] = $notebook;
        }
        return $notebooks;
    }

    private function hydrate(array $row): Notebook
    {
        return new Notebook(
            notebook_id: (int) $row['notebook_id'],
            title: $row['title'] ?? null,
            created_at: $row['created_at'] ?? null
        );
    }
}
```

- [ ] **Step 4: Run the Notebook tests**

Run: `--group step0`, then `PhotoLoadingCharacterisationTest.php`.
Expected: WrongRow, AtomicSave and the notebooks case of ListQueryCount PASS. ApiPhotoSurvivesForm (parts) and the workers case still FAIL. All characterisation tests PASS.

- [ ] **Step 5: Confirm no caller still uses the removed methods**

Run: `grep -rn -E 'NotebookRepository|setNotebookId' wwwroot templates classes scripts | grep -v 'classes/Database/NotebookRepository.php'`
Expected: only `new NotebookRepository(` lines, plus `wwwroot/admin/notebooks/notebook.php:35` and `:43` (`setNotebookId`, `savePhotosFromUrls`), which Task 11 fixes. The commit window between Tasks 10 and 11 is a known broken form; do not deploy between them.

- [ ] **Step 6: Commit**

```bash
git add classes/Database/NotebookRepository.php tests/Integration/RepositoryState
git commit -m "Stop NotebookRepository remembering a current row" \
  -m "Photos now load for the whole list in two queries, and a save names its notebook instead of trusting whichever one the repository last loaded. The form handler is switched over in the next commit." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

### Task 11: Notebook form posts the photos it loaded

**Files:**
- Modify: `templates/admin/notebooks/notebook.tpl.php` (after the `<form` opening tag)
- Modify: `wwwroot/admin/notebooks/notebook.php:35-36,43-44`
- Test: `tests/Integration/RepositoryState/FormLoadedIdsTest.php`

**Interfaces:**
- Consumes: `\Utilities::postedIds()` (Task 7), `NotebookRepository::savePhotosFromForm()` (Task 10).
- Produces: `FormLoadedIdsTest::renders(string $template, array $vars): string`, which Tasks 14, 17, 20, 24, 28 and 32 extend with one test each.

- [ ] **Step 1: Write the failing template test**

```php
<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Support/PhotoFixtures.php';

/** Each edit form must post back the photo IDs it showed, outside any JS-managed photo list. */
class FormLoadedIdsTest extends TestCase
{
    use PhotoFixtures;

    protected function setUp(): void
    {
        $this->fixturesUp();
    }

    protected function tearDown(): void
    {
        $this->cleanupFixtures();
    }

    private function renders(string $template, array $vars): string
    {
        $page = new \Template(getTestConfig());
        $page->setTemplate($template);
        foreach ($vars as $name => $value) {
            $page->set($name, $value);
        }
        return $page->grabTheGoods();
    }

    private function assertPostsLoadedIds(string $html, array $photoIds): void
    {
        preg_match_all('/<input type="hidden" name="loaded_photo_ids\[\]" value="(\d+)">/', $html, $m);
        $this->assertSame(array_map('strval', $photoIds), $m[1]);
        $form = strpos($html, '<form');
        $this->assertNotFalse($form);
        $this->assertGreaterThan($form, strpos($html, 'loaded_photo_ids'));
    }

    public function testNotebookForm(): void
    {
        $id = $this->makeRow('notebook');
        [$a, $b] = [$this->makePhoto('a'), $this->makePhoto('b')];
        $this->link('notebooks_2_photos', 'notebook_id', $id, $b, 1);
        $this->link('notebooks_2_photos', 'notebook_id', $id, $a, 2);

        $html = $this->renders('admin/notebooks/notebook.tpl.php', [
            'errors' => [],
            'notebook' => (new \Database\NotebookRepository($this->db()))->findById($id),
        ]);

        $this->assertPostsLoadedIds($html, [$b, $a]);
    }
}
```

- [ ] **Step 2: Run it**

Expected: FAIL, `[]` does not match `[b, a]`.

- [ ] **Step 3: Add the hidden fields to the template**

In `templates/admin/notebooks/notebook.tpl.php`, directly after the line with the opening `<form …>` tag, insert:
```php
<?php foreach ($notebook->photos ?? [] as $photo): ?>
        <input type="hidden" name="loaded_photo_ids[]" value="<?= (int) $photo->photo_id ?>">
<?php endforeach; ?>
```

- [ ] **Step 4: Run it**

Expected: PASS.

- [ ] **Step 5: Commit the template**

```bash
git add templates/admin/notebooks/notebook.tpl.php tests/Integration/RepositoryState/FormLoadedIdsTest.php
git commit -m "Post back the photos the Notebook form loaded" \
  -m "Knowing what the form showed lets a save remove only the photos Rob deleted on screen. The IDs sit outside the editable list, so adding or removing URL rows cannot touch them." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

- [ ] **Step 6: Switch the handler**

In `wwwroot/admin/notebooks/notebook.php`, replace
```php
            $repo->setNotebookId(notebook_id: $notebook_id);
            $repo->savePhotosFromUrls(urls: $image_urls);
```
with
```php
            $repo->savePhotosFromForm($notebook_id, \Utilities::postedIds('loaded_photo_ids'), $image_urls);
```
and replace
```php
            $repo->setNotebookId(notebook_id: $mla_database->insertId());
            $repo->savePhotosFromUrls(urls: $image_urls);
```
with
```php
            $repo->savePhotosFromForm($mla_database->insertId(), [], $image_urls);
```

- [ ] **Step 7: Lint and check that no old calls remain**

Run: `php -l wwwroot/admin/notebooks/notebook.php && grep -rn 'setNotebookId\|NotebookRepository.*savePhotosFromUrls' wwwroot templates classes`
Expected: `No syntax errors detected`; no grep output.

- [ ] **Step 8: Commit the handler**

```bash
git add wwwroot/admin/notebooks/notebook.php
git commit -m "Save Notebook photos by explicit ID" \
  -m "The handler passes the notebook's ID and the photos the form loaded, so a Save no longer wipes photos attached elsewhere while the form was open." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

### Task 12: Gate for bubble 2

- [ ] Run the **Bubble Gate** with:
  - branch `stateless-notebooks`
  - DONE `DONE stateless Notebooks`
  - expected red Step 0 tests: `Step0ApiPhotoSurvivesFormTest`, `Step0ListQueryCountTest` (workers case)
  - list `https://db.marbletrack3.com/admin/notebooks/index.php`
  - edit `https://db.marbletrack3.com/admin/notebooks/notebook.php?id=<id>`
  - API path: none
  - G3 "before" = `$SCRATCH/baseline.json`

---

# Bubble 3 — `stateless-pages`

### Task 13: PageRepository holds no current row

Start the bubble:
```bash
git switch -c stateless-pages
sed -i 's/^const SENTIMENTAL_VERSION = .*/const SENTIMENTAL_VERSION = "Stateless Pages";/' prepend.php
git commit -am "BEGIN stateless Pages" -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

**Files:**
- Modify: `classes/Database/PageRepository.php`

**Interfaces:**
- Produces: `PageRepository::savePhotosFromForm(int $page_id, int[] $loadedPhotoIds, string[] $urls): void`. `Page->primaryPhoto` is still set. Removed: `setPageId`, `getId`, `getDb`, `getPhotoLinkingTable`, `getPrimaryKeyColumn`, and the `HasPhotos` methods.

- [ ] **Step 1: Run the characterisation tests as the safety net**

Run: `PhotoLoadingCharacterisationTest.php`
Expected: PASS before the change. That includes `testPagePrimaryPhotoIsLastFlaggedElseFirst`, which this task must keep green.

- [ ] **Step 2: Edit `classes/Database/PageRepository.php`**

1. Delete `use Domain\HasPhotos;` and `use HasPhotos;`, the `$photoLinkingTable`, `$primaryKeyColumn` and `$page_id` fields, and the methods `getId`, `setPageId`, `getDb`, `getPhotoLinkingTable`, `getPrimaryKeyColumn`.
2. Add the field `private PhotoLinks $photoLinks;`, and at the end of the constructor add:
   ```php
        $this->photoLinks = new PhotoLinks($db, 'pages', 'pages_2_photos', 'page_id');
   ```
3. In `findAll()`, replace the loop and the `return` that follows it (the loop that sets `$this->page_id` and calls `hydrate`) with:
   ```php
        return $this->hydrateAll($results->toArray());
   ```
4. In `findById()`, delete `$this->page_id = $page_id;` and replace `return $this->hydrate($results->data);` (with its `setRow(0)` line) with:
   ```php
        return $this->hydrateAll($results->toArray())[0];
   ```
5. Replace `hydrate()` with:
   ```php
    /**
     * @param int[]    $loadedPhotoIds photo IDs the edit form showed when it loaded
     * @param string[] $urls           the form's photo URLs, in screen order
     */
    public function savePhotosFromForm(int $page_id, array $loadedPhotoIds, array $urls): void
    {
        $this->photoLinks->saveFormUrls($page_id, $loadedPhotoIds, $urls);
    }

    /** @return Page[] */
    private function hydrateAll(array $rows): array
    {
        $photos = $this->photoLinks->forIds(array_column($rows, 'page_id'));
        $pages = [];
        foreach ($rows as $row) {
            $page = $this->hydrate($row);
            $page->photos = $photos[$page->page_id] ?? [];
            $page->primaryPhoto = PhotoLinks::primaryOf($page->photos);
            $pages[] = $page;
        }
        return $pages;
    }

    private function hydrate(array $row): Page
    {
        return new Page(
            page_id: (int) $row['page_id'],
            notebook_id: (int) $row['notebook_id'],
            number: $row['number'] ?? '',
            created_at: $row['created_at'] ?? ''
        );
    }
   ```
6. Run `grep -n 'hydrate(\|page_id =\|loadPhotos\|getPhotos' classes/Database/PageRepository.php`. Expected: `hydrate(` appears only inside `hydrateAll`; nothing else matches.

- [ ] **Step 3: Run the tests**

Run: the characterisation tests, then the suite.
Expected: PASS.

- [ ] **Step 4: Commit**

```bash
git add classes/Database/PageRepository.php
git commit -m "Stop PageRepository remembering a current row" \
  -m "Same change as Notebooks: photos load for the whole list at once and a save names its page. The page list's primary photo is still the last flagged one, else the first." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

### Task 14: Page form posts the photos it loaded

**Files:**
- Modify: `templates/admin/notebooks/pages/page.tpl.php`
- Modify: `wwwroot/admin/notebooks/pages/page.php:42-43,46-47`
- Modify: `tests/Integration/RepositoryState/FormLoadedIdsTest.php`

- [ ] **Step 1: Add the failing test**

Before writing it, run `grep -n '\$page->set\|\$page = new' wwwroot/admin/notebooks/pages/page.php` and pass **every** variable the handler sets. The list below assumes `errors`, `page` and `columns`; correct it from the grep.

```php
    public function testPageForm(): void
    {
        $id = $this->makeRow('page');
        [$a, $b] = [$this->makePhoto('a'), $this->makePhoto('b')];
        $this->link('pages_2_photos', 'page_id', $id, $a, 1);
        $this->link('pages_2_photos', 'page_id', $id, $b, 2);

        $html = $this->renders('admin/notebooks/pages/page.tpl.php', [
            'errors' => [],
            'page' => (new \Database\PageRepository($this->db()))->findById($id),
            'columns' => [],
        ]);

        $this->assertPostsLoadedIds($html, [$a, $b]);
    }
```

- [ ] **Step 2: Run it**

Expected: FAIL (`[]`).

- [ ] **Step 3: Template**

The template's JavaScript (lines ~232–296) reorders and removes the photo rows. So the hidden fields go directly after the opening `<form …>` tag of the form that contains the existing photos' `image_urls[]` inputs (line ~71), never inside a photo row:
```php
<?php foreach ($page->photos ?? [] as $photo): ?>
        <input type="hidden" name="loaded_photo_ids[]" value="<?= (int) $photo->photo_id ?>">
<?php endforeach; ?>
```
If the variable in this template is not `$page`, use the one its `->photos` loop uses (`grep -n -- '->photos' templates/admin/notebooks/pages/page.tpl.php`).

- [ ] **Step 4: Run it, then commit the template**

Expected: PASS.
```bash
git add templates/admin/notebooks/pages/page.tpl.php tests/Integration/RepositoryState/FormLoadedIdsTest.php
git commit -m "Post back the photos the Page form loaded" \
  -m "The page form's script reorders and removes photo rows, so the loaded IDs sit outside those rows where the script cannot reach them." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

- [ ] **Step 5: Handler**

In `wwwroot/admin/notebooks/pages/page.php` replace
```php
            $repo->setPageId($page_id);
            $repo->savePhotosFromUrls(urls: $image_urls);
```
with
```php
            $repo->savePhotosFromForm($page_id, \Utilities::postedIds('loaded_photo_ids'), $image_urls);
```
and
```php
            $repo->setPageId($new_page_id);
            $repo->savePhotosFromUrls(urls: $image_urls);
```
with
```php
            $repo->savePhotosFromForm($new_page_id, [], $image_urls);
```

- [ ] **Step 6: Lint, grep, commit**

Run: `php -l wwwroot/admin/notebooks/pages/page.php && grep -rn 'setPageId' wwwroot templates classes`
Expected: no syntax errors; no grep output.
```bash
git add wwwroot/admin/notebooks/pages/page.php
git commit -m "Save Page photos by explicit ID" \
  -m "The handler names the page and passes the photos its form loaded, so the reordering script's result wins without wiping photos added elsewhere." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

### Task 15: Gate for bubble 3

- [ ] Run the **Bubble Gate** with:
  - branch `stateless-pages`
  - DONE `DONE stateless Pages`
  - expected red: `Step0ApiPhotoSurvivesFormTest`, `Step0ListQueryCountTest` (workers)
  - list `https://db.marbletrack3.com/admin/notebooks/pages/index.php`
  - edit `https://db.marbletrack3.com/admin/notebooks/pages/page.php?id=<id>`
  - API: none

---

# Bubble 4 — `stateless-episodes`

### Task 16: EpisodeRepository holds no current row

Start the bubble:
```bash
git switch -c stateless-episodes
sed -i 's/^const SENTIMENTAL_VERSION = .*/const SENTIMENTAL_VERSION = "Stateless Episodes";/' prepend.php
git commit -am "BEGIN stateless Episodes" -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

**Files:**
- Modify: `classes/Database/EpisodeRepository.php`

**Interfaces:**
- Produces: `EpisodeRepository::savePhotosFromForm(int $episode_id, int[] $loadedPhotoIds, string[] $urls): void`. Removed: `setEpisodeId`, `getId`, `getDb`, `getPhotoLinkingTable`, `getPrimaryKeyColumn`, and the `HasPhotos` methods.

- [ ] **Step 1: Edit `classes/Database/EpisodeRepository.php`**

1. Delete `use Domain\HasPhotos;`, `use HasPhotos;`, the fields `$photoLinkingTable`, `$primaryKeyColumn` and `$episode_id`, and the methods `getId`, `setEpisodeId`, `getDb`, `getPhotoLinkingTable`, `getPrimaryKeyColumn`.
2. Add `private PhotoLinks $photoLinks;`, and at the end of the constructor:
   ```php
        $this->photoLinks = new PhotoLinks($db, 'episodes', 'episodes_2_photos', 'episode_id');
   ```
3. `findAll()`: replace the loop that sets `$this->episode_id` and hydrates (and the `return` after it) with `return $this->hydrateAll($results->toArray());`.
4. `findById()` and `findByLivestreamId()`: delete the `$this->episode_id = …;` line, and replace `return $this->hydrate(row: $results->data);` (and its `setRow(0)`) with `return $this->hydrateAll($results->toArray())[0];`.
5. Replace `hydrate()` with:
   ```php
    /**
     * @param int[]    $loadedPhotoIds photo IDs the edit form showed when it loaded
     * @param string[] $urls           the form's photo URLs, in screen order
     */
    public function savePhotosFromForm(int $episode_id, array $loadedPhotoIds, array $urls): void
    {
        $this->photoLinks->saveFormUrls($episode_id, $loadedPhotoIds, $urls);
    }

    /** @return Episode[] */
    private function hydrateAll(array $rows): array
    {
        $photos = $this->photoLinks->forIds(array_column($rows, 'episode_id'));
        $episodes = [];
        foreach ($rows as $row) {
            $episode = $this->hydrate($row);
            $episode->photos = $photos[$episode->episode_id] ?? [];
            $episodes[] = $episode;
        }
        return $episodes;
    }

    private function hydrate(array $row): Episode
    {
        return new Episode(
            episode_id: (int) $row['episode_id'],
            title: $row['title'] ?? '',
            episode_english_description: $row['episode_english_description'] ?? '',
            episode_frames: $row['episode_frames'] ?? '',
            livestream_id: isset($row['livestream_id']) ? (int) $row['livestream_id'] : null,
            created_at: $row['created_at']
        );
    }
   ```
6. `grep -n 'hydrate(\|episode_id =\|loadPhotos' classes/Database/EpisodeRepository.php`. Expected: `hydrate(` appears only inside `hydrateAll`.

- [ ] **Step 2: Run the characterisation tests and the suite.** Expected: PASS.

- [ ] **Step 3: Commit**

```bash
git add classes/Database/EpisodeRepository.php
git commit -m "Stop EpisodeRepository remembering a current row" \
  -m "Photos load for the whole list at once and saves name their episode. findByLivestreamId() set the hidden ID too, so a livestream lookup before a save could redirect it." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

### Task 17: Episode form posts the photos it loaded

**Files:**
- Modify: `templates/admin/episodes/episode.tpl.php`
- Modify: `wwwroot/admin/episodes/episode.php:57-58,74-75`
- Modify: `tests/Integration/RepositoryState/FormLoadedIdsTest.php`

- [ ] **Step 1: Add the failing test** (get the variable list from `grep -n '\$page->set' wwwroot/admin/episodes/episode.php`)

```php
    public function testEpisodeForm(): void
    {
        $id = $this->makeRow('episode');
        [$a, $b] = [$this->makePhoto('a'), $this->makePhoto('b')];
        $this->link('episodes_2_photos', 'episode_id', $id, $a, 1);
        $this->link('episodes_2_photos', 'episode_id', $id, $b, 2);

        $html = $this->renders('admin/episodes/episode.tpl.php', [
            'errors' => [],
            'episode' => (new \Database\EpisodeRepository($this->db()))->findById($id),
        ]);

        $this->assertPostsLoadedIds($html, [$a, $b]);
    }
```

- [ ] **Step 2: Run it.** Expected: FAIL.

- [ ] **Step 3: Template.** Directly after the opening `<form …>` tag:
```php
<?php foreach ($episode->photos ?? [] as $photo): ?>
        <input type="hidden" name="loaded_photo_ids[]" value="<?= (int) $photo->photo_id ?>">
<?php endforeach; ?>
```

- [ ] **Step 4: Run it, then commit the template**

```bash
git add templates/admin/episodes/episode.tpl.php tests/Integration/RepositoryState/FormLoadedIdsTest.php
git commit -m "Post back the photos the Episode form loaded" \
  -m "Lets a save remove only the photos deleted on screen." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

- [ ] **Step 5: Handler.** In `wwwroot/admin/episodes/episode.php` replace
```php
            $epRepo->setEpisodeId($episode_id);
            $epRepo->savePhotosFromUrls(urls: $image_urls);
```
with
```php
            $epRepo->savePhotosFromForm($episode_id, \Utilities::postedIds('loaded_photo_ids'), $image_urls);
```
and
```php
            $epRepo->setEpisodeId($newId);
            $epRepo->savePhotosFromUrls(urls: $image_urls);
```
with
```php
            $epRepo->savePhotosFromForm($newId, [], $image_urls);
```

- [ ] **Step 6: Lint, grep, commit**

Run: `php -l wwwroot/admin/episodes/episode.php && grep -rn 'setEpisodeId' wwwroot templates classes`. Expected: clean, no output.
```bash
git add wwwroot/admin/episodes/episode.php
git commit -m "Save Episode photos by explicit ID" \
  -m "The handler names the episode and passes the photos its form loaded." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

### Task 18: Gate for bubble 4

- [ ] **Bubble Gate** with:
  - branch `stateless-episodes`
  - DONE `DONE stateless Episodes`
  - expected red: `Step0ApiPhotoSurvivesFormTest`, `Step0ListQueryCountTest` (workers)
  - list `https://db.marbletrack3.com/admin/episodes/index.php`
  - edit `https://db.marbletrack3.com/admin/episodes/episode.php?episode_id=<id>`
  - API: none

---

# Bubble 5 — `stateless-moments`

### Task 19: MomentRepository holds no current row

Start the bubble:
```bash
git switch -c stateless-moments
sed -i 's/^const SENTIMENTAL_VERSION = .*/const SENTIMENTAL_VERSION = "Stateless Moments";/' prepend.php
git commit -am "BEGIN stateless Moments" -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

**Files:**
- Modify: `classes/Database/MomentRepository.php`
- Test: `tests/Integration/RepositoryState/MomentFindersTest.php`

**Interfaces:**
- Produces:
  - `MomentRepository::savePhotosFromForm(int $moment_id, int[] $loadedPhotoIds, string[] $urls): void`
  - `MomentRepository::addPhotosFromUrls(int $moment_id, string[] $urls): void`
  - Removed: `setMomentId`, `getId`, `getPhotoLinkingTable`, `getPrimaryKeyColumn`, and the `HasPhotos` methods (`getDb()` stays only if something outside the file calls it; check with `grep -rn 'getDb()' classes wwwroot | grep -i moment`).

- [ ] **Step 1: Write a test covering every Moment finder that hydrates**

```php
<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Support/PhotoFixtures.php';

class MomentFindersTest extends TestCase
{
    use PhotoFixtures;

    protected function setUp(): void
    {
        $this->fixturesUp();
    }

    protected function tearDown(): void
    {
        $this->cleanupFixtures();
    }

    public function testEveryFinderAttachesEachMomentsOwnPhotos(): void
    {
        $repo = new \Database\MomentRepository($this->db());
        $withPhoto = $this->makeMoment(1000001, 1000002, $this->prefix . 'finder with');
        $without = $this->makeMoment(1000003, 1000004, $this->prefix . 'finder without');
        $photo = $this->makePhoto('finder');
        $this->link('moments_2_photos', 'moment_id', $withPhoto, $photo, 1);

        $lists = [
            'findAll' => $repo->findAll(),
            'findByFilter' => $repo->findByFilter($this->prefix . 'finder'),
            'findFiltered' => $repo->findFiltered($this->prefix . 'finder')['moments'],
        ];
        foreach ($lists as $finder => $moments) {
            $byId = [];
            foreach ($moments as $m) {
                $byId[$m->moment_id] = $m;
            }
            $this->assertSame([$photo], $this->photoIds($byId[$withPhoto]->photos), $finder);
            $this->assertSame([], $this->photoIds($byId[$without]->photos), $finder);
        }
        $this->assertSame([$photo], $this->photoIds($repo->findById($withPhoto)->photos));
        $filtered = $repo->findFiltered($this->prefix . 'finder');
        foreach ($filtered['moments'] as $m) {
            $this->assertIsInt($m->perspective_count);
        }
    }
}
```

- [ ] **Step 2: Run it on the old code.** Expected: PASS (it pins today's behaviour). If `findByFilter`'s signature rejects these arguments, read `MomentRepository.php:118` and adjust the call, not the class.

- [ ] **Step 3: Edit `classes/Database/MomentRepository.php`**

1. Delete `use Domain\HasPhotos;`, `use HasPhotos;`, the fields `$photoLinkingTable`, `$primaryKeyColumn` and `$moment_id`, and the methods `getId`, `setMomentId`, `getPhotoLinkingTable`, `getPrimaryKeyColumn`. Keep `getDb()` only if the Step-header grep found an outside caller.
2. Add `private PhotoLinks $photoLinks;`, and at the end of the constructor:
   ```php
        $this->photoLinks = new PhotoLinks($db, 'moments', 'moments_2_photos', 'moment_id');
   ```
3. Multi-row finders `findWithinTakeId`, `findAll`, `findByFilter`, `findByPartId`, `findSimilarMoments`: replace each loop that sets `$this->moment_id` and hydrates (and the `return` of its array) with `return $this->hydrateAll($results->toArray());`.
4. `findFiltered`: replace its loop with
   ```php
        $rows = $results->toArray();
        $moments = $this->hydrateAll($rows);
        foreach ($moments as $i => $moment) {
            $moment->perspective_count = (int) ($rows[$i]['perspective_count'] ?? 0);
        }
   ```
   keeping the total-count query and `return ['moments' => $moments, 'total' => $total];` below it.
5. `findById`: delete `$this->moment_id = $moment_id;`, and replace the `setRow(0)` + `return $this->hydrate($results->data);` with `return $this->hydrateAll($results->toArray())[0];`.
6. Replace `hydrate()` with:
   ```php
    /**
     * @param int[]    $loadedPhotoIds photo IDs the edit form showed when it loaded
     * @param string[] $urls           the form's photo URLs, in screen order
     */
    public function savePhotosFromForm(int $moment_id, array $loadedPhotoIds, array $urls): void
    {
        $this->photoLinks->saveFormUrls($moment_id, $loadedPhotoIds, $urls);
    }

    /** Append photos, keeping any already linked. Used by the API and the realtime editor. */
    public function addPhotosFromUrls(int $moment_id, array $urls): void
    {
        $this->photoLinks->addUrls($moment_id, $urls);
    }

    /** @return Moment[] */
    private function hydrateAll(array $rows): array
    {
        $photos = $this->photoLinks->forIds(array_column($rows, 'moment_id'));
        $moments = [];
        foreach ($rows as $row) {
            $moment = $this->hydrate($row);
            $moment->photos = $photos[$moment->moment_id] ?? [];
            $moments[] = $moment;
        }
        return $moments;
    }

    private function hydrate(array $row): Moment
    {
        return new Moment(
            moment_id: (int) $row['moment_id'],
            frame_start: isset($row['frame_start']) ? (int)$row['frame_start'] : null,
            frame_end: isset($row['frame_end']) ? (int)$row['frame_end'] : null,
            take_id: isset($row['take_id']) ? (int)$row['take_id'] : null,
            notes: $row['notes'] ?? null,
            moment_date: $row['moment_date'] ?? null
        );
    }
   ```
7. `grep -n 'hydrate(\|moment_id = (int)\|this->moment_id\|loadPhotos\|getPhotos()' classes/Database/MomentRepository.php`. Expected: `hydrate(` appears only inside `hydrateAll`, and there is no `$this->moment_id`.

- [ ] **Step 4: Run `MomentFindersTest`, the characterisation tests and the suite.** Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add classes/Database/MomentRepository.php tests/Integration/RepositoryState/MomentFindersTest.php
git commit -m "Stop MomentRepository remembering a current row" \
  -m "Seven finders each set the hidden ID per row and loaded photos per row. They now hydrate whole lists with two photo queries, and saves name their moment." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

### Task 20: Moment form posts the photos it loaded

**Files:**
- Modify: `templates/admin/moments/moment.tpl.php`
- Modify: `wwwroot/admin/moments/moment.php:53-54,58-59`
- Modify: `tests/Integration/RepositoryState/FormLoadedIdsTest.php`

- [ ] **Step 1: Add the failing test** (variables from `grep -n '\$page->set' wwwroot/admin/moments/moment.php`; the list below assumes `errors`, `moment`, `takes` and `default_take_id`)

```php
    public function testMomentForm(): void
    {
        $id = $this->makeRow('moment');
        [$a, $b] = [$this->makePhoto('a'), $this->makePhoto('b')];
        $this->link('moments_2_photos', 'moment_id', $id, $a, 1);
        $this->link('moments_2_photos', 'moment_id', $id, $b, 2);

        $html = $this->renders('admin/moments/moment.tpl.php', [
            'errors' => [],
            'moment' => (new \Database\MomentRepository($this->db()))->findById($id),
            'takes' => [],
            'default_take_id' => '',
        ]);

        $this->assertPostsLoadedIds($html, [$a, $b]);
    }
```

- [ ] **Step 2: Run it.** Expected: FAIL.

- [ ] **Step 3: Template.** Directly after the opening `<form …>` tag:
```php
<?php foreach ($moment->photos ?? [] as $photo): ?>
        <input type="hidden" name="loaded_photo_ids[]" value="<?= (int) $photo->photo_id ?>">
<?php endforeach; ?>
```

- [ ] **Step 4: Run it, then commit the template**

```bash
git add templates/admin/moments/moment.tpl.php tests/Integration/RepositoryState/FormLoadedIdsTest.php
git commit -m "Post back the photos the Moment form loaded" \
  -m "Moments are the kind most often given photos through the API while open in the admin, so this is where a Save wiping them would bite first." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

- [ ] **Step 5: Handler.** In `wwwroot/admin/moments/moment.php` replace
```php
            $moment_repo->setMomentId(moment_id: $moment_id);
            $moment_repo->savePhotosFromUrls(urls: $image_urls);
```
with
```php
            $moment_repo->savePhotosFromForm($moment_id, \Utilities::postedIds('loaded_photo_ids'), $image_urls);
```
and
```php
            $moment_repo->setMomentId(moment_id: $new_moment_id);
            $moment_repo->savePhotosFromUrls(urls: $image_urls);
```
with
```php
            $moment_repo->savePhotosFromForm($new_moment_id, [], $image_urls);
```

- [ ] **Step 6: Lint and commit**

Run: `php -l wwwroot/admin/moments/moment.php`
```bash
git add wwwroot/admin/moments/moment.php
git commit -m "Save Moment photos by explicit ID" \
  -m "The handler names the moment and passes the photos its form loaded." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

### Task 21: Moment API and realtime editor add by explicit ID

**Files:**
- Modify: `wwwroot/api/v1/moments/index.php:95-96,147-148`
- Modify: `wwwroot/admin/ajax/save_moment_from_realtime.php:58-59`

- [ ] **Step 1: API.** In `wwwroot/api/v1/moments/index.php`, replace
```php
        $repo->setMomentId($moment->moment_id);
        $repo->addPhotosFromUrls($input['photo_urls']);
```
with
```php
        $repo->addPhotosFromUrls($moment->moment_id, $input['photo_urls']);
```
and
```php
        $repo->setMomentId($moment_id);
        $repo->addPhotosFromUrls($input['photo_urls']);
```
with
```php
        $repo->addPhotosFromUrls($moment_id, $input['photo_urls']);
```

- [ ] **Step 2: Lint and commit the API**

Run: `php -l wwwroot/api/v1/moments/index.php`
```bash
git add wwwroot/api/v1/moments/index.php
git commit -m "Add Moment photos through the API by explicit ID" \
  -m "The API only ever appends, and now says which moment it is appending to." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

- [ ] **Step 3: Realtime editor.** In `wwwroot/admin/ajax/save_moment_from_realtime.php`, replace
```php
        $momentRepo->setMomentId($moment_id); // Set ID for HasPhotos trait
        $momentRepo->savePhotosFromUrls([$image_url]);
```
with
```php
        $momentRepo->addPhotosFromUrls($moment_id, [$image_url]);
```
It runs inside the handler's `$mla_database->beginTransaction()`, and `PhotoLinks` joins that transaction.

- [ ] **Step 4: Lint, grep and commit**

Run: `php -l wwwroot/admin/ajax/save_moment_from_realtime.php && grep -rn 'setMomentId' wwwroot templates classes`. Expected: clean, no output.
```bash
git add wwwroot/admin/ajax/save_moment_from_realtime.php
git commit -m "Add the realtime editor's photo inside its transaction" \
  -m "The moment is brand new, so appending is the same as replacing, and the link write now joins the handler's transaction instead of running outside it." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

### Task 22: Gate for bubble 5

- [ ] **Bubble Gate** with:
  - branch `stateless-moments`
  - DONE `DONE stateless Moments`
  - expected red: `Step0ApiPhotoSurvivesFormTest`, `Step0ListQueryCountTest` (workers)
  - list `https://db.marbletrack3.com/admin/moments/index.php`
  - edit `https://db.marbletrack3.com/admin/moments/moment.php?id=<id>`
  - API path `moments`

---

# Bubble 6 — `stateless-workers`

### Task 23: WorkersRepository holds no current row, and loads moments in bulk

Start the bubble:
```bash
git switch -c stateless-workers
sed -i 's/^const SENTIMENTAL_VERSION = .*/const SENTIMENTAL_VERSION = "Stateless Workers";/' prepend.php
git commit -am "BEGIN stateless Workers" -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

**Files:**
- Modify: `classes/Database/WorkersRepository.php`

**Interfaces:**
- Consumes: `PhotoLinks`, `PerspectiveMoments` (Task 6).
- Produces:
  - `WorkersRepository::savePhotosFromForm(int $worker_id, int[] $loadedPhotoIds, string[] $urls): void`
  - `WorkersRepository::addPhotosFromUrls(int $worker_id, string[] $urls): void`
  - `WorkersRepository::addPhotos(int $worker_id, Photo[] $photos): void`
  - `getDb()` stays (used by `HasShortcodes`).
  - Removed: `use HasPhotos`, `use HasMoments`, `getMomentLinkingTable`, `getPhotoLinkingTable`, `getPrimaryKeyColumn`, `getId`, `setWorkerId`.

- [ ] **Step 1: Edit `classes/Database/WorkersRepository.php`**

1. Delete `use Domain\HasPhotos;`, `use Domain\HasMoments;` (keep `HasShortcodes`), the `use HasPhotos;` and `use HasMoments;` lines, the fields `$photoLinkingTable`, `$primaryKeyColumn` and `$worker_id`, and the methods `getPhotoLinkingTable`, `getMomentLinkingTable`, `getPrimaryKeyColumn`, `getId`, `setWorkerId`.
2. Add these fields:
   ```php
    private PhotoLinks $photoLinks;
    private PerspectiveMoments $perspectiveMoments;
   ```
   and at the end of the constructor:
   ```php
        $this->photoLinks = new PhotoLinks($db, 'workers', 'workers_2_photos', 'worker_id');
        $this->perspectiveMoments = new PerspectiveMoments($db);
   ```
3. `findAll()`: replace the loop and its `return` with `return $this->hydrateAll($results->toArray());`.
4. `findById()` and `findByAlias()`: delete the `$this->worker_id = …;` line, and replace `return $this->hydrate($results->data);` (with its `setRow(0)`) with `return $this->hydrateAll($results->toArray())[0];`.
5. Replace `hydrate()` with:
   ```php
    /**
     * @param int[]    $loadedPhotoIds photo IDs the edit form showed when it loaded
     * @param string[] $urls           the form's photo URLs, in screen order
     */
    public function savePhotosFromForm(int $worker_id, array $loadedPhotoIds, array $urls): void
    {
        $this->photoLinks->saveFormUrls($worker_id, $loadedPhotoIds, $urls);
    }

    /** Append photos, keeping any already linked. Used by the API. */
    public function addPhotosFromUrls(int $worker_id, array $urls): void
    {
        $this->photoLinks->addUrls($worker_id, $urls);
    }

    /** @param \Media\Photo[] $photos */
    public function addPhotos(int $worker_id, array $photos): void
    {
        $this->photoLinks->add($worker_id, $photos);
    }

    /** @return Worker[] */
    private function hydrateAll(array $rows): array
    {
        $ids = array_map(fn (array $row) => (int) $row['worker_id'], $rows);
        $photos = $this->photoLinks->forIds($ids);
        $moments = $this->perspectiveMoments->forPerspectives('worker', $ids);
        $workers = [];
        foreach ($rows as $row) {
            $worker = $this->hydrate($row);
            $worker->photos = $photos[$worker->worker_id] ?? [];
            $worker->moments = $moments[$worker->worker_id] ?? [];
            $workers[] = $worker;
        }
        return $workers;
    }

    private function hydrate(array $row): Worker
    {
        return new Worker(
            worker_id: (int) $row['worker_id'],
            worker_alias: $row['worker_alias'],
            busy_sort: (int) $row['busy_sort'],
            name: $row['worker_name'] ?? '',
            description: $row['worker_description'] ?? ''
        );
    }
   ```
6. `grep -n 'hydrate(\|worker_id = (int)\|this->worker_id\|loadPhotos\|loadMoments' classes/Database/WorkersRepository.php`. Expected: `hydrate(` appears only inside `hydrateAll`, and there is no `$this->worker_id`.

- [ ] **Step 2: Run the tests**

Run: `--group step0`, the two characterisation tests, and the suite.
Expected: the workers case of `Step0ListQueryCountTest` now PASSES. Worker rows in both characterisation tests PASS.

- [ ] **Step 3: Commit**

```bash
git add classes/Database/WorkersRepository.php
git commit -m "Stop WorkersRepository remembering a current row" \
  -m "The worker list cost four queries per worker: two for photos and two for moments. It now costs four in total, and photo saves name their worker." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

### Task 24: Worker form posts the photos it loaded

**Files:**
- Modify: `templates/admin/workers/worker.tpl.php`
- Modify: `wwwroot/admin/workers/worker.php:51-52,60-61`
- Modify: `tests/Integration/RepositoryState/FormLoadedIdsTest.php`

- [ ] **Step 1: Add the failing test** (variables from `grep -n '\$page->set' wwwroot/admin/workers/worker.php`; the list below assumes `errors`, `worker` and `all_moments`)

```php
    public function testWorkerForm(): void
    {
        $id = $this->makeRow('worker');
        [$a, $b] = [$this->makePhoto('a'), $this->makePhoto('b')];
        $this->link('workers_2_photos', 'worker_id', $id, $a, 1);
        $this->link('workers_2_photos', 'worker_id', $id, $b, 2);

        $html = $this->renders('admin/workers/worker.tpl.php', [
            'errors' => [],
            'worker' => (new \Database\WorkersRepository($this->db(), 'en'))->findById($id),
            'all_moments' => [],
        ]);

        $this->assertPostsLoadedIds($html, [$a, $b]);
    }
```

- [ ] **Step 2: Run it.** Expected: FAIL.

- [ ] **Step 3: Template.** Directly after the opening `<form …>` tag:
```php
<?php foreach ($worker->photos ?? [] as $photo): ?>
        <input type="hidden" name="loaded_photo_ids[]" value="<?= (int) $photo->photo_id ?>">
<?php endforeach; ?>
```

- [ ] **Step 4: Run it, then commit the template**

```bash
git add templates/admin/workers/worker.tpl.php tests/Integration/RepositoryState/FormLoadedIdsTest.php
git commit -m "Post back the photos the Worker form loaded" \
  -m "Lets a save remove only the photos deleted on screen." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

- [ ] **Step 5: Handler.** In `wwwroot/admin/workers/worker.php` replace the update branch's
```php
            $repo->setWorkerId($worker_id);
            $repo->savePhotosFromUrls(urls: $image_urls);
```
with
```php
            $repo->savePhotosFromForm($worker_id, \Utilities::postedIds('loaded_photo_ids'), $image_urls);
```
and the insert branch's identical pair (after `$worker_id = $repo->insert(…)`) with
```php
            $repo->savePhotosFromForm($worker_id, [], $image_urls);
```
Read lines 45–65 first: the two pairs are textually identical, so edit them one at a time by line.

- [ ] **Step 6: Lint and commit**

Run: `php -l wwwroot/admin/workers/worker.php`
```bash
git add wwwroot/admin/workers/worker.php
git commit -m "Save Worker photos by explicit ID" \
  -m "The handler names the worker and passes the photos its form loaded." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

### Task 25: Worker API and part-image uploader add by explicit ID

**Files:**
- Modify: `wwwroot/api/v1/workers/index.php:49-50`
- Modify: `wwwroot/admin/parts/images/save_photos.php:81-83` (Worker half only)

- [ ] **Step 1: API.** Replace
```php
        $repo->setWorkerId($worker->worker_id);
        $repo->addPhotosFromUrls($input['photo_urls']);
```
with
```php
        $repo->addPhotosFromUrls($worker->worker_id, $input['photo_urls']);
```
Lint, then commit:
```bash
git add wwwroot/api/v1/workers/index.php
git commit -m "Add Worker photos through the API by explicit ID" \
  -m "The API only ever appends, and now says which worker it is appending to." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

- [ ] **Step 2: Uploader, Worker half.** In `wwwroot/admin/parts/images/save_photos.php` replace
```php
            $workersRepo->setWorkerId($worker_id);
            $workersRepo->addPhotos([$photo]);
```
with
```php
            $workersRepo->addPhotos($worker_id, [$photo]);
```
Leave the `$partsRepo` lines for Task 29.

- [ ] **Step 3: Lint, grep and commit**

Run: `php -l wwwroot/admin/parts/images/save_photos.php && grep -rn 'setWorkerId' wwwroot templates classes`. Expected: clean, no output.
```bash
git add wwwroot/admin/parts/images/save_photos.php
git commit -m "Tag uploaded photos onto Workers by explicit ID" \
  -m "The uploader switched one repository between workers in a loop. Each add now names its worker and joins the uploader's transaction." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

### Task 26: Gate for bubble 6

- [ ] **Bubble Gate** with:
  - branch `stateless-workers`
  - DONE `DONE stateless Workers`
  - expected red: `Step0ApiPhotoSurvivesFormTest`
  - list `https://db.marbletrack3.com/admin/workers/index.php`
  - edit `https://db.marbletrack3.com/admin/workers/worker.php?id=<id>`
  - API path `workers`

---

# Bubble 7 — `stateless-parts`

### Task 27: PartsRepository holds no current row, and loads moments in bulk

Start the bubble:
```bash
git switch -c stateless-parts
sed -i 's/^const SENTIMENTAL_VERSION = .*/const SENTIMENTAL_VERSION = "Stateless Parts";/' prepend.php
git commit -am "BEGIN stateless Parts" -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

**Files:**
- Modify: `classes/Database/PartsRepository.php`
- Modify: `tests/Integration/RepositoryState/Step0ApiPhotoSurvivesFormTest.php`

**Interfaces:**
- Produces:
  - `PartsRepository::savePhotosFromForm(int $part_id, int[] $loadedPhotoIds, string[] $urls): void`
  - `PartsRepository::addPhotosFromUrls(int $part_id, string[] $urls): void`
  - `PartsRepository::addPhotos(int $part_id, Photo[] $photos): void`
  - `getDb()` stays.
  - Removed: `use HasPhotos`, `use HasMoments`, `getPhotoLinkingTable`, `getMomentLinkingTable`, `getPrimaryKeyColumn`, `getId`, `setPartId`.

- [ ] **Step 1: Move the Parts Step 0 test to the new API**

Replace the body after the `$loaded = …` line with:
```php
        $api = new \Database\PartsRepository($this->db(), 'en');
        $api->addPhotosFromUrls($part, [$this->url('three')]);   // the API attaches a photo meanwhile

        $form->savePhotosFromForm(                                 // Rob saves without touching photos
            $part,
            array_map(fn ($p) => $p->photo_id, $loaded->photos),
            array_map(fn ($p) => $p->getUrl(), $loaded->photos)
        );

        $this->assertContains($three, $this->linkedIds('parts_2_photos', 'part_id', $part));
```
Run `--group step0`. Expected: FAIL (`savePhotosFromForm` undefined).

- [ ] **Step 2: Edit `classes/Database/PartsRepository.php`**

1. Delete `use Domain\HasPhotos;`, `use Domain\HasMoments;` (keep `HasShortcodes`), the `use HasPhotos;` and `use HasMoments;` lines, the fields `$photoLinkingTable`, `$primaryKeyColumn` and `$part_id`, and the methods `getPhotoLinkingTable`, `getMomentLinkingTable`, `getPrimaryKeyColumn`, `getId`, `setPartId`.
2. Add these fields:
   ```php
    private PhotoLinks $photoLinks;
    private PerspectiveMoments $perspectiveMoments;
   ```
   and at the end of the constructor:
   ```php
        $this->photoLinks = new PhotoLinks($db, 'parts', 'parts_2_photos', 'part_id');
        $this->perspectiveMoments = new PerspectiveMoments($db);
   ```
3. Multi-row `findAll`, `findByFilter`, `findPossParts`: replace each loop (it calls `setPartId(…)` then `hydrate`) and its `return` with `return $this->hydrateAll($results->toArray());`.
4. Single-row `findById`, `findByAlias`, `findBySlug`: delete the `setPartId(…)` line and any `$part_id = (int) $results->data['part_id'];` that only fed it. Replace `return $this->hydrate($results->data);` (with its `setRow(0)`) with `return $this->hydrateAll($results->toArray())[0];`.
5. Replace `hydrate()` with:
   ```php
    /**
     * @param int[]    $loadedPhotoIds photo IDs the edit form showed when it loaded
     * @param string[] $urls           the form's photo URLs, in screen order
     */
    public function savePhotosFromForm(int $part_id, array $loadedPhotoIds, array $urls): void
    {
        $this->photoLinks->saveFormUrls($part_id, $loadedPhotoIds, $urls);
    }

    /** Append photos, keeping any already linked. Used by the API. */
    public function addPhotosFromUrls(int $part_id, array $urls): void
    {
        $this->photoLinks->addUrls($part_id, $urls);
    }

    /** @param \Media\Photo[] $photos */
    public function addPhotos(int $part_id, array $photos): void
    {
        $this->photoLinks->add($part_id, $photos);
    }

    /** @return Part[] */
    private function hydrateAll(array $rows): array
    {
        $ids = array_map(fn (array $row) => (int) $row['part_id'], $rows);
        $photos = $this->photoLinks->forIds($ids);
        $moments = $this->perspectiveMoments->forPerspectives('part', $ids);
        $parts = [];
        foreach ($rows as $row) {
            $part = $this->hydrate($row);
            $part->photos = $photos[$part->part_id] ?? [];
            $part->moments = $moments[$part->part_id] ?? [];
            $parts[] = $part;
        }
        return $parts;
    }

    private function hydrate(array $row): Part
    {
        $part = new Part(
            part_id: (int) $row['part_id'],
            part_alias: $row['part_alias'],
            name: $row['part_name'] ?? '',
            description: $row['part_description'] ?? '',
            is_rail: (bool) ($row['is_rail'] ?? false),
            is_support: (bool) ($row['is_support'] ?? false),
            is_track: (bool) ($row['is_track'] ?? false),
            no_track: (bool) ($row['no_track'] ?? false)
        );

        if($part->name == $part->description) {
            // quickly get the source of the data.  Not needed once all Parts on this site have detailed info
            $part->frontend_link = "https://www.marbletrack3.com/p/{$part->part_alias}";
        } else {
            // We have data on this site so stay on this site.
            $part->frontend_link = "/parts/" . \Utilities::slugify($part->name, 200);
        }

        return $part;
    }
   ```
6. `grep -n 'hydrate(\|setPartId\|this->part_id\|loadPhotos\|loadMoments' classes/Database/PartsRepository.php`. Expected: `hydrate(` appears only inside `hydrateAll`.

- [ ] **Step 3: Run the tests**

Run: `--group step0`, the characterisation tests, and the suite.
Expected: **all four Step 0 classes now PASS**; characterisation PASS.

- [ ] **Step 4: Commit**

```bash
git add classes/Database/PartsRepository.php tests/Integration/RepositoryState/Step0ApiPhotoSurvivesFormTest.php
git commit -m "Stop PartsRepository remembering a current row" \
  -m "Parts had the most finders setting the hidden ID. The part list now costs four queries in total instead of four per part, and a form save keeps photos the API attached while it was open." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

### Task 28: Part form posts the photos it loaded

**Files:**
- Modify: `templates/admin/parts/part.tpl.php`
- Modify: `wwwroot/admin/parts/part.php:54-55,60-61`
- Modify: `tests/Integration/RepositoryState/FormLoadedIdsTest.php`

- [ ] **Step 1: Add the failing test** (variables from `grep -n '\$page->set' wwwroot/admin/parts/part.php`; the list below assumes `errors`, `part`, `all_moments` and `tracks`)

```php
    public function testPartForm(): void
    {
        $id = $this->makeRow('part');
        [$a, $b] = [$this->makePhoto('a'), $this->makePhoto('b')];
        $this->link('parts_2_photos', 'part_id', $id, $a, 1);
        $this->link('parts_2_photos', 'part_id', $id, $b, 2);

        $html = $this->renders('admin/parts/part.tpl.php', [
            'errors' => [],
            'part' => (new \Database\PartsRepository($this->db(), 'en'))->findById($id),
            'all_moments' => [],
            'tracks' => [],
        ]);

        $this->assertPostsLoadedIds($html, [$a, $b]);
    }
```

- [ ] **Step 2: Run it.** Expected: FAIL.

- [ ] **Step 3: Template.** Directly after the opening `<form …>` tag:
```php
<?php foreach ($part->photos ?? [] as $photo): ?>
        <input type="hidden" name="loaded_photo_ids[]" value="<?= (int) $photo->photo_id ?>">
<?php endforeach; ?>
```

- [ ] **Step 4: Run it, then commit the template**

```bash
git add templates/admin/parts/part.tpl.php tests/Integration/RepositoryState/FormLoadedIdsTest.php
git commit -m "Post back the photos the Part form loaded" \
  -m "Lets a save remove only the photos deleted on screen." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

- [ ] **Step 5: Handler.** In `wwwroot/admin/parts/part.php` replace
```php
            $repo->setPartId(part_id: $part->part_id);
            $repo->savePhotosFromUrls(urls: $image_urls);
```
with
```php
            $repo->savePhotosFromForm($part->part_id, \Utilities::postedIds('loaded_photo_ids'), $image_urls);
```
and
```php
            $repo->setPartId($newId);
            $repo->savePhotosFromUrls($image_urls);
```
with
```php
            $repo->savePhotosFromForm($newId, [], $image_urls);
```

- [ ] **Step 6: Lint and commit**

```bash
git add wwwroot/admin/parts/part.php
git commit -m "Save Part photos by explicit ID" \
  -m "The handler names the part and passes the photos its form loaded." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

### Task 29: Part API and part-image uploader add by explicit ID

**Files:**
- Modify: `wwwroot/api/v1/parts/index.php:51-52`
- Modify: `wwwroot/admin/parts/images/save_photos.php:76-77` (Part half)

- [ ] **Step 1: API.** Replace
```php
        $repo->setPartId($part->part_id);
        $repo->addPhotosFromUrls($input['photo_urls']);
```
with
```php
        $repo->addPhotosFromUrls($part->part_id, $input['photo_urls']);
```
Lint, then commit:
```bash
git add wwwroot/api/v1/parts/index.php
git commit -m "Add Part photos through the API by explicit ID" \
  -m "The API only ever appends, and now says which part it is appending to." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

- [ ] **Step 2: Uploader, Part half.** Replace
```php
        $partsRepo->setPartId($part_id);
        $partsRepo->addPhotos([$photo]);
```
with
```php
        $partsRepo->addPhotos($part_id, [$photo]);
```

- [ ] **Step 3: Lint, grep and commit**

Run: `php -l wwwroot/admin/parts/images/save_photos.php && grep -rn 'setPartId' wwwroot templates classes`. Expected: clean, no output.
```bash
git add wwwroot/admin/parts/images/save_photos.php
git commit -m "Link uploaded photos to their Part by explicit ID" \
  -m "Each add now names its part and joins the uploader's transaction." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

### Task 30: Gate for bubble 7

- [ ] **Bubble Gate** with:
  - branch `stateless-parts`
  - DONE `DONE stateless Parts`
  - expected red: none
  - list `https://db.marbletrack3.com/admin/parts/index.php`
  - edit `https://db.marbletrack3.com/admin/parts/part.php?id=<id>`
  - API path `parts`

---

# Bubble 8 — `stateless-marbles`

### Task 31: MarblesRepository holds no current row, and loads moments in bulk

Start the bubble:
```bash
git switch -c stateless-marbles
sed -i 's/^const SENTIMENTAL_VERSION = .*/const SENTIMENTAL_VERSION = "Stateless Marbles";/' prepend.php
git commit -am "BEGIN stateless Marbles" -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

**Files:**
- Modify: `classes/Database/MarblesRepository.php`

**Interfaces:**
- Produces:
  - `MarblesRepository::savePhotosFromForm(int $marble_id, int[] $loadedPhotoIds, string[] $urls): void`
  - `MarblesRepository::addPhotosFromUrls(int $marble_id, string[] $urls): void`
  - `getDb()` stays.
  - Removed: `use HasPhotos`, `use HasMoments`, `getPrimaryKeyColumn`, `getId`, `getPhotoLinkingTable`, `setMarbleId`, `getMomentLinkingTable`.

- [ ] **Step 1: Edit `classes/Database/MarblesRepository.php`**

1. Delete `use Domain\HasPhotos;`, `use Domain\HasMoments;` (keep `HasShortcodes`), the trait `use` lines, the fields `$photoLinkingTable`, `$primaryKeyColumn` and `$marble_id`, and the methods `getPrimaryKeyColumn`, `getId`, `getPhotoLinkingTable`, `setMarbleId`, `getMomentLinkingTable`.
2. Add these fields:
   ```php
    private PhotoLinks $photoLinks;
    private PerspectiveMoments $perspectiveMoments;
   ```
   and at the end of the constructor:
   ```php
        $this->photoLinks = new PhotoLinks($db, 'marbles', 'marbles_2_photos', 'marble_id');
        $this->perspectiveMoments = new PerspectiveMoments($db);
   ```
3. `findAll`: replace the loop and its `return` with `return $this->hydrateAll($results->toArray());`.
4. `findById`, `findByAlias`: replace `return $this->hydrate($results->data);` (with its `setRow(0)`) with `return $this->hydrateAll($results->toArray())[0];`.
5. Replace `hydrate()` with:
   ```php
    /**
     * @param int[]    $loadedPhotoIds photo IDs the edit form showed when it loaded
     * @param string[] $urls           the form's photo URLs, in screen order
     */
    public function savePhotosFromForm(int $marble_id, array $loadedPhotoIds, array $urls): void
    {
        $this->photoLinks->saveFormUrls($marble_id, $loadedPhotoIds, $urls);
    }

    /** Append photos, keeping any already linked. Used by the API. */
    public function addPhotosFromUrls(int $marble_id, array $urls): void
    {
        $this->photoLinks->addUrls($marble_id, $urls);
    }

    /** @return Marble[] */
    private function hydrateAll(array $rows): array
    {
        $ids = array_map(fn (array $row) => (int) $row['marble_id'], $rows);
        $photos = $this->photoLinks->forIds($ids);
        $moments = $this->perspectiveMoments->forPerspectives('marble', $ids);
        $marbles = [];
        foreach ($rows as $row) {
            $marble = $this->hydrate($row);
            $marble->moments = $moments[$marble->marble_id] ?? [];
            $marble->photos = $photos[$marble->marble_id] ?? [];
            $marbles[] = $marble;
        }
        return $marbles;
    }

    private function hydrate(array $row): Marble
    {
        return new Marble(
            marble_id: (int) $row['marble_id'],
            marble_alias: $row['marble_alias'],
            marble_name: $row['marble_name'],
            team_name: $row['team_name'] ?? null,
            size: $row['size'],
            color: $row['color'],
            quantity: (int) $row['quantity'],
            description: $row['description'] ?? null,
        );
    }
   ```
6. `grep -n 'hydrate(\|this->marble_id\|loadPhotos\|loadMoments' classes/Database/MarblesRepository.php`. Expected: `hydrate(` appears only inside `hydrateAll`.

- [ ] **Step 2: Run the characterisation tests and the suite.** Expected: PASS.

- [ ] **Step 3: Commit**

```bash
git add classes/Database/MarblesRepository.php
git commit -m "Stop MarblesRepository remembering a current row" \
  -m "The last repository to hold a hidden row ID. Photos and moments load for the whole list at once, and photo saves name their marble." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

### Task 32: Marble form posts the photos it loaded

**Files:**
- Modify: `templates/admin/marbles/marble.tpl.php`
- Modify: `wwwroot/admin/marbles/marble.php:61-68`
- Modify: `tests/Integration/RepositoryState/FormLoadedIdsTest.php`

- [ ] **Step 1: Add the failing test** (variables from `grep -n '\$page->set' wwwroot/admin/marbles/marble.php`)

```php
    public function testMarbleForm(): void
    {
        $id = $this->makeRow('marble');
        [$a, $b] = [$this->makePhoto('a'), $this->makePhoto('b')];
        $this->link('marbles_2_photos', 'marble_id', $id, $a, 1);
        $this->link('marbles_2_photos', 'marble_id', $id, $b, 2);

        $html = $this->renders('admin/marbles/marble.tpl.php', [
            'errors' => [],
            'marble' => (new \Database\MarblesRepository($this->db()))->findById($id),
        ]);

        $this->assertPostsLoadedIds($html, [$a, $b]);
    }
```

- [ ] **Step 2: Run it.** Expected: FAIL.

- [ ] **Step 3: Template.** Directly after the opening `<form …>` tag:
```php
<?php foreach ($marble->photos ?? [] as $photo): ?>
        <input type="hidden" name="loaded_photo_ids[]" value="<?= (int) $photo->photo_id ?>">
<?php endforeach; ?>
```

- [ ] **Step 4: Run it, then commit the template**

```bash
git add templates/admin/marbles/marble.tpl.php tests/Integration/RepositoryState/FormLoadedIdsTest.php
git commit -m "Post back the photos the Marble form loaded" \
  -m "The marble form lists photo URLs in a textarea rather than inputs, but posts the loaded IDs the same way as the other forms." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

- [ ] **Step 5: Handler.** In `wwwroot/admin/marbles/marble.php` replace
```php
        $repo->setMarbleId($marble_id);
        if (!empty($photo_urls)) {
            $repo->savePhotosFromUrls($photo_urls);
        } else {
            $repo->savePhotos([]);
        }
```
with
```php
        // A new marble has loaded nothing, so postedIds() is [] and nothing is removed.
        $repo->savePhotosFromForm($marble_id, \Utilities::postedIds('loaded_photo_ids'), $photo_urls);
```
An empty textarea against a loaded list now means "remove the ones you loaded", so the `savePhotos([])` branch is no longer needed.

- [ ] **Step 6: Lint and commit**

```bash
git add wwwroot/admin/marbles/marble.php
git commit -m "Save Marble photos by explicit ID" \
  -m "One call covers both the filled and the emptied textarea, since an empty list against what the form loaded already means remove those." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

### Task 33: Marble API adds by explicit ID

**Files:**
- Modify: `wwwroot/api/v1/marbles/index.php:83-87`

- [ ] **Step 1:** Replace
```php
        $repo->setMarbleId($marble->marble_id);
        $urls = array_filter(array_map('trim', $input['photo_urls']));
        if (!empty($urls)) {
            $repo->addPhotosFromUrls($urls);
        }
```
with
```php
        $urls = array_filter(array_map('trim', $input['photo_urls']));
        if (!empty($urls)) {
            $repo->addPhotosFromUrls($marble->marble_id, $urls);
        }
```

- [ ] **Step 2: Lint, grep and commit**

Run: `php -l wwwroot/api/v1/marbles/index.php && grep -rn 'setMarbleId' wwwroot templates classes`. Expected: clean, no output.
```bash
git add wwwroot/api/v1/marbles/index.php
git commit -m "Add Marble photos through the API by explicit ID" \
  -m "The API only ever appends, and now says which marble it is appending to." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

### Task 34: Delete HasPhotos and HasMoments

**Files:**
- Delete: `classes/Domain/HasPhotos.php`
- Delete: `classes/Domain/HasMoments.php`
- Modify: `classes/Media/Photo.php:15` (comment only)

- [ ] **Step 1: Prove nothing uses them**

Run: `grep -rn -E 'HasPhotos|HasMoments|savePhotosFromUrls|saveMoments|getMomentLinkingTable|getPhotoLinkingTable|loadPhotos|loadMoments|set(Notebook|Page|Episode|Moment|Worker|Part|Marble)Id\(' classes wwwroot templates scripts tests`
Expected: matches only inside `classes/Domain/HasPhotos.php` and `classes/Domain/HasMoments.php`.

- [ ] **Step 2: Delete them and fix the stale comment**

```bash
git rm classes/Domain/HasPhotos.php classes/Domain/HasMoments.php
sed -i 's|public bool \$isPrimary = false; // used in HasPhotos trait|public bool $isPrimary = false; // the link'"'"'s is_primary; set by PhotoLinks|' classes/Media/Photo.php
```

- [ ] **Step 3: Run the whole suite**

Expected: every test green except the pre-existing failures from Task 1. The full `--group step0` passes.

- [ ] **Step 4: Commit**

```bash
git add -A classes/Domain classes/Media/Photo.php
git commit -m "Delete the HasPhotos and HasMoments traits" \
  -m "No repository uses them any more. Their saveMoments() never had a caller, and the hidden-ID pattern they depended on is gone from all seven repositories." \
  -m "Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>"
```

### Task 35: Gate for bubble 8, then hand back

- [ ] **Bubble Gate** with:
  - branch `stateless-marbles`
  - DONE `DONE stateless Marbles`
  - expected red: none
  - list `https://db.marbletrack3.com/admin/marbles/index.php`
  - edit `https://db.marbletrack3.com/admin/marbles/marble.php?id=<id>`
  - API path `marbles`
- [ ] **Final diff against the baseline.** `php scripts/photo_links_snapshot.php --diff $SCRATCH/baseline.json <latest after snapshot>`. Expected: only rows changed by real use since the baseline, never a row losing photos it had. Show Rob any lines.
- [ ] **Tell Rob:** the chain `stateless-repositories → … → stateless-marbles` is deployed and checked, and `stateless-marbles` is the tip for him to bring master up to when he chooses. Also mention one thing seen and not fixed: `syncMomentsFromTranslations()` on Workers and Parts has the same "stale form wipes what was added meanwhile" shape for moment perspectives.
- [ ] **Delete** `$SCRATCH/admin_cookie.txt`.
