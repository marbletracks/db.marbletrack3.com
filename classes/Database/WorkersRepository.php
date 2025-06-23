<?php
namespace Database;

use Database\DbInterface;
use Domain\HasPhotos;
use Physical\Worker;

class WorkersRepository
{
    use HasPhotos;
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

    public function getDb(): DbInterface
    {
        return $this->db;
    }
    public function getPhotoLinkingTable(): string
    {
        return $this->photoLinkingTable;
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
        $worker = new Worker(
            worker_id: (int) $row['worker_id'],
            worker_alias: $row['worker_alias'],
            name: $row['worker_name'] ?? '',
            description: $row['worker_description'] ?? ''
        );
        $this->loadPhotos();  // defined in HasPhotos trait
        $worker->photos = $this->getPhotos(); // Get photos from HasPhotos trait
        return $worker;
    }
}
