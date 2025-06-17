<?php
namespace Database;

use Database\DbInterface;
use Physical\Worker;

class WorkersRepository
{
    private DbInterface $db;
    private string $langCode;

    public function __construct(DbInterface $db, string $langCode)
    {
        $this->db = $db;
        $this->langCode = $langCode;
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
        return $this->hydrate($results->data);
    }

    public function findAll(): array
    {
        $results = $this->db->fetchResults(
            sql: <<<SQL
SELECT w.worker_id, w.worker_alias, n.worker_name, n.worker_description
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
            $workers[] = $this->hydrate($results->data);
        }

        return $workers;
    }

    public function getPhotosForWorker(int $worker_id, bool $primaryOnly = false): array
    {
        $sql = <<<SQL
    SELECT photo_id, photo_code, friendly_name, caption, is_primary, created_at
    FROM workers_photos
    WHERE worker_id = ?
    SQL;

        if ($primaryOnly) {
            $sql .= " AND is_primary = 1";
        }

        $sql .= " ORDER BY is_primary DESC, created_at ASC";

        $results = $this->db->fetchResults($sql, 'i', [$worker_id]);

        $photos = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $photos[] = [
                'photo_id' => (int)$results->data['photo_id'],
                'photo_code' => $results->data['photo_code'],
                'friendly_name' => $results->data['friendly_name'],
                'caption' => $results->data['caption'],
                'is_primary' => (bool)$results->data['is_primary'],
                'created_at' => $results->data['created_at'],
            ];
        }
        return $photos;
    }

    public function insert(string $alias, string $name = '', string $description = ''): int
    {
        $worker_id = $this->db->insertFromRecord(
            'workers',
            's',
            ['worker_alias' => $alias]
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

    private function hydrate(array $row): Worker
    {
        return new Worker(
            worker_id: (int) $row['worker_id'],
            worker_alias: $row['worker_alias'],
            name: $row['worker_name'] ?? '',
            description: $row['worker_description'] ?? ''
        );
    }
}
