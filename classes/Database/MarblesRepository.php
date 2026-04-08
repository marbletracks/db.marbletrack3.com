<?php
namespace Database;

use Database\DbInterface;
use Domain\HasPhotos;
use Domain\HasShortcodes;
use Domain\HasMoments;
use Physical\Marble;

class MarblesRepository
{
    use HasPhotos;
    use HasShortcodes;
    use HasMoments;

    private DbInterface $db;
    private string $photoLinkingTable = 'marbles_2_photos';
    private string $primaryKeyColumn = 'marble_id';
    private int $marble_id;

    public function __construct(DbInterface $db, string $langCode = 'en')
    {
        $this->db = $db;
    }

    // ── HasShortcodes trait methods ───────────────────────────────────────────

    public function getSELECTExactAlias(): string
    {
        // First param (?) is lang code from HasShortcodes trait — unused but must accept it
        return <<<SQL
SELECT
    m.marble_id AS id,
    m.marble_alias AS alias,
    m.slug,
    m.marble_name AS name
FROM marbles m
WHERE ? IS NOT NULL
  AND (m.marble_alias = ?
  OR m.marble_name = ?)
LIMIT ?
SQL;
    }

    public function getSELECTLikeAlias(): string
    {
        // First param (?) is lang code from HasShortcodes trait — unused but must accept it
        return <<<SQL
SELECT
    m.marble_id AS id,
    m.marble_alias AS alias,
    m.slug,
    m.marble_name AS name
FROM marbles m
WHERE ? IS NOT NULL
  AND (m.marble_alias LIKE ?
  OR m.marble_name LIKE ?)
LIMIT ?
SQL;
    }

    public function getSELECTForShortcodeExpansion(string $langCode): string
    {
        return <<<SQL
SELECT
    m.marble_id AS id,
    m.marble_alias AS alias,
    m.slug,
    m.marble_name AS name
FROM marbles m
SQL;
    }

    public function getAliasType(): string
    {
        return 'marble';
    }

    public function getTableAlias(): string
    {
        return 'm';
    }

    public function getPrimaryKeyColumn(): string
    {
        return $this->primaryKeyColumn;
    }

    public function getId(): int
    {
        return $this->marble_id;
    }

    public function getPhotoLinkingTable(): string
    {
        return $this->photoLinkingTable;
    }

    public function setMarbleId(int $marble_id): void
    {
        $this->marble_id = $marble_id;
    }

    public function getMomentLinkingTable(): string
    {
        return '';
    }

    public function getDb(): DbInterface
    {
        return $this->db;
    }

    // ── CRUD ─────────────────────────────────────────────────────────────────

    public function findAll(): array
    {
        $results = $this->db->fetchResults(
            "SELECT marble_id, marble_alias, marble_name, slug, team_name, size, color, quantity, description
             FROM marbles
             ORDER BY size ASC, marble_name ASC"
        );

        $marbles = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $marbles[] = $this->hydrate($results->data);
        }

        return $marbles;
    }

    public function findById(int $marble_id): ?Marble
    {
        $results = $this->db->fetchResults(
            "SELECT marble_id, marble_alias, marble_name, slug, team_name, size, color, quantity, description
             FROM marbles WHERE marble_id = ?",
            'i',
            [$marble_id]
        );

        if ($results->numRows() === 0) {
            return null;
        }

        $results->setRow(0);
        return $this->hydrate($results->data);
    }

    public function findByAlias(string $alias): ?Marble
    {
        $results = $this->db->fetchResults(
            "SELECT marble_id, marble_alias, marble_name, slug, team_name, size, color, quantity, description
             FROM marbles WHERE marble_alias = ?",
            's',
            [$alias]
        );

        if ($results->numRows() === 0) {
            return null;
        }

        $results->setRow(0);
        return $this->hydrate($results->data);
    }

    public function insert(string $alias, string $name, string $size, string $color, int $quantity = 1, ?string $team_name = null, ?string $description = null): int
    {
        $slug = \Utilities::slugify($name, 200);

        return $this->db->insertFromRecord(
            'marbles',
            'ssssssis',
            [
                'marble_alias' => $alias,
                'marble_name' => $name,
                'slug' => $slug,
                'team_name' => $team_name,
                'size' => $size,
                'color' => $color,
                'quantity' => $quantity,
                'description' => $description,
            ]
        );
    }

    public function update(int $marble_id, string $alias, string $name, string $size, string $color, int $quantity = 1, ?string $team_name = null, ?string $description = null): void
    {
        $slug = \Utilities::slugify($name, 200);

        $this->db->executeSQL(
            "UPDATE marbles SET marble_alias = ?, marble_name = ?, slug = ?, team_name = ?, size = ?, color = ?, quantity = ?, description = ? WHERE marble_id = ?",
            'ssssssisi',
            [$alias, $name, $slug, $team_name, $size, $color, $quantity, $description, $marble_id]
        );
    }

    private function hydrate(array $row): Marble
    {
        $marble = new Marble(
            marble_id: (int) $row['marble_id'],
            marble_alias: $row['marble_alias'],
            marble_name: $row['marble_name'],
            team_name: $row['team_name'] ?? null,
            size: $row['size'],
            color: $row['color'],
            quantity: (int) $row['quantity'],
            description: $row['description'] ?? null,
        );

        $this->marble_id = $marble->marble_id;
        $this->loadMoments($marble);
        $marble->moments = $this->getMoments();

        $this->loadPhotos();
        $marble->photos = $this->getPhotos();

        return $marble;
    }
}
