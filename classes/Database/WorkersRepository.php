<?php
namespace Database;

use Database\DbInterface;
use Domain\HasPhotos;
use Domain\HasMoments;
use Domain\HasShortcodes;
use Physical\Worker;

class WorkersRepository
{
    use HasPhotos;
    use HasMoments;
    use HasShortcodes;
    private DbInterface $db;
    private string $langCode;

    private string $photoLinkingTable = 'workers_2_photos';
    private string $primaryKeyColumn = 'worker_id';
    private int $worker_id;  // Must be set when the Worker is loaded


    public function __construct(DbInterface $db, string $langCode)
    {
        $this->db = $db;
        $this->langCode = $langCode;
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
    public function getPhotoLinkingTable(): string
    {
        return $this->photoLinkingTable;
    }
    public function getMomentLinkingTable(): string
    {
        // This is deprecated for Workers, as we now use moment_translations as the source of truth.
        return '';
    }

    public function syncMomentsFromTranslations(int $worker_id, array $submitted_moment_ids): void
    {
        $moment_repo = new MomentRepository($this->getDb());

        // Get current moments based on existing translations for this worker
        $translations = $moment_repo->findTranslations($worker_id);
        $current_moment_ids = isset($translations['worker']) ? array_keys($translations['worker']) : [];

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

    public function getPrimaryKeyColumn(): string
    {
        return $this->primaryKeyColumn;
    }
    public function getId(): int
    {
        return $this->worker_id;
    }
    // must do this before saving photos
    // so that HasPhotos knows which worker to save photos for
    public function setWorkerId(int $worker_id): void
    {
        $this->worker_id = $worker_id;
    }
    public function findById(int $worker_id): ?Worker
    {
        $results = $this->db->fetchResults(
            <<<SQL
SELECT w.worker_id, w.worker_alias, n.worker_name, n.worker_description
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
        $this->worker_id = $worker_id; // Set the worker_id for HasPhotos

        $results->setRow(0);
        return $this->hydrate($results->data);
    }

    public function findByAlias(string $alias): ?Worker
    {
        $results = $this->db->fetchResults(
            <<<SQL
SELECT w.worker_id, w.worker_alias, n.worker_name, n.worker_description
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

        $results->setRow(0);
        $this->worker_id = (int)$results->data['worker_id']; // Set the worker_id for HasPhotos
        return $this->hydrate($results->data);
    }

    public function findAll(): array
    {
        $results = $this->db->fetchResults(
            sql: <<<SQL
SELECT w.worker_id,
       w.worker_alias,
       n.worker_name,
       n.worker_description
FROM workers w
JOIN worker_names n ON w.worker_id = n.worker_id AND n.language_code = ?
ORDER BY w.worker_id ASC
SQL,
            paramtypes: 's',
            var1: [$this->langCode]
        );

        $workers = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $this->worker_id = (int)$results->data['worker_id']; // Set the worker_id for HasPhotos
            $workers[] = $this->hydrate($results->data);
        }

        return $workers;
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
        if ($name || $description) {
            $this->db->executeSQL(
                "UPDATE worker_names SET worker_name = ?, worker_description = ? WHERE worker_id = ? AND language_code = ?",
                'ssis',
                [$name, $description, $worker_id, $this->langCode]
            );
        }
    }

    private function hydrate(array $row): Worker
    {
        $worker = new Worker(
            worker_id: (int) $row['worker_id'],
            worker_alias: $row['worker_alias'],
            name: $row['worker_name'] ?? '',
            description: $row['worker_description'] ?? ''
        );
        $this->loadPhotos();  // defined in HasPhotos trait
        $worker->photos = $this->getPhotos(); // Get photos from HasPhotos trait

        $this->loadMoments($worker);
        $worker->moments = $this->getMoments();
        return $worker;
    }
}
