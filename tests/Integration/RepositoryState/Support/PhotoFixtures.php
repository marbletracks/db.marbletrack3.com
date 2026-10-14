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
                // WorkersRepository::insert() omits busy_sort, which has no default in the strict test schema.
                $id = $db->insertFromRecord(
                    'workers',
                    'ssi',
                    ['worker_alias' => "{$p}w{$n}", 'slug' => "{$p}w{$n}", 'busy_sort' => 0]
                );
                $db->insertFromRecord(
                    'worker_names',
                    'isss',
                    ['worker_id' => $id, 'language_code' => 'en', 'worker_name' => "{$p}Worker {$n}", 'worker_description' => 'fixture']
                );
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
