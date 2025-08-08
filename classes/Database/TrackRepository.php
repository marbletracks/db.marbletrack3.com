<?php
namespace Database;

use Database\DbInterface;
use Physical\Track;
use Physical\Part;

class TrackRepository
{
    private DbInterface $db;

    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    public function findById(int $track_id): ?Track
    {
        $results = $this->db->fetchResults(
            <<<SQL
SELECT t.track_id,
       t.track_alias,
       t.track_name,
       t.track_description,
       t.marble_sizes_accepted,
       t.is_transport,
       t.is_splitter,
       t.is_landing_zone,
       t.entity_type,
       t.entity_type
FROM tracks t
WHERE t.track_id = ?
SQL,
            'i',
            [$track_id]
        );

        if ($results->numRows() === 0) {
            return null;
        }

        $results->setRow(0);
        return $this->hydrate($results->data);
    }

    public function findByAlias(string $alias): ?Track
    {
        $results = $this->db->fetchResults(
            <<<SQL
SELECT t.track_id,
       t.track_alias,
       t.track_name,
       t.track_description,
       t.marble_sizes_accepted,
       t.is_transport,
       t.is_splitter,
       t.is_landing_zone,
       t.entity_type
FROM tracks t
WHERE t.track_alias = ?
SQL,
            's',
            [$alias]
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
SELECT t.track_id,
       t.track_alias,
       t.track_name,
       t.track_description,
       t.marble_sizes_accepted,
       t.is_transport,
       t.is_splitter,
       t.is_landing_zone,
       t.entity_type
FROM tracks t
ORDER BY
    t.is_landing_zone DESC,  -- Landing zones first
    t.track_name ASC
SQL
        );

        $tracks = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $tracks[] = $this->hydrate($results->data);
        }
        return $tracks;
    }

    public function findLandingZones(): array
    {
        $results = $this->db->fetchResults(
            <<<SQL
SELECT t.track_id,
       t.track_alias,
       t.track_name,
       t.track_description,
       t.marble_sizes_accepted,
       t.is_transport,
       t.is_splitter,
       t.is_landing_zone,
       t.entity_type
FROM tracks t
WHERE t.is_landing_zone = TRUE
ORDER BY t.track_name ASC
SQL
        );

        $tracks = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $tracks[] = $this->hydrate($results->data);
        }
        return $tracks;
    }

    public function findByEntityType(string $entity_type): array
    {
        $results = $this->db->fetchResults(
            <<<SQL
SELECT t.track_id,
       t.track_alias,
       t.track_name,
       t.track_description,
       t.marble_sizes_accepted,
       t.is_transport,
       t.is_splitter,
       t.is_landing_zone,
       t.entity_type
FROM tracks t
WHERE t.entity_type = ?
ORDER BY t.track_name ASC
SQL,
            's',
            [$entity_type]
        );

        $tracks = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $tracks[] = $this->hydrate($results->data);
        }
        return $tracks;
    }

    public function findMarbleTracks(): array
    {
        $results = $this->db->fetchResults(
            <<<SQL
SELECT t.track_id,
       t.track_alias,
       t.track_name,
       t.track_description,
       t.marble_sizes_accepted,
       t.is_transport,
       t.is_splitter,
       t.is_landing_zone,
       t.entity_type
FROM tracks t
WHERE t.entity_type IN ('marble', 'mixed')
ORDER BY t.track_name ASC
SQL
        );

        $tracks = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $tracks[] = $this->hydrate($results->data);
        }
        return $tracks;
    }

    public function findWorkerTracks(): array
    {
        $results = $this->db->fetchResults(
            <<<SQL
SELECT t.track_id,
       t.track_alias,
       t.track_name,
       t.track_description,
       t.marble_sizes_accepted,
       t.is_transport,
       t.is_splitter,
       t.is_landing_zone,
       t.entity_type
FROM tracks t
WHERE t.entity_type IN ('worker', 'mixed')
ORDER BY t.track_name ASC
SQL
        );

        $tracks = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $tracks[] = $this->hydrate($results->data);
        }
        return $tracks;
    }

    public function findUpstreamTracks(int $track_id): array
    {
        $results = $this->db->fetchResults(
            <<<SQL
SELECT t.track_id,
       t.track_alias,
       t.track_name,
       t.track_description,
       t.marble_sizes_accepted,
       t.is_transport,
       t.is_splitter,
       t.is_landing_zone,
       t.entity_type,
       tc.marble_sizes as connection_marble_sizes,
       tc.connection_type,
       tc.connection_description
FROM tracks t
JOIN track_connections tc ON t.track_id = tc.from_track_id
WHERE tc.to_track_id = ?
ORDER BY t.track_name ASC
SQL,
            'i',
            [$track_id]
        );

        $tracks = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $tracks[] = $this->hydrate($results->data);
        }
        return $tracks;
    }

    public function findDownstreamTracks(int $track_id): array
    {
        $results = $this->db->fetchResults(
            <<<SQL
SELECT t.track_id,
       t.track_alias,
       t.track_name,
       t.track_description,
       t.marble_sizes_accepted,
       t.is_transport,
       t.is_splitter,
       t.is_landing_zone,
       t.entity_type,
       tc.marble_sizes as connection_marble_sizes,
       tc.connection_type,
       tc.connection_description
FROM tracks t
JOIN track_connections tc ON t.track_id = tc.to_track_id
WHERE tc.from_track_id = ?
ORDER BY t.track_name ASC
SQL,
            'i',
            [$track_id]
        );

        $tracks = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $tracks[] = $this->hydrate($results->data);
        }
        return $tracks;
    }

    public function findPartsByTrackId(int $track_id, string $langCode = 'en'): array
    {
        $results = $this->db->fetchResults(
            <<<SQL
SELECT p.part_id,
       p.part_alias,
       pt.part_name,
       pt.part_description,
       p.is_rail,
       p.is_support,
       p.is_track,
       tp.part_role,
       tp.is_exclusive_to_track
FROM parts p
JOIN track_parts tp ON p.part_id = tp.part_id
LEFT JOIN part_translations pt ON p.part_id = pt.part_id AND pt.language_code = ?
WHERE tp.track_id = ?
ORDER BY
    CASE tp.part_role
        WHEN 'main' THEN 1
        WHEN 'connector' THEN 2
        WHEN 'guide' THEN 3
        WHEN 'support' THEN 4
        ELSE 5
    END,
    pt.part_name ASC
SQL,
            'si',
            [$langCode, $track_id]
        );

        $partsRepo = new PartsRepository($this->db, $langCode);
        $parts = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $part = new Part(
                part_id: $results->data['part_id'],
                part_alias: $results->data['part_alias'],
                name: $results->data['part_name'] ?? '',
                description: $results->data['part_description'] ?? '',
                is_rail: $results->data['is_rail'],
                is_support: $results->data['is_support'],
                is_track: $results->data['is_track']
            );
            $part->role_in_track = $results->data['part_role'];
            $part->is_exclusive_to_track = (bool) $results->data['is_exclusive_to_track'];
            $parts[] = $part;
        }
        return $parts;
    }

    private function hydrate(array $data): Track
    {
        // Convert SET field to array
        $marble_sizes = !empty($data['marble_sizes_accepted'])
            ? explode(',', $data['marble_sizes_accepted'])
            : [];

        return new Track(
            track_id: $data['track_id'],
            track_alias: $data['track_alias'],
            track_name: $data['track_name'] ?? '',
            track_description: $data['track_description'] ?? '',
            marble_sizes_accepted: $marble_sizes,
            is_transport: (bool) $data['is_transport'],
            is_splitter: (bool) $data['is_splitter'],
            is_landing_zone: (bool) $data['is_landing_zone'],
            entity_type: $data['entity_type'] ?? 'marble'
        );
    }

    public function insert(string $alias, string $name, string $description, array $marble_sizes, bool $is_transport, bool $is_splitter, bool $is_landing_zone, string $entity_type = 'marble'): int
    {
        $marble_sizes_str = implode(',', $marble_sizes);

        return $this->db->insertFromRecord(
            'tracks',
            'ssssiiis',
            [
                'track_alias' => $alias,
                'track_name' => $name,
                'track_description' => $description,
                'marble_sizes_accepted' => $marble_sizes_str,
                'is_transport' => $is_transport,
                'is_splitter' => $is_splitter,
                'is_landing_zone' => $is_landing_zone,
                'entity_type' => $entity_type
            ]
        );
    }

    public function update(int $track_id, string $alias, string $name, string $description, array $marble_sizes, bool $is_transport, bool $is_splitter, bool $is_landing_zone, string $entity_type = 'marble'): void
    {
        $marble_sizes_str = implode(',', $marble_sizes);

        $this->db->executeSQL(
            <<<SQL
UPDATE tracks
SET track_alias = ?, track_name = ?, track_description = ?, marble_sizes_accepted = ?,
    is_transport = ?, is_splitter = ?, is_landing_zone = ?, entity_type = ?
WHERE track_id = ?
SQL,
            'ssssiissi',
            [$alias, $name, $description, $marble_sizes_str, $is_transport, $is_splitter, $is_landing_zone, $entity_type, $track_id]
        );
    }

    public function deleteConnection(int $from_track_id, int $to_track_id): void
    {
        $this->db->executeSQL(
            "DELETE FROM track_connections WHERE from_track_id = ? AND to_track_id = ?",
            'ii',
            [$from_track_id, $to_track_id]
        );
    }

    public function insertConnection(int $from_track_id, int $to_track_id, array $marble_sizes, string $connection_type = 'direct', string $description = ''): int
    {
        $marble_sizes_str = implode(',', $marble_sizes);

        return $this->db->insertFromRecord(
            'track_connections',
            'iisss',
            [
                'from_track_id' => $from_track_id,
                'to_track_id' => $to_track_id,
                'marble_sizes' => $marble_sizes_str,
                'connection_type' => $connection_type,
                'connection_description' => $description
            ]
        );
    }

    public function connectionExists(int $from_track_id, int $to_track_id): bool
    {
        $results = $this->db->fetchResults(
            "SELECT COUNT(*) as count FROM track_connections WHERE from_track_id = ? AND to_track_id = ?",
            'ii',
            [$from_track_id, $to_track_id]
        );

        $results->setRow(0);
        return $results->data['count'] > 0;
    }

    public function deleteTrackPart(int $track_id, int $part_id): void
    {
        $this->db->executeSQL(
            "DELETE FROM track_parts WHERE track_id = ? AND part_id = ?",
            'ii',
            [$track_id, $part_id]
        );
    }

    public function insertTrackPart(int $track_id, int $part_id, string $part_role = 'main', bool $is_exclusive = false): int
    {
        return $this->db->insertFromRecord(
            'track_parts',
            'iisi',
            [
                'track_id' => $track_id,
                'part_id' => $part_id,
                'part_role' => $part_role,
                'is_exclusive_to_track' => $is_exclusive
            ]
        );
    }

    public function trackPartExists(int $track_id, int $part_id): bool
    {
        $results = $this->db->fetchResults(
            "SELECT COUNT(*) as count FROM track_parts WHERE track_id = ? AND part_id = ?",
            'ii',
            [$track_id, $part_id]
        );

        $results->setRow(0);
        return $results->data['count'] > 0;
    }

    public function updateTrackPartExclusivity(int $track_id, int $part_id, bool $is_exclusive): void
    {
        $this->db->executeSQL(
            "UPDATE track_parts SET is_exclusive_to_track = ? WHERE track_id = ? AND part_id = ?",
            'iii',
            [$is_exclusive, $track_id, $part_id]
        );
    }

    public function findExclusivePartIds(): array
    {
        $results = $this->db->fetchResults(
            "SELECT DISTINCT part_id FROM track_parts WHERE is_exclusive_to_track = TRUE"
        );

        $part_ids = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $part_ids[] = $results->data['part_id'];
        }
        return $part_ids;
    }

    public function findTracksByPartId(int $part_id): array
    {
        $results = $this->db->fetchResults(
            <<<SQL
SELECT t.track_id,
       t.track_alias,
       t.track_name,
       t.track_description,
       t.marble_sizes_accepted,
       t.is_transport,
       t.is_splitter,
       t.is_landing_zone,
       t.entity_type,
       tp.part_role,
       tp.is_exclusive_to_track
FROM tracks t
JOIN track_parts tp ON t.track_id = tp.track_id
WHERE tp.part_id = ?
ORDER BY t.track_name ASC
SQL,
            'i',
            [$part_id]
        );

        $tracks = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $track = $this->hydrate($results->data);
            $track->part_role = $results->data['part_role'];
            $track->part_is_exclusive = (bool) $results->data['is_exclusive_to_track'];
            $tracks[] = $track;
        }
        return $tracks;
    }

    public function getDb(): DbInterface
    {
        return $this->db;
    }
}
