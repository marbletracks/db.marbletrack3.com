<?php
namespace Database;

use Database\DbInterface;
use Physical\Part;
use Database\EDatabaseException;

class PartsRepository
{
    private DbInterface $db;
    private string $langCode;

    public function __construct(DbInterface $db, string $langCode)
    {
        $this->db = $db;
        $this->langCode = $langCode;
    }

    public function findById(int $part_id): ?Part
    {
        $results = $this->db->fetchResults(
            <<<SQL
SELECT p.part_id, p.part_alias, t.part_name, t.part_description
FROM parts p
LEFT JOIN part_translations t ON p.part_id = t.part_id AND t.language_code = ?
WHERE p.part_id = ?
SQL,
            'si',
            [$this->langCode, $part_id]
        );

        if ($results->numRows() === 0) {
            return null;
        }

        $results->setRow(0);
        return $this->hydrate($results->data);
    }

    public function findByAlias(string $alias): ?Part
    {
        $results = $this->db->fetchResults(
            <<<SQL
SELECT p.part_id, p.part_alias, t.part_name, t.part_description
FROM parts p
LEFT JOIN part_translations t ON p.part_id = t.part_id AND t.language_code = ?
WHERE p.part_alias = ?
SQL,
            'ss',
            [$this->langCode,
            $alias]
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
            <<<SQL
SELECT p.part_id, p.part_alias, t.part_name, t.part_description
FROM parts p
LEFT JOIN part_translations t ON p.part_id = t.part_id AND t.language_code = ?
ORDER BY p.part_id ASC
SQL,
            's',
            [$this->langCode]
        );

        $parts = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $parts[] = $this->hydrate($results->data);
        }

        return $parts;
    }

    public function insert(string $alias, string $name = '', string $description = ''): int
    {
        $part_id = $this->db->insertFromRecord(
            'parts',
            's',
            ['part_alias' => $alias]
        );

        if ($name || $description) {
            $this->db->insertFromRecord(
                'part_translations',
                'isss',
                [
                    'part_id' => $part_id,
                    'language_code' => $this->langCode,
                    'part_name' => $name,
                    'part_description' => $description
                ]
            );
        }

        return $part_id;
    }

    private function hydrate(array $row): Part
    {
        // print_rob("Hydrating Part with data: " . print_r($row, true), false);
        return new Part(
            part_id: (int) $row['part_id'],
            part_alias: $row['part_alias'],
            name: $row['part_name'] ?? '',
            description: $row['part_description'] ?? ''
        );
    }
}
