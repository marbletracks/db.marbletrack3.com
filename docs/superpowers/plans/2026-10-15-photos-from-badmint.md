# Photos from badmint (MT3 side) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let db.marbletrack3.com take photos from badmint's `/mt3/` page: Tracks get photos, the Moment form gets an upload button and a `postMessage` listener, and the Part, Track and Worker pages link to badmint instead of posting to badmin's `bullet.php`.

**Architecture:** A `tracks_2_photos` link table and a `PhotoLinks` in `TrackRepository`, exactly as the other kinds have; `PATCH /api/v1/tracks/<id>` appends `photo_urls`. The admin Track form gets the same `loaded_photo_ids[]` + `image_urls[]` rows as Parts. The Moment form opens badmint with `?for=moment` and the Part and Workers from its Notes, and a small listener turns each posted-back photo into an Image URL row, so MT3's own Save links it (badmint never creates Moments). The Parts image page becomes a redirect to badmint.

**Tech Stack:** PHP 8.1 (Docker) / Dreamhost PHP, mysqli via `Database\Database`, PHPUnit 9.6 in Docker (`docker-compose.yml`, `phpunit-docker.xml`), plain JS, no framework.

**Spec:** `~/work/rob/badmint.robnugen.com/docs/superpowers/specs/2026-10-13-mt3-uploads-design.md` — sections "MT3 side", "Design page", "Decided (round 2)", "Decided (round 3)", "MT3 photo linking". The badmint side is DONE at badmint `0171cf0` (mg #709).

## Global Constraints

- Cannot commit on `master`; work is on branch `photos-from-badmint` (from `9ee909c`). BEGIN commit is the version uptick only (`SENTIMENTAL_VERSION` in `prepend.php`). Close with `git close-bubble`; never checkout or merge master.
- Commit format: multiple `-m`, imperative subject ≤50 chars, body says why, ending with the two attribution lines (`Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>`, `Claude-Session: https://claude.ai/code/session_014FNHyAimU44oxxFiqPTRr3`).
- Test gate: `docker compose exec -T php vendor/bin/phpunit -c phpunit-docker.xml` (whole suite) from `~/work/rob/db.marbletrack3.com`; one file by appending its path. The Docker MySQL is seeded from `dbmt3.sql`, so a new table goes into `db_schemas/`, into the running container, and into `dbmt3.sql`.
- Origins are strict both ways: badmint posts only to `https://db.marbletrack3.com`; MT3's listener accepts only `https://badmint.robnugen.com` and only `{type:'mt3-photos', v:1, photos:[{url, thumb, name}]}`.
- badmint's page reads `for=moment`, `part=`, `track=`, `worker=` (id or alias; `worker` may be a comma list) and `moment=<id>`; nothing else.
- Notes and Perspectives are never touched by the listener; hidden `loaded_photo_ids[]` comes from the server and is never touched.
- Deploy is `ssh db_mt3_deploy photos-from-badmint` after `git push`, per the repo's deploy skill; production is checked from the branch, not from master.
- Production schema change (the new table) is applied by hand over SSH after deploy, with the same SQL file.

## Review Focus

1. A `message` event from any origin other than `https://badmint.robnugen.com`, or with another `type`/`v`, must be ignored (Task 8; JS has no harness, pinned by the static test on the origin check and checked live).
2. A posted-back URL already present in an Image URL row must not be added twice (Task 8; same).
3. A Track with no photos must serialize `photos: []` on `GET /api/v1/tracks/<id>` and show no rows on the admin page (Task 3 test, Task 4 live).
4. `PATCH /api/v1/tracks/<id>` with `photo_urls` absent or empty must change no links; present, it appends and dedupes (Task 3 test covers the repository; Task 4 live).
5. `/admin/parts/images/create.php` with a bad `part_id` must still go to the Parts image index, never to badmint (Task 6).

---

### Task 1: BEGIN the bubble

**Files:**
- Modify: `prepend.php:3`

- [ ] **Step 1: Uptick the sentimental version**

Change `const SENTIMENTAL_VERSION = "Stateless follow-ups";` to `const SENTIMENTAL_VERSION = "Photos from badmint";`.

- [ ] **Step 2: Commit**

```bash
git add prepend.php
git commit -m "BEGIN photos from badmint" -m "mg #709: Tracks get photos, the Moment form takes photos back from badmint's /mt3/ page, and the Part, Track and Worker pages link there instead of posting to badmin's bullet.php." -m "Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>" -m "Claude-Session: https://claude.ai/code/session_014FNHyAimU44oxxFiqPTRr3"
```

### Task 2: The tracks_2_photos table

**Files:**
- Create: `db_schemas/10_tracks/create_tracks_2_photos.sql`
- Modify: `dbmt3.sql` (after the `tracks` table's dump block)

**Interfaces:**
- Produces: table `tracks_2_photos(track_id, photo_id, photo_sort, is_primary, created_at)`, same shape as `moments_2_photos`.

- [ ] **Step 1: Write the schema file**

```sql
-- Photos of a Track (mg #709). badmint files them on b.robnugen.com and PATCHes the
-- _1000 URL to /api/v1/tracks/<id>; same shape as moments_2_photos.
CREATE TABLE IF NOT EXISTS tracks_2_photos (
    track_id INT NOT NULL,
    photo_id INT NOT NULL,
    photo_sort INT NOT NULL DEFAULT 0,
    is_primary BOOLEAN DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (track_id, photo_id),
    KEY photo_id (photo_id),
    FOREIGN KEY (track_id) REFERENCES tracks(track_id) ON DELETE CASCADE,
    FOREIGN KEY (photo_id) REFERENCES photos(photo_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

- [ ] **Step 2: Apply it to the Docker test database**

Run: `docker compose exec -T mysql mysql -uroot -pmt3_test_pass dbmt3_test < db_schemas/10_tracks/create_tracks_2_photos.sql && docker compose exec -T mysql mysql -uroot -pmt3_test_pass dbmt3_test -e "SHOW CREATE TABLE tracks_2_photos\G" | head -5`
Expected: the CREATE TABLE echoed back (password warning on stderr is noise).

- [ ] **Step 3: Add the table to dbmt3.sql**

Find the block that ends the `tracks` dump (`CREATE TABLE \`tracks\` (` … `) ENGINE=InnoDB …;` followed by `/*!40101 SET character_set_client = @saved_cs_client */;`). After that line insert, in mysqldump style:

```sql

--
-- Table structure for table `tracks_2_photos`
--

DROP TABLE IF EXISTS `tracks_2_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tracks_2_photos` (
  `track_id` int NOT NULL,
  `photo_id` int NOT NULL,
  `photo_sort` int NOT NULL DEFAULT '0',
  `is_primary` tinyint(1) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`track_id`,`photo_id`),
  KEY `photo_id` (`photo_id`),
  CONSTRAINT `tracks_2_photos_ibfk_1` FOREIGN KEY (`track_id`) REFERENCES `tracks` (`track_id`) ON DELETE CASCADE,
  CONSTRAINT `tracks_2_photos_ibfk_2` FOREIGN KEY (`photo_id`) REFERENCES `photos` (`photo_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
```

Match the `COLLATE` of the `moments_2_photos` block in the file (copy it).

- [ ] **Step 4: Prove the dump loads with the new table**

Run: `docker compose exec -T mysql sh -c 'mysql -uroot -pmt3_test_pass -e "DROP DATABASE IF EXISTS dump_check; CREATE DATABASE dump_check" && mysql -uroot -pmt3_test_pass dump_check < /docker-entrypoint-initdb.d/00-schema.sql && mysql -uroot -pmt3_test_pass dump_check -N -e "SHOW TABLES LIKE \"%2_photos\"" && mysql -uroot -pmt3_test_pass -e "DROP DATABASE dump_check"'`
Expected: 9 rows including `tracks_2_photos` (the bind mount serves the edited file).

- [ ] **Step 5: Commit**

```bash
git add db_schemas/10_tracks/create_tracks_2_photos.sql dbmt3.sql
git commit -m "Add tracks_2_photos" -m "Tracks were the one kind with no photo links. Same shape as moments_2_photos so PhotoLinks can serve it unchanged. dbmt3.sql carries it so a fresh Docker database has the table." -m "Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>" -m "Claude-Session: https://claude.ai/code/session_014FNHyAimU44oxxFiqPTRr3"
```

### Task 3: Track photos in TrackRepository

**Files:**
- Modify: `classes/Physical/Track.php:10-13`
- Modify: `classes/Database/TrackRepository.php` (constructor, `findById`, `findByAlias`, `findAll`, new methods)
- Modify: `tests/Integration/RepositoryState/Support/PhotoFixtures.php:36-82,127-132`
- Create: `tests/Integration/RepositoryState/TrackPhotosTest.php`

**Interfaces:**
- Consumes: `Database\PhotoLinks` (`forIds`, `addUrls`, `saveFormUrls`), table from Task 2.
- Produces: `Track::$photos` (`Media\Photo[]`), `TrackRepository::addPhotosFromUrls(int $track_id, array $urls): void`, `TrackRepository::savePhotosFromForm(int $track_id, array $loadedPhotoIds, array $urls): void`. `findById`, `findByAlias` and `findAll` fill `$photos`; the navigation finders (upstream, downstream, landing zones, by entity type, by part) leave it empty.

- [ ] **Step 1: Teach the fixtures to make a Track**

In `PhotoFixtures::makeRow()` add before `throw`:

```php
            case 'track':
                $id = (new TrackRepository($db))->insert("{$p}t{$n}", "{$p}Track {$n}", 'fixture', ['small'], true, false, false);
                $this->track('tracks', 'track_id', $id);
                return $id;
```

Add `use Database\TrackRepository;` to the use list, and `'tracks_2_photos' => 'track_id',` to `$linkTables` in `cleanupFixtures()`.

- [ ] **Step 2: Write the failing test**

```php
<?php

use Database\TrackRepository;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Support/PhotoFixtures.php';

/** Tracks get photos the way every other kind has them: through PhotoLinks (mg #709). */
class TrackPhotosTest extends TestCase
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

    private function repo(): TrackRepository
    {
        return new TrackRepository($this->db());
    }

    private function urls(array $photos): array
    {
        return array_map(fn ($photo) => $photo->getUrl(), $photos);
    }

    public function testATrackWithoutPhotosHasAnEmptyList(): void
    {
        $id = $this->makeRow('track');
        $this->assertSame([], $this->repo()->findById($id)->photos);
    }

    public function testAddPhotosFromUrlsAppendsAndFindByIdLoadsThem(): void
    {
        $id = $this->makeRow('track');
        $this->makePhoto('a');
        $this->makePhoto('b');

        $this->repo()->addPhotosFromUrls($id, [$this->url('a'), $this->url('b')]);
        $this->repo()->addPhotosFromUrls($id, [$this->url('b')]);   // the API's Retry: harmless

        $track = $this->repo()->findById($id);
        $this->assertSame([$this->url('a'), $this->url('b')], $this->urls($track->photos));
        $this->assertSame($this->linkedIds('tracks_2_photos', 'track_id', $id), $this->photoIds($track->photos));
        $this->assertSame($this->urls($track->photos), $this->urls($this->repo()->findByAlias($track->track_alias)->photos));
    }

    public function testFindAllCarriesEachTracksOwnPhotos(): void
    {
        $a = $this->makeRow('track');
        $b = $this->makeRow('track');
        $pa = $this->makePhoto('a');
        $pb = $this->makePhoto('b');
        $this->link('tracks_2_photos', 'track_id', $a, $pa, 1);
        $this->link('tracks_2_photos', 'track_id', $b, $pb, 1);

        $byId = [];
        foreach ($this->repo()->findAll() as $track) {
            $byId[$track->track_id] = $track;
        }

        $this->assertSame([$pa], $this->photoIds($byId[$a]->photos));
        $this->assertSame([$pb], $this->photoIds($byId[$b]->photos));
    }

    public function testFormSaveRemovesOnlyWhatTheFormDeleted(): void
    {
        $id = $this->makeRow('track');
        [$p1, $p2, $api] = [$this->makePhoto('p1'), $this->makePhoto('p2'), $this->makePhoto('api')];
        $this->link('tracks_2_photos', 'track_id', $id, $p1, 1);
        $this->link('tracks_2_photos', 'track_id', $id, $p2, 2);
        $this->link('tracks_2_photos', 'track_id', $id, $api, 3);   // attached by badmint while the form was open

        $this->repo()->savePhotosFromForm($id, [$p1, $p2], [$this->url('p2')]);

        $this->assertSame([$p2, $api], $this->linkedIds('tracks_2_photos', 'track_id', $id));
    }
}
```

- [ ] **Step 3: Run it to see it fail**

Run: `docker compose exec -T php vendor/bin/phpunit -c phpunit-docker.xml tests/Integration/RepositoryState/TrackPhotosTest.php`
Expected: FAIL — `Call to undefined method Database\TrackRepository::addPhotosFromUrls()` and, in the first test, `Undefined property: Physical\Track::$photos` (or an empty-array assertion failing on null).

- [ ] **Step 4: Implement**

`Track.php`: after `public array $parts = [];` add

```php
    public array $photos = [];        // Media\Photo[]; filled by findById, findByAlias and findAll
```

`TrackRepository.php`:

```php
    private DbInterface $db;
    private PhotoLinks $photoLinks;

    public function __construct(DbInterface $db)
    {
        $this->db = $db;
        $this->photoLinks = new PhotoLinks($db, 'tracks', 'tracks_2_photos', 'track_id');
    }
```

In `findById` and `findByAlias`, replace `return $this->hydrate($results->data);` with `return $this->withPhotos([$this->hydrate($results->data)])[0];`. In `findAll`, replace `return $tracks;` with `return $this->withPhotos($tracks);`. Add:

```php
    /**
     * @param Track[] $tracks
     * @return Track[] the same objects, photos filled in two queries
     */
    private function withPhotos(array $tracks): array
    {
        $photos = $this->photoLinks->forIds(array_map(fn (Track $t) => $t->track_id, $tracks));
        foreach ($tracks as $track) {
            $track->photos = $photos[$track->track_id] ?? [];
        }
        return $tracks;
    }

    /** The admin form's photo list: removes only what the form loaded and dropped. */
    public function savePhotosFromForm(int $track_id, array $loadedPhotoIds, array $urls): void
    {
        $this->photoLinks->saveFormUrls($track_id, $loadedPhotoIds, $urls);
    }

    /** Append, skipping URLs already linked; what the API and badmint use. */
    public function addPhotosFromUrls(int $track_id, array $urls): void
    {
        $this->photoLinks->addUrls($track_id, $urls);
    }
```

- [ ] **Step 5: Run the test file, then the whole suite**

Run: `docker compose exec -T php vendor/bin/phpunit -c phpunit-docker.xml tests/Integration/RepositoryState/TrackPhotosTest.php`
Expected: OK (4 tests).
Run: `docker compose exec -T php vendor/bin/phpunit -c phpunit-docker.xml 2>&1 | tail -4`
Expected: OK, same count as before plus 4 (note the baseline count from a run before Task 1).

- [ ] **Step 6: Commit**

```bash
git add classes/Physical/Track.php classes/Database/TrackRepository.php tests/Integration/RepositoryState/Support/PhotoFixtures.php tests/Integration/RepositoryState/TrackPhotosTest.php
git commit -m "Give Tracks photos through PhotoLinks" -m "findById, findByAlias and findAll fill Track::\$photos; the navigation finders leave it empty since nothing shows photos there. savePhotosFromForm and addPhotosFromUrls mirror PartsRepository so the admin form and the API work the same way for every kind." -m "Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>" -m "Claude-Session: https://claude.ai/code/session_014FNHyAimU44oxxFiqPTRr3"
```

### Task 4: photo_urls on the Tracks API

**Files:**
- Modify: `wwwroot/api/v1/tracks/index.php:1-6,39-54,82-99`

**Interfaces:**
- Consumes: `TrackRepository::addPhotosFromUrls`, `Track::$photos`.
- Produces: `PATCH /api/v1/tracks/<id>` with `{"photo_urls": [...]}` appends; `GET`/`PATCH` single-track JSON carries `photos: [{photo_id, url}]`.

No unit harness runs these handlers (they include `_auth.php` and act at include time); checked live in Task 11.

- [ ] **Step 1: Implement**

Docblock line 5 becomes ` * PATCH /api/v1/tracks/6     — update track descriptions and/or add photo_urls`.

After the `technical_description` block (line 50) add:

```php
    if (!empty($input['photo_urls']) && is_array($input['photo_urls'])) {
        $repo->addPhotosFromUrls($track->track_id, $input['photo_urls']);
    }
```

Replace both `echo json_encode($updated);` (PATCH) and `echo json_encode($track);` (GET single) with `echo json_encode(trackToArray($updated));` / `echo json_encode(trackToArray($track));`, and add at the bottom (after the 405):

```php
// ── Helpers ──────────────────────────────────────────────────────────────────

function trackToArray(\Physical\Track $track): array
{
    $data = get_object_vars($track);
    $data['photos'] = array_map(fn (\Media\Photo $photo) => [
        'photo_id' => $photo->photo_id,
        'url'      => $photo->getUrl(),
    ], $track->photos);
    return $data;
}
```

- [ ] **Step 2: Lint**

Run: `docker compose exec -T php php -l wwwroot/api/v1/tracks/index.php`
Expected: `No syntax errors detected`.

- [ ] **Step 3: Commit**

```bash
git add wwwroot/api/v1/tracks/index.php
git commit -m "Take photo_urls on PATCH /api/v1/tracks" -m "badmint attaches a filed Track photo the same way it does for Parts, Workers and Moments. Single-track JSON now lists photos as {photo_id, url} like the Parts endpoint, so a reader sees the resolved URL rather than a raw code." -m "Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>" -m "Claude-Session: https://claude.ai/code/session_014FNHyAimU44oxxFiqPTRr3"
```

### Task 5: Photo rows on the admin Track form

**Files:**
- Modify: `wwwroot/admin/tracks/track.php:21-30,58-86`
- Modify: `templates/admin/tracks/track.tpl.php:12,232-234`
- Modify: `tests/Unit/FormHandlerWiringTest.php:32-40`

**Interfaces:**
- Consumes: `TrackRepository::savePhotosFromForm`, `TrackRepository::insert(): int`.

- [ ] **Step 1: Add the handler to the wiring test**

In `handlers()` add `'tracks/track.php',` after `'parts/part.php',`.

- [ ] **Step 2: Run it to see it fail**

Run: `docker compose exec -T php vendor/bin/phpunit -c phpunit-docker.xml tests/Unit/FormHandlerWiringTest.php`
Expected: 1 failure: `tracks/track.php must read loaded_photo_ids`.

- [ ] **Step 3: Implement the handler**

In `track.php` after `$entity_type = …;` (line 29) add `$image_urls = array_filter(array_map('trim', $_POST['image_urls'] ?? []));`. In the save block, after `$repo->update(...)` add `$repo->savePhotosFromForm($track->track_id, \Utilities::postedIds('loaded_photo_ids'), $image_urls);` and change the insert to `$new_track_id = $repo->insert(...)` followed by `$repo->savePhotosFromForm($new_track_id, [], $image_urls);`.

- [ ] **Step 4: Implement the template**

After `<form action="" method="post">` (line 12) add:

```php
<?php foreach ($track->photos ?? [] as $photo): ?>
        <input type="hidden" name="loaded_photo_ids[]" value="<?= (int) $photo->photo_id ?>">
<?php endforeach; ?>
```

Before `<button type="submit"><?= $track ? 'Update Track' : 'Create Track' ?></button>` add:

```php
        <label>
            Image URLs:
            <?php if ($track): ?>
                <a href="https://badmint.robnugen.com/mt3/?track=<?= (int) $track->track_id ?>" target="_blank">📷 Upload &amp; crop…</a>
            <?php endif; ?>
            <br>
            <div id="image-url-fields">
<?php foreach ($track->photos ?? [] as $photo): ?>
    <a href="<?= htmlspecialchars($photo->getUrl()) ?>"><img src="<?= htmlspecialchars($photo->getThumbnailUrl()) ?>" alt="Image preview"></a><br>
                <input type="text" size=130 name="image_urls[]" value="<?= htmlspecialchars($photo->getUrl()) ?>"><br>
<?php endforeach; ?>
                <!-- add empty row so we always have space -->
                <input type="text" size=130 name="image_urls[]" value=""><br>
            </div>
            <button type="button" onclick="addImageUrlField()">Add another</button>
        </label><br><br>

        <script>
            function addImageUrlField() {
                const div = document.getElementById('image-url-fields');
                const input = document.createElement('input');
                input.type = 'text';
                input.name = 'image_urls[]';
                div.appendChild(input);
                div.appendChild(document.createElement('br'));
            }
        </script>

```

- [ ] **Step 5: Run the wiring test, lint, then the suite**

Run: `docker compose exec -T php vendor/bin/phpunit -c phpunit-docker.xml tests/Unit/FormHandlerWiringTest.php && docker compose exec -T php php -l wwwroot/admin/tracks/track.php && docker compose exec -T php php -l templates/admin/tracks/track.tpl.php`
Expected: OK (8 tests), no syntax errors.

- [ ] **Step 6: Commit**

```bash
git add wwwroot/admin/tracks/track.php templates/admin/tracks/track.tpl.php tests/Unit/FormHandlerWiringTest.php
git commit -m "Show and edit a Track's photos on its admin page" -m "Same loaded_photo_ids[] + image_urls[] rows as the Part form, saved through savePhotosFromForm so a photo badmint attached while the form was open survives. The upload link opens badmint's /mt3/ page with this Track preselected." -m "Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>" -m "Claude-Session: https://claude.ai/code/session_014FNHyAimU44oxxFiqPTRr3"
```

### Task 6: The Parts image page becomes a link to badmint

**Files:**
- Create: `tests/Unit/NothingPostsToBadminTest.php`
- Modify: `wwwroot/admin/parts/images/create.php` (whole file)
- Modify: `templates/admin/parts/images/index.tpl.php:46` (the per-part upload link)
- Delete: `templates/admin/parts/images/create.tpl.php`, `wwwroot/admin/parts/images/save_photos.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

use PHPUnit\Framework\TestCase;

/**
 * Photos are uploaded through badmint's /mt3/ page now (mg #709). Nothing in MT3 may
 * post to badmin's bullet.php, which is the last reason that script still exists.
 */
class NothingPostsToBadminTest extends TestCase
{
    public function testNoTemplateOrPageMentionsBulletPhp(): void
    {
        $root = dirname(__DIR__, 2);
        $hits = [];
        foreach (['templates', 'wwwroot'] as $dir) {
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("{$root}/{$dir}"));
            foreach ($it as $file) {
                if ($file->isFile() && preg_match('/\.(php|js|html)$/', $file->getFilename())
                    && strpos((string) file_get_contents($file->getPathname()), 'bullet.php') !== false) {
                    $hits[] = substr($file->getPathname(), strlen($root) + 1);
                }
            }
        }
        $this->assertSame([], $hits, 'bullet.php is still referenced');
    }

    public function testThePartsImagePageSendsToBadmint(): void
    {
        $root = dirname(__DIR__, 2);
        $create = file_get_contents("{$root}/wwwroot/admin/parts/images/create.php");
        $this->assertStringContainsString('https://badmint.robnugen.com/mt3/?part=', $create);
        $this->assertStringNotContainsString('setTemplate', $create, 'create.php is a redirect, not a page');
        $index = file_get_contents("{$root}/templates/admin/parts/images/index.tpl.php");
        $this->assertStringContainsString('https://badmint.robnugen.com/mt3/?part=', $index);
        $this->assertFileDoesNotExist("{$root}/wwwroot/admin/parts/images/save_photos.php");
    }
}
```

- [ ] **Step 2: Run it to see it fail**

Run: `docker compose exec -T php vendor/bin/phpunit -c phpunit-docker.xml tests/Unit/NothingPostsToBadminTest.php`
Expected: 2 failures (`create.tpl.php` references bullet.php; create.php has no badmint URL).

- [ ] **Step 3: Check nothing else uses the deleted files**

Run: `grep -rn "save_photos\|images/create" --include=*.php --include=*.js --include=*.md . | grep -v vendor | grep -v docs/superpowers`
Expected: only `templates/admin/parts/images/index.tpl.php` (the link) and the two files themselves. Anything else is a finding: fix the reference in this task.

- [ ] **Step 4: Implement**

`create.php` becomes:

```php
<?php
declare(strict_types=1);

# Must include here because DH runs FastCGI https://www.phind.com/search?cache=zfj8o8igbqvaj8cm91wp1b7k
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

// Part photos are uploaded, straightened and cropped on badmint's /mt3/ page, which
// files them on b.robnugen.com and attaches them here through the API (mg #709).
// This address stays so old bookmarks and links still land somewhere useful.
if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

$part_id = intval($_GET['part_id'] ?? 0);
if ($part_id <= 0) {
    header("Location: /admin/parts/images/");
    exit;
}

header("Location: https://badmint.robnugen.com/mt3/?part=" . $part_id);
exit;
```

`index.tpl.php` line 46: the `<a href="/admin/parts/images/create.php?part_id=…"` becomes `<a href="https://badmint.robnugen.com/mt3/?part=<?= (int) $part->part_id ?>" target="_blank"` with the rest of the tag unchanged.

```bash
git rm templates/admin/parts/images/create.tpl.php wwwroot/admin/parts/images/save_photos.php
```

- [ ] **Step 5: Run the test, lint, then the suite**

Run: `docker compose exec -T php vendor/bin/phpunit -c phpunit-docker.xml tests/Unit/NothingPostsToBadminTest.php && docker compose exec -T php php -l wwwroot/admin/parts/images/create.php`
Expected: OK (2 tests); no syntax errors.

- [ ] **Step 6: Commit**

```bash
git add tests/Unit/NothingPostsToBadminTest.php wwwroot/admin/parts/images/create.php templates/admin/parts/images/index.tpl.php
git commit -m "Send Part photo uploads to badmint" -m "The six-slot form posted across sites to badmin's bullet.php with a shared password, then save_photos.php linked the URLs. badmint's /mt3/ page does the upload, the crop and the attach, so the page is a redirect and save_photos.php goes. Nothing outside badmin posts to bullet.php now, which is what lets it retire." -m "Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>" -m "Claude-Session: https://claude.ai/code/session_014FNHyAimU44oxxFiqPTRr3"
```

### Task 7: Upload links on the Part and Worker forms

**Files:**
- Create: `tests/Unit/BadmintLinksTest.php`
- Modify: `templates/admin/parts/part.tpl.php:144-145`
- Modify: `templates/admin/workers/worker.tpl.php:109-110`

- [ ] **Step 1: Write the failing test**

```php
<?php

use PHPUnit\Framework\TestCase;

/** Each kind's edit page links to badmint's /mt3/ page with itself preselected (mg #709). */
class BadmintLinksTest extends TestCase
{
    /** @dataProvider pages */
    public function testEditPageLinksToBadmintWithItselfPreselected(string $template, string $query): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . "/templates/admin/{$template}");
        $this->assertNotFalse($source);
        $this->assertStringContainsString("https://badmint.robnugen.com/mt3/?{$query}=<?= (int) ", $source);
    }

    public function pages(): array
    {
        return [
            'part' => ['parts/part.tpl.php', 'part'],
            'worker' => ['workers/worker.tpl.php', 'worker'],
            'track' => ['tracks/track.tpl.php', 'track'],
        ];
    }
}
```

- [ ] **Step 2: Run it to see it fail**

Run: `docker compose exec -T php vendor/bin/phpunit -c phpunit-docker.xml tests/Unit/BadmintLinksTest.php`
Expected: 2 failures (part, worker); track passes from Task 5.

- [ ] **Step 3: Implement**

`part.tpl.php`: replace

```php
        <label>
            Image URLs:<br>
```

with

```php
        <label>
            Image URLs:
            <?php if ($part): ?>
                <a href="https://badmint.robnugen.com/mt3/?part=<?= (int) $part->part_id ?>" target="_blank">📷 Upload &amp; crop…</a>
            <?php endif; ?>
            <br>
```

`worker.tpl.php`: the same around its `Image URLs:<br>` with `$worker` and `?worker=<?= (int) $worker->worker_id ?>`.

- [ ] **Step 4: Run the test, lint, then the suite**

Run: `docker compose exec -T php vendor/bin/phpunit -c phpunit-docker.xml tests/Unit/BadmintLinksTest.php && docker compose exec -T php php -l templates/admin/parts/part.tpl.php && docker compose exec -T php php -l templates/admin/workers/worker.tpl.php`
Expected: OK (3 tests); no syntax errors.

- [ ] **Step 5: Commit**

```bash
git add tests/Unit/BadmintLinksTest.php templates/admin/parts/part.tpl.php templates/admin/workers/worker.tpl.php
git commit -m "Link the Part and Worker forms to badmint uploads" -m "badmint attaches through the API while the form is open, and the stateless save keeps those links, so a plain link in a new tab is enough; reload to see the new rows." -m "Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>" -m "Claude-Session: https://claude.ai/code/session_014FNHyAimU44oxxFiqPTRr3"
```

### Task 8: The Moment form's upload button and listener

**Files:**
- Create: `wwwroot/admin/js/mt3-photos.js`
- Create: `tests/Unit/Mt3PhotosListenerTest.php`
- Modify: `templates/admin/moments/moment.tpl.php:71-82,117-119`

**Interfaces:**
- Consumes: badmint's message `{type:'mt3-photos', v:1, photos:[{url, thumb, name}]}` from origin `https://badmint.robnugen.com`; `data.perspectives` (`{type, id, name}`) from `/admin/ajax/expand_shortcodes.php`, already fetched by the template's preview code.
- Produces: `window.mt3PhotosPerspectives(list)` called by the template after each preview; `#mt3-photos-open` button with `data-moment-id`.

- [ ] **Step 1: Write the failing test**

```php
<?php

use PHPUnit\Framework\TestCase;

/**
 * The Moment form takes photos back from badmint by postMessage (mg #709). The
 * contract that matters is the origin check; JS has no harness here, so this pins
 * the strings that carry it and that the template wires the script in.
 */
class Mt3PhotosListenerTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function testTheListenerAcceptsOnlyBadmintAndOnlyItsMessage(): void
    {
        $js = file_get_contents("{$this->root}/wwwroot/admin/js/mt3-photos.js");
        $this->assertNotFalse($js);
        $this->assertStringContainsString("const BADMINT = 'https://badmint.robnugen.com';", $js);
        $this->assertStringContainsString('if (event.origin !== BADMINT) return;', $js);
        $this->assertStringContainsString("data.type !== 'mt3-photos' || data.v !== 1", $js);
        $this->assertStringContainsString("startsWith('https://b.robnugen.com/')", $js);
    }

    public function testTheMomentFormWiresTheButtonAndTheScript(): void
    {
        $tpl = file_get_contents("{$this->root}/templates/admin/moments/moment.tpl.php");
        $this->assertNotFalse($tpl);
        $this->assertStringContainsString('id="mt3-photos-open"', $tpl);
        $this->assertStringContainsString('data-moment-id="<?= (int) ($moment->moment_id ?? 0) ?: \'\' ?>"', $tpl);
        $this->assertStringContainsString('<script src="/admin/js/mt3-photos.js" defer></script>', $tpl);
        $this->assertStringContainsString('window.mt3PhotosPerspectives(data.perspectives || [])', $tpl);
    }
}
```

- [ ] **Step 2: Run it to see it fail**

Run: `docker compose exec -T php vendor/bin/phpunit -c phpunit-docker.xml tests/Unit/Mt3PhotosListenerTest.php`
Expected: 2 failures (no JS file; template lacks the button).

- [ ] **Step 3: Write the script**

`wwwroot/admin/js/mt3-photos.js`:

```js
// Photos from badmint (mg #709). "📷 Upload & crop…" opens badmint's /mt3/ page for
// the Part and Workers named in the Notes (a popup on desktop, a tab on the phone).
// Each photo it saves comes back by postMessage and becomes an Image URL row, so this
// form's own Save links it: badmint never creates a Moment. Only badmint is heard, and
// only its one message. Notes, Perspectives and loaded_photo_ids are never touched.
(function () {
  'use strict';
  const BADMINT = 'https://badmint.robnugen.com';
  const fields = document.getElementById('image-url-fields');
  const button = document.getElementById('mt3-photos-open');
  if (!fields || !button) return;

  let perspectives = [];   // {type, id, name} from the latest Notes preview
  window.mt3PhotosPerspectives = function (list) {
    perspectives = Array.isArray(list) ? list : [];
  };

  function uploadUrl() {
    const q = new URLSearchParams({ for: 'moment' });
    if (button.dataset.momentId) q.set('moment', button.dataset.momentId);
    const part = perspectives.find(p => p.type === 'part');
    if (part) q.set('part', String(part.id));
    const workers = perspectives.filter(p => p.type === 'worker').map(p => String(p.id));
    if (workers.length) q.set('worker', workers.join(','));
    return BADMINT + '/mt3/?' + q.toString();
  }
  button.addEventListener('click', function () {
    window.open(uploadUrl(), 'mt3-photos', 'popup,width=560,height=900');
  });

  function inputs() {
    return Array.from(fields.querySelectorAll('input[name="image_urls[]"]'));
  }
  function addRow(photo) {
    if (inputs().some(i => i.value.trim() === photo.url)) return;   // Retry resends; add once
    const blank = inputs().find(i => i.value.trim() === '') || null;
    const img = document.createElement('img');
    img.src = photo.thumb;
    img.alt = photo.name || 'Image preview';
    const input = document.createElement('input');
    input.type = 'text';
    input.size = 130;
    input.name = 'image_urls[]';
    input.value = photo.url;
    [img, document.createElement('br'), input, document.createElement('br')]
      .forEach(el => fields.insertBefore(el, blank));
  }

  window.addEventListener('message', function (event) {
    if (event.origin !== BADMINT) return;
    const data = event.data;
    if (!data || data.type !== 'mt3-photos' || data.v !== 1 || !Array.isArray(data.photos)) return;
    data.photos.forEach(p => {
      if (p && typeof p.url === 'string' && p.url.startsWith('https://b.robnugen.com/')
          && typeof p.thumb === 'string') {
        addRow(p);
      }
    });
  });
})();
```

- [ ] **Step 4: Wire the template**

`moment.tpl.php`: replace

```php
        <label>
            Image URLs:<br>
```

with

```php
        <label>
            Image URLs:
            <button type="button" id="mt3-photos-open" data-moment-id="<?= (int) ($moment->moment_id ?? 0) ?: '' ?>">📷 Upload &amp; crop…</button>
            <br>
```

After `<script src="/admin/js/autocomplete.js" defer></script>` add `<script src="/admin/js/mt3-photos.js" defer></script>`.

In the preview handler, after `previewDiv.innerHTML = data.expanded_text || '';` add:

```js
            if (window.mt3PhotosPerspectives) window.mt3PhotosPerspectives(data.perspectives || []);
```

- [ ] **Step 5: Run the test, check the JS parses, lint, then the suite**

Run: `docker compose exec -T php vendor/bin/phpunit -c phpunit-docker.xml tests/Unit/Mt3PhotosListenerTest.php && node --check wwwroot/admin/js/mt3-photos.js && docker compose exec -T php php -l templates/admin/moments/moment.tpl.php`
Expected: OK (2 tests); node silent; no syntax errors.

- [ ] **Step 6: Commit**

```bash
git add wwwroot/admin/js/mt3-photos.js tests/Unit/Mt3PhotosListenerTest.php templates/admin/moments/moment.tpl.php
git commit -m "Take photos from badmint on the Moment form" -m "Create Moment has no ID until Save, so badmint cannot attach through the API; it posts each saved photo back and the form's own Save links it. The button passes the Part and Workers from the Notes so badmint files the photo under the right name. Only badmint's origin and its one message shape are accepted." -m "Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>" -m "Claude-Session: https://claude.ai/code/session_014FNHyAimU44oxxFiqPTRr3"
```

### Task 9: Tracks become attachable on badmint

**Files (repo `~/work/rob/badmint.robnugen.com`, branch `port-workers`, after the closed bubble; 1 commit, no bubble):**
- Modify: `classes/Mt3/Api.php:26,114-117`
- Modify: `Tests/Unit/Mt3ApiTest.php` (`testTracksAndUnknownKindsMakeNoRequest`, `testAttachUsesEachKindsPath`)
- Modify: `CLAUDE.md` and `templates/mt3/index.tpl.php` wherever "no Track photos" is said (grep `Track photos`).

- [ ] **Step 1: Make the tests expect Tracks to PATCH**

In `testAttachUsesEachKindsPath` add a call `$api->attach('track', 4, ['https://b.robnugen.com/x_1000.jpg'])` and assert `Api::BASE . '/tracks/4'`. In `testTracksAndUnknownKindsMakeNoRequest` drop the track assertion and rename it `testUnknownKindsMakeNoRequest`.

- [ ] **Step 2: Run to see it fail**

Run (in badmint): `docker run --rm --network none -v "$PWD":/app -w /app standards-codeception-runner:php8.3 php vendor/bin/codecept run Unit Mt3ApiTest`
Expected: 1 failure on the tracks path (attach returned 'MT3 has no Track photos yet', no call recorded).

- [ ] **Step 3: Implement**

`ATTACHABLE = ['part', 'track', 'worker', 'moment'];`, delete the `'MT3 has no Track photos yet'` branch and the docblock sentence "Tracks join once MT3 has tracks_2_photos"; fix the template/CLAUDE.md wording found by grep.

- [ ] **Step 4: Gates and commit**

Run: the Codeception file, then `~/work/rob/standards-mcp/run-phpcs.sh . && ~/work/rob/standards-mcp/run-phpstan.sh . && ~/work/rob/standards-mcp/run-unit.sh .`
Expected: all pass.

```bash
git add classes/Mt3/Api.php Tests/Unit/Mt3ApiTest.php CLAUDE.md templates/mt3/index.tpl.php
git commit -m "Attach Track photos now that MT3 takes them" -m "MT3 has tracks_2_photos and photo_urls on PATCH /api/v1/tracks/<id> (db.marbletrack3.com branch photos-from-badmint), so Tracks join the attachable kinds." -m "Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>" -m "Claude-Session: https://claude.ai/code/session_014FNHyAimU44oxxFiqPTRr3"
./deploy.sh
```

Deploy badmint only after MT3 is deployed (Task 11), or a Track attach would 200 on the old MT3 and silently drop the URL.

### Task 10: Document it

**Files:**
- Modify: `CLAUDE.md` (MT3), after "## Gotchas"

- [ ] **Step 1: Add the section**

```markdown
## Photos from badmint (mg #709)

Photos of Parts, Tracks and Workers are uploaded, straightened and cropped on
`https://badmint.robnugen.com/mt3/`, which files them on b.robnugen.com and attaches
the `_1000` URL here through the keyed API (`PATCH /api/v1/{parts,tracks,workers,moments}/<id>`
with `photo_urls`; append-only, deduped). The Part, Track and Worker edit pages link there
with `?part=`, `?track=`, `?worker=`. The Moment form opens it with `?for=moment` and the
Part and Workers from the Notes; `wwwroot/admin/js/mt3-photos.js` takes each saved photo back
by `postMessage` (origin `https://badmint.robnugen.com` only) as an Image URL row, and the
form's own Save links it. Nothing in MT3 posts to badmin's `bullet.php` any more
(`tests/Unit/NothingPostsToBadminTest.php`).
```

- [ ] **Step 2: Commit**

```bash
git add CLAUDE.md
git commit -m "Say where MT3 photos come from" -m "Co-Authored-By: Claude Fable 5.1 <noreply@anthropic.com>" -m "Claude-Session: https://claude.ai/code/session_014FNHyAimU44oxxFiqPTRr3"
```

### Task 11: Deploy and check in production

- [ ] **Step 1: Whole suite green**

Run: `docker compose exec -T php vendor/bin/phpunit -c phpunit-docker.xml 2>&1 | tail -3`
Expected: OK.

- [ ] **Step 2: Push and deploy the branch**

```bash
git push -u origin photos-from-badmint
ssh db_mt3_deploy photos-from-badmint
```

Expected: the deploy script reports the branch checked out.

- [ ] **Step 3: Create the table in production**

Over `ssh db.mt3`, read `dbHost`, `dbName`, `dbUser`, `dbPass` from `/home/dh_fbrdk3/db.marbletrack3.com/classes/Config.php` into shell variables and run `mysql -h "$H" -u "$U" -p"$P" "$D" < /home/dh_fbrdk3/db.marbletrack3.com/db_schemas/10_tracks/create_tracks_2_photos.sql`, then `SHOW TABLES LIKE 'tracks_2_photos'`. Nothing is printed but the table name; the credentials stay on the server.
Expected: `tracks_2_photos`.

- [ ] **Step 4: Live checks**

- `curl -s https://db.marbletrack3.com/api/v1/tracks/4/ -H "X-API-Key: …"` (from b.rn through badmint's key, as in the earlier read check): `photos: []` present.
- `https://db.marbletrack3.com/admin/tracks/track.php?id=4` renders with the Image URLs block and the badmint link (Playwright, MT3 login from `.env`).
- `https://db.marbletrack3.com/admin/parts/images/create.php?part_id=1` answers 302 to `https://badmint.robnugen.com/mt3/?part=1`.
- The Moment form renders the button and loads `/admin/js/mt3-photos.js` (200).
- Then badmint Task 9 deploy; on b.rn add `track:<id>` of Rob's chosen test Track to `mt3_test_targets`; a Track photo through `/mt3/` attaches and shows on the admin Track page. The Create Moment hand-off is Rob's phone/desktop check (badmint login is his).

- [ ] **Step 5: Close the bubble**

```bash
git close-bubble --dry-run
git close-bubble "DONE photos from badmint"
git push
ssh db_mt3_deploy photos-from-badmint
```
