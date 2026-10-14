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
