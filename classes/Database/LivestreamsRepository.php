<?php
namespace Database;

use Database\DbInterface;
use Media\LocalLivestream;

class LivestreamsRepository
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
SELECT livestream_id, external_id, platform, title, description, duration, thumbnail_url, published_at, status, created_at
FROM livestreams
ORDER BY published_at DESC, livestream_id DESC
SQL
        );

        $livestreams = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $livestreams[] = new LocalLivestream(
                livestream_id: (int) $results->data['livestream_id'],
                external_id: $results->data['external_id'],
                platform: $results->data['platform'] ?? '',
                title: $results->data['title'] ?? '',
                description: $results->data['description'] ?? '',
                thumbnail_url: $results->data['thumbnail_url'] ?? null,
                published_at: $results->data['published_at'],
                duration: $results->data['duration'] ?? null,
                status: $results->data['status'],
                created_at: $results->data['created_at']
            );
        }

        return $livestreams;
    }

    public function findById(int $livestream_id): ?LocalLivestream
    {
        $results = $this->db->fetchResults(
            <<<SQL
SELECT livestream_id, external_id, platform, title, description, thumbnail_url,published_at, status, created_at
FROM livestreams
WHERE livestream_id = ?
SQL,
            'i',
            [$livestream_id]
        );

        if ($results->numRows() === 0) {
            return null;
        }

        $results->setRow(0);
        return new LocalLivestream(
            livestream_id: (int) $results->data['livestream_id'],
            external_id: $results->data['external_id'],
            platform: $results->data['platform'] ?? '',
            title: $results->data['title'] ?? '',
            description: $results->data['description'] ?? '',
            thumbnail_url: $results->data['thumbnail_url'] ?? null,
            duration: $results->data['duration'] ?? null,
            published_at: $results->data['published_at'],
            status: $results->data['status'],
            created_at: $results->data['created_at']
        );
    }

    public function findByExternalId(string $external_id): ?LocalLivestream
    {
        $results = $this->db->fetchResults(
            <<<SQL
SELECT livestream_id
FROM livestreams
WHERE external_id = ?
SQL,
            's',
            [$external_id]
        );
        if ($results->numRows() === 0) {
            return null;
        }
        $results->setRow(0);
        return $this->findById((int) $results->data['livestream_id']);
    }

    public function saveDurationToDatabase(LocalLivestream $livestream): bool
    {
        $this->errors = [];

        if (empty($livestream->livestream_id)) {
            echo "No livestream_id set to save duration to database<br>";
            return false;
        }

        $params = [];
        $params['duration'] = $livestream->duration;
        $params['thumbnail_url'] = $livestream->thumbnail_url;
        $types = "ss";

        $this->db->updateFromRecord("`livestreams`", $types, $params, "`livestream_id` = " . intval($livestream->livestream_id));
        return true;  // Maybe add a Transaction and try-catch here?
    }

    public function setLivestreamStatus(int $livestream_id, string $status): bool
    {
        $params = [
            'status' => $status,
        ];
        $types = 's';

        $this->db->updateFromRecord(
            "`livestreams`",
            $types,
            $params,
            "`livestream_id` = " . intval($livestream_id)
        );
        return true;
    }
}
