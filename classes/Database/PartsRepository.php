<?php
namespace Database;

use Database\DbInterface;
use Physical\Part;

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
SELECT p.part_id,
       p.part_alias,
       p.is_rail,
       p.is_support,
       p.is_track,
       t.part_name,
       t.part_description
FROM parts p
-- don't LEFT JOIN because some are missing translations (e.g. outer spiral)
JOIN part_translations t ON p.part_id = t.part_id AND t.language_code = ?
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

    public function findPossParts(): array
    {
        $results = $this->db->fetchResults(
            <<<SQL
SELECT p.part_id, p.part_alias, t.part_name, t.part_description
FROM parts p
JOIN part_translations t ON p.part_id = t.part_id AND t.language_code = ?
WHERE p.part_alias REGEXP '^[0-9]{1,2}POSS$'
ORDER BY p.part_id DESC
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
            description: $row['part_description'] ?? '',
            is_rail: $row['is_rail'] ?? false,
            is_support: $row['is_support'] ?? false,
            is_track: $row['is_track'] ?? false,
        );
    }

    public function getImageUrls(int $part_id): array
    {
        $results = $this->db->fetchResults(
            "SELECT image_url FROM part_image_urls WHERE part_id = ?",
            'i',
            [$part_id]
        );

        $urls = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $urls[] = $results->data['image_url'];
        }

        return $urls;
    }

    public function updateImageUrls(int $part_id, array $urls): void
    {
        // Delete existing
        $this->db->executeSQL("DELETE FROM part_image_urls WHERE part_id = ?", 'i', [$part_id]);

        // Insert new
        foreach ($urls as $url) {
            $this->db->insertFromRecord('part_image_urls', 'is', [
                'part_id' => $part_id,
                'image_url' => $url
            ]);
        }
    }

}
