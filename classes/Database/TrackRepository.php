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
       t.is_landing_zone
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
       t.is_landing_zone
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
       t.is_landing_zone
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
       t.is_landing_zone
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
       tp.part_role
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
            is_landing_zone: (bool) $data['is_landing_zone']
        );
    }

    public function getDb(): DbInterface
    {
        return $this->db;
    }
}
