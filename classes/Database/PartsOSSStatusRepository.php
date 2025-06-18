<?php

namespace Database;

use Database\DbInterface;
use Physical\PartsOSSStatus;

class PartsOSSStatusRepository
{
    private DbInterface $db;

    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    public function findAll(): array
    {
        $results = $this->db->fetchResults(
            <<<SQL
SELECT * FROM parts_oss_status ORDER BY ssop_mm ASC
SQL
        );

        $statuses = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $statuses[] = $this->hydrate($results->data);
        }

        return $statuses;
    }

    public function findById(int $status_id): ?PartsOSSStatus
    {
        $results = $this->db->fetchResults(
            <<<SQL
SELECT * FROM parts_oss_status WHERE parts_oss_status_id = ?
SQL,
            'i',
            [$status_id]
        );

        if ($results->numRows() === 0) {
            return null;
        }

        $results->setRow(0);
        return $this->hydrate($results->data);
    }

    public function insertOrUpdate(?int $parts_oss_status_id, int $part_id, string $ssop_label, float $ssop_mm, float $height_orig, float $height_best, ?float $height_now = null): int
    {
        if ($parts_oss_status_id !== null) {
            $this->db->executeSQL(
                "UPDATE parts_oss_status SET part_id = ?, ssop_label = ?, ssop_mm = ?, height_orig = ?, height_best = ?, height_now = ?, last_updated = CURRENT_TIMESTAMP WHERE parts_oss_status_id = ?",
                'isddddi',
                [
                    $part_id,
                    $ssop_label,
                    $ssop_mm,
                    $height_orig,
                    $height_best,
                    $height_now,
                    $parts_oss_status_id,
                ]
            );
            return $parts_oss_status_id;
        } else {
            return $this->db->insertFromRecord(
                'parts_oss_status',
                'dsdddd',
                [
                    'part_id' => $part_id,
                    'ssop_label' => $ssop_label,
                    'ssop_mm' => $ssop_mm,
                    'height_orig' => $height_orig,
                    'height_best' => $height_best,
                    'height_now' => $height_now,
                ]
            );
        }
    }


    private function hydrate(array $row): PartsOSSStatus
    {
        return new PartsOSSStatus(
            parts_oss_status_id: (int) $row['parts_oss_status_id'],
            part_id: (int) $row['part_id'],
            ssop_label: $row['ssop_label'],
            ssop_mm: (float) $row['ssop_mm'],
            height_orig: (float) $row['height_orig'],
            height_best: (float) $row['height_best'],
            height_now: isset($row['height_now']) ? (float) $row['height_now'] : null,
            last_updated: $row['last_updated']
        );
    }
}
