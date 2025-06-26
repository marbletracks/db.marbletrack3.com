<?php
namespace Database;

use Database\DbInterface;
use Media\Episode;

class EpisodeRepository
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
SELECT episode_id, title, episode_english_description, livestream_id, created_at
FROM episodes
ORDER BY created_at DESC
SQL
        );

        $episodes = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $episodes[] = new Episode(
                episode_id: (int) $results->data['episode_id'],
                title: $results->data['title'] ?? '',
                episode_english_description: $results->data['episode_english_description'] ?? '',
                livestream_id: $results->data['livestream_id'] !== null ? (int) $results->data['livestream_id'] : null,
                created_at: $results->data['created_at']
            );
        }

        return $episodes;
    }

    public function findById(int $episode_id): ?Episode
    {
        $results = $this->db->fetchResults(
            "SELECT episode_id, title, episode_english_description, livestream_id, created_at FROM episodes WHERE episode_id = ?",
            'i',
            [$episode_id]
        );

        if ($results->numRows() === 0) {
            return null;
        }

        $results->setRow(0);
        return new Episode(
            episode_id: (int) $results->data['episode_id'],
            title: $results->data['title'] ?? '',
            episode_english_description: $results->data['episode_english_description'] ?? '',
            livestream_id: $results->data['livestream_id'] !== null ? (int) $results->data['livestream_id'] : null,
            created_at: $results->data['created_at']
        );
    }

    public function update(int $episode_id, string $title, string $desc, ?int $livestreamId = null): bool
    {
        $rowsAffected = $this->db->executeSQL(
            "UPDATE episodes SET title = ?, episode_english_description = ?, livestream_id = ? WHERE episode_id = ?",
            'ssii',
            [$title, $desc, $livestreamId, $episode_id]
        );
        return $rowsAffected > 0;
    }

    public function insert(string $title, string $desc, ?int $livestreamId = null): int
    {
        return $this->db->insertFromRecord(
            'episodes',
            'ssi',
            [
                'title' => $title,
                'episode_english_description' => $desc,
                'livestream_id' => $livestreamId
            ]
        );
    }

}
