<?php
namespace Database;

use Database\DbInterface;
use Domain\HasPhotos;
use Domain\HasShortcodes;
use Physical\Part;

class PartsRepository
{
    use HasPhotos;
    use HasShortcodes;

    private DbInterface $db;
    private string $langCode;

    // Configuration for HasPhotos trait
    private string $photoLinkingTable = 'parts_2_photos';
    private string $primaryKeyColumn = 'part_id';
    private int $part_id;  // Must be set before loading/saving photos

    public function __construct(DbInterface $db, string $langCode)
    {
        $this->db = $db;
        $this->langCode = $langCode;
    }

    public function getSelectPrefix(): string
    {
        return <<<SQL
SELECT
    p.part_id AS id,
    p.part_alias AS alias,
    pt.part_name AS name
FROM parts p
LEFT JOIN part_translations pt
  ON p.part_id = pt.part_id
  AND pt.language_code = ?
SQL;
    }

    public function getTableAlias(): string
    {
        return 'p';
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
        return $this->part_id;
    }

    public function setPartId(int $part_id): void
    {
        $this->part_id = $part_id;
    }

    public function findById(int $part_id): ?Part
    {
        $results = $this->db->fetchResults(
            <<<SQL
SELECT p.part_id,
       p.part_alias,
       t.part_name,
       t.part_description,
       p.is_rail,
       p.is_support,
       p.is_track
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

        $this->setPartId($part_id);
        $results->setRow(0);

        return $this->hydrate($results->data);
    }

    public function findByAlias(string $alias): ?Part
    {
        $results = $this->db->fetchResults(
            <<<SQL
SELECT p.part_id,
       p.part_alias,
       t.part_name,
       t.part_description,
       p.is_rail,
       p.is_support,
       p.is_track
FROM parts p
LEFT JOIN part_translations t ON p.part_id = t.part_id AND t.language_code = ?
WHERE p.part_alias = ?
SQL,
            'ss',
            [$this->langCode, $alias]
        );

        if ($results->numRows() === 0) {
            return null;
        }

        $results->setRow(0);
        $part_id = (int) $results->data['part_id'];
        $this->setPartId($part_id);

        return $this->hydrate($results->data);
    }

    public function findAll(): array
    {
        $results = $this->db->fetchResults(
            sql: <<<SQL
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
            paramtypes: 's',
            var1: [$this->langCode]
        );

        $parts = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $this->setPartId((int) $results->data['part_id']);
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
            $this->setPartId(part_id: (int) $results->data['part_id']);
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
        $part = new Part(
            part_id: (int) $row['part_id'],
            part_alias: $row['part_alias'],
            name: $row['part_name'] ?? '',
            description: $row['part_description'] ?? '',
            is_rail: (bool) ($row['is_rail'] ?? false),
            is_support: (bool) ($row['is_support'] ?? false),
            is_track: (bool) ($row['is_track'] ?? false)
        );

        // Load and attach photos via HasPhotos trait
        $this->loadPhotos();
        $part->photos = $this->getPhotos();

        return $part;
    }
}
