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
}
