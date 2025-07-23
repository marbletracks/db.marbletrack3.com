<?php
namespace Database;

use Database\DbInterface;
use Domain\HasPhotos;
use Domain\HasMoments;
use Domain\HasShortcodes;
use Physical\Part;

class PartsRepository
{
    use HasPhotos;
    use HasMoments;
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

    public function getSELECTExactAlias(): string
    {
        return <<<SQL
SELECT
    p.part_id AS id,
    p.part_alias AS alias,
    p.slug,
    pt.part_name AS name
FROM parts p
LEFT JOIN part_translations pt
  ON p.part_id = pt.part_id
  AND pt.language_code = ?
WHERE p.part_alias = ?
  OR pt.part_name = ?
LIMIT ?
SQL;
    }

    public function getSELECTLikeAlias(): string
    {
        return <<<SQL
SELECT
    p.part_id AS id,
    p.part_alias AS alias,
    p.slug,
    pt.part_name AS name
FROM parts p
LEFT JOIN part_translations pt
  ON p.part_id = pt.part_id
  AND pt.language_code = ?
WHERE p.part_alias LIKE ?
  OR pt.part_name LIKE ?
LIMIT ?
SQL;
    }

    public function getSELECTForShortcodeExpansion(string $langCode): string
    {
        return <<<SQL
SELECT
    p.part_id AS id,
    p.part_alias AS alias,
    p.slug,
    pt.part_name AS name
FROM parts p
LEFT JOIN part_translations pt
  ON p.part_id = pt.part_id
  AND pt.language_code = "$langCode"
SQL;
    }

    /**
     * written where we need [part:alias]
     * @return string
     */
    public function getAliasType(): string
    {
        return 'part';
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

    public function getMomentLinkingTable(): string
    {
        // This is deprecated for Parts, as we now use moment_translations as the source of truth.
        return '';
    }

    /**
     * Used by wwwroot/admin/parts/part.php to save Moments with Parts.
     * Created by Gemini, based on Workers (created by Gemini)
     * @param int $part_id
     * @param array $submitted_moment_ids
     * @return void
     */
    public function syncMomentsFromTranslations(int $part_id, array $submitted_moment_ids): void
    {
        $moment_repo = new MomentRepository($this->getDb());

        // Get current moments based on existing translations for this part
        $current_moment_ids = $moment_repo->findMomentIdsByEntity($part_id, 'part');

        // Cast submitted IDs to integers
        $submitted_moment_ids = array_map('intval', $submitted_moment_ids);

        // Find moments to add (present in submitted, but not in current)
        $moments_to_add = array_diff($submitted_moment_ids, $current_moment_ids);
        foreach ($moments_to_add as $moment_id) {
            $moment_repo->createTranslationIfNotExists($moment_id, $part_id, 'part');
        }

        // Find moments to remove (present in current, but not in submitted)
        $moments_to_remove = array_diff($current_moment_ids, $submitted_moment_ids);
        foreach ($moments_to_remove as $moment_id) {
            $moment_repo->deleteTranslation($moment_id, $part_id, 'part');
        }
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

        $results->setRow(0);
        $this->setPartId($part_id);

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

    public function findBySlug(string $slug): ?Part
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
WHERE p.slug = ?
SQL,
            'ss',
            [$this->langCode, $slug]
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

    public function findByFilter(string $filter): array
    {
        $filter = trim($filter);
        if (empty($filter)) {
            return $this->findAll();
        }

        // Priority search:
        // 1. Exact match of alias
        // 2. LIKE "alias%" (starts with)
        // 3. Exact match of name
        // 4. LIKE "%name%" (contains)
        
        $results = $this->db->fetchResults(
            sql: <<<SQL
SELECT p.part_id,
       p.part_alias,
       p.is_rail,
       p.is_support,
       p.is_track,
       t.part_name,
       t.part_description,
       CASE 
           WHEN p.part_alias = ? THEN 1
           WHEN p.part_alias LIKE ? THEN 2
           WHEN t.part_name = ? THEN 3
           WHEN t.part_name LIKE ? THEN 4
           ELSE 5
       END as priority
FROM parts p
JOIN part_translations t ON p.part_id = t.part_id AND t.language_code = ?
WHERE p.part_alias = ?
   OR p.part_alias LIKE ?
   OR t.part_name = ?
   OR t.part_name LIKE ?
ORDER BY priority ASC, p.part_id ASC
SQL,
            paramtypes: 'sssssssss',
            var1: [
                $filter,           // exact alias match (case)
                $filter . '%',     // alias starts with (case)
                $filter,           // exact name match (case)
                '%' . $filter . '%',  // name contains (case)
                $this->langCode,   // language code
                $filter,           // exact alias match (where)
                $filter . '%',     // alias starts with (where)
                $filter,           // exact name match (where)
                '%' . $filter . '%'   // name contains (where)
            ]
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
WHERE p.part_alias REGEXP '^[0-9]{1,2}POSS'
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
        // Generate slug from name if provided
        $slug = null;
        if (!empty($name)) {
            $slug = \Utilities::slugify($name, 200);
        }

        $insertData = ['part_alias' => $alias];
        $paramTypes = 's';

        if ($slug !== null) {
            $insertData['slug'] = $slug;
            $paramTypes = 'ss';
        }

        $part_id = $this->db->insertFromRecord(
            'parts',
            $paramTypes,
            $insertData
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

    public function update(int $part_id, string $alias, string $name = '', string $description = ''): void
    {
        // Generate slug from name if provided
        $slug = null;
        if (!empty($name)) {
            $slug = \Utilities::slugify($name, 200);
        }

        // Update parts table
        if ($slug !== null) {
            $this->db->executeSQL(
                "UPDATE parts SET part_alias = ?, slug = ? WHERE part_id = ?",
                'ssi',
                [$alias, $slug, $part_id]
            );
        } else {
            $this->db->executeSQL(
                "UPDATE parts SET part_alias = ? WHERE part_id = ?",
                'si',
                [$alias, $part_id]
            );
        }

        // Update translations
        if ($name || $description) {
            $this->db->executeSQL(
                "REPLACE INTO part_translations (
                    part_id,
                    language_code,
                    part_name,
                    part_description
                ) VALUES (?, ?, ?, ?)",
                'isss',
                [$part_id, $this->langCode, $name, $description]
            );
        }
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

        // Load and attach moments via HasMoments trait
        $this->loadMoments($part);
        $part->moments = $this->getMoments();

        if($part->name == $part->description) {
            // quickly get the source of the data.  Not needed once all Parts on this site have detailed info
            $part->frontend_link = "https://www.marbletrack3.com/p/{$part->part_alias}";
        } else {
            // We have data on this site so stay on this site.
            $part->frontend_link = "/parts/" . \Utilities::slugify($part->name, 200);
        }

        return $part;
    }
}
