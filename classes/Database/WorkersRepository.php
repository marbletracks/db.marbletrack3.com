<?php
namespace Database;

use Database\DbInterface;
use Domain\HasShortcodes;
use Physical\Worker;

class WorkersRepository
{
    use HasShortcodes;
    private DbInterface $db;
    private string $langCode;

    private PhotoLinks $photoLinks;
    private PerspectiveMoments $perspectiveMoments;


    public function __construct(DbInterface $db, string $langCode)
    {
        $this->db = $db;
        $this->langCode = $langCode;
        $this->photoLinks = new PhotoLinks($db, 'workers', 'workers_2_photos', 'worker_id');
        $this->perspectiveMoments = new PerspectiveMoments($db);
    }

    public function getSELECTExactAlias(): string
    {
        return <<<SQL
SELECT
    w.worker_id AS id,
    w.worker_alias AS alias,
    w.slug,
    wn.worker_name AS name
FROM workers w
LEFT JOIN worker_names wn
  ON w.worker_id = wn.worker_id
  AND wn.language_code = ?
WHERE w.worker_alias = ?
  OR wn.worker_name = ?
LIMIT ?
SQL;
    }

    public function getSELECTLikeAlias(): string
    {
        return <<<SQL
SELECT
    w.worker_id AS id,
    w.worker_alias AS alias,
    w.slug,
    wn.worker_name AS name
FROM workers w
LEFT JOIN worker_names wn
  ON w.worker_id = wn.worker_id
  AND wn.language_code = ?
WHERE w.worker_alias LIKE ?
  OR wn.worker_name LIKE ?
LIMIT ?
SQL;
    }

    public function getSELECTForShortcodeExpansion(string $langCode): string
    {
        return <<<SQL
SELECT
    w.worker_id AS id,
    w.worker_alias AS alias,
    w.slug,
    wn.worker_name AS name
FROM workers w
LEFT JOIN worker_names wn
  ON w.worker_id = wn.worker_id
  AND wn.language_code = "$langCode"
SQL;
    }

    /**
     * written where we need [worker:alias]
     * @return string
     */
    public function getAliasType(): string
    {
        return 'worker';
    }


    public function getTableAlias(): string
    {
        return 'w';
    }
    public function getDb(): DbInterface
    {
        return $this->db;
    }
    public function syncMomentsFromTranslations(int $worker_id, array $submitted_moment_ids): void
    {
        $moment_repo = new MomentRepository($this->getDb());

        // Get current moments based on existing translations for this worker
        $current_moment_ids = $moment_repo->findMomentIdsByEntity($worker_id, 'worker');

        // Cast submitted IDs to integers
        $submitted_moment_ids = array_map('intval', $submitted_moment_ids);

        // Find moments to add (present in submitted, but not in current)
        $moments_to_add = array_diff($submitted_moment_ids, $current_moment_ids);
        foreach ($moments_to_add as $moment_id) {
            $moment_repo->createTranslationIfNotExists($moment_id, $worker_id, 'worker');
        }

        // Find moments to remove (present in current, but not in submitted)
        $moments_to_remove = array_diff($current_moment_ids, $submitted_moment_ids);
        foreach ($moments_to_remove as $moment_id) {
            $moment_repo->deleteTranslation($moment_id, $worker_id, 'worker');
        }
    }

    public function findById(int $worker_id): ?Worker
    {
        $results = $this->db->fetchResults(
            <<<SQL
SELECT w.worker_id, w.worker_alias, w.busy_sort, n.worker_name, n.worker_description
FROM workers w
LEFT JOIN worker_names n ON w.worker_id = n.worker_id AND n.language_code = ?
WHERE w.worker_id = ?
SQL,
            'si',
            [$this->langCode, $worker_id]
        );

        if ($results->numRows() === 0) {
            return null;
        }
        return $this->hydrateAll($results->toArray())[0];
    }

    public function findByAlias(string $alias): ?Worker
    {
        $results = $this->db->fetchResults(
            <<<SQL
SELECT w.worker_id, w.worker_alias, w.busy_sort, n.worker_name, n.worker_description
FROM workers w
LEFT JOIN worker_names n ON w.worker_id = n.worker_id AND n.language_code = ?
WHERE w.worker_alias = ?
SQL,
            'ss',
            [$this->langCode, $alias]
        );

        if ($results->numRows() === 0) {
            return null;
        }

        return $this->hydrateAll($results->toArray())[0];
    }

    public function findAll(): array
    {
        $results = $this->db->fetchResults(
            sql: <<<SQL
SELECT w.worker_id,
       w.worker_alias,
       w.busy_sort,
       n.worker_name,
       n.worker_description
FROM workers w
LEFT JOIN worker_names n ON w.worker_id = n.worker_id AND n.language_code = ?
ORDER BY w.busy_sort ASC, w.worker_id ASC
SQL,
            paramtypes: 's',
            var1: [$this->langCode]
        );

        return $this->hydrateAll($results->toArray());
    }

    public function insert(string $alias, string $name = '', string $description = ''): int
    {
        // Generate slug from name if provided
        $slug = null;
        if (!empty($name)) {
            $slug = \Utilities::slugify($name, 20);
        }

        $insertData = ['worker_alias' => $alias];
        $paramTypes = 's';

        if ($slug !== null) {
            $insertData['slug'] = $slug;
            $paramTypes = 'ss';
        }

        $worker_id = $this->db->insertFromRecord(
            'workers',
            $paramTypes,
            $insertData
        );

        if ($name || $description) {
            $this->db->insertFromRecord(
                'worker_names',
                'isss',
                [
                    'worker_id' => $worker_id,
                    'language_code' => $this->langCode,
                    'worker_name' => $name,
                    'worker_description' => $description
                ]
            );
        }

        return $worker_id;
    }

    public function update(int $worker_id, string $alias, string $name = '', string $description = ''): void
    {
        // Generate slug from name if provided
        $slug = null;
        if (!empty($name)) {
            $slug = \Utilities::slugify($name, 20);
        }

        // Update workers table
        if ($slug !== null) {
            $this->db->executeSQL(
                "UPDATE workers SET worker_alias = ?, slug = ? WHERE worker_id = ?",
                'ssi',
                [$alias, $slug, $worker_id]
            );
        } else {
            $this->db->executeSQL(
                "UPDATE workers SET worker_alias = ? WHERE worker_id = ?",
                'si',
                [$alias, $worker_id]
            );
        }

        // Update translations
        if ($name && $description) {
            $this->db->executeSQL(
                "UPDATE worker_names SET worker_name = ?, worker_description = ? WHERE worker_id = ? AND language_code = ?",
                'ssis',
                [$name, $description, $worker_id, $this->langCode]
            );
        } elseif ($name) {
            $this->db->executeSQL(
                "UPDATE worker_names SET worker_name = ? WHERE worker_id = ? AND language_code = ?",
                'sis',
                [$name, $worker_id, $this->langCode]
            );
        } elseif ($description) {
            $this->db->executeSQL(
                "UPDATE worker_names SET worker_description = ? WHERE worker_id = ? AND language_code = ?",
                'sis',
                [$description, $worker_id, $this->langCode]
            );
        }
    }

    /**
     * Get photos that are in workers_2_photos but NOT yet used in moments_2_photos
     * @param int $worker_id
     * @return \Media\Photo[]
     */
    public function getUnusedPhotos(int $worker_id): array
    {
        $results = $this->db->fetchResults(
            <<<SQL
SELECT DISTINCT p.photo_id, p.code, p.url
FROM workers_2_photos w2p
JOIN photos p ON w2p.photo_id = p.photo_id
WHERE w2p.worker_id = ?
  AND w2p.photo_id NOT IN (
    SELECT photo_id FROM moments_2_photos
  )
ORDER BY w2p.photo_sort ASC, p.photo_id ASC
SQL,
            'i',
            [$worker_id]
        );

        $photos = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $data = $results->data;
            $photos[] = new \Media\Photo(
                photo_id: (int) $data['photo_id'],
                code: $data['code'],
                url: $data['url']
            );
        }

        return $photos;
    }

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
}
