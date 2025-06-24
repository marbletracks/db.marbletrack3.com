<?php
namespace Database;

use Database\DbInterface;
use Media\Livestream;

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
SELECT livestream_id, external_id, title, description, published_at, status, created_at
FROM livestreams
ORDER BY published_at DESC, livestream_id DESC
SQL
        );

        $livestreams = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $livestreams[] = new Livestream(
                livestream_id: (int) $results->data['livestream_id'],
                external_id: $results->data['external_id'],
                title: $results->data['title'] ?? '',
                description: $results->data['description'] ?? '',
                published_at: $results->data['published_at'],
                status: $results->data['status'],
                created_at: $results->data['created_at']
            );
        }

        return $livestreams;
    }

    public function findById(int $livestream_id): ?Livestream
    {
        $results = $this->db->fetchResults(
            <<<SQL
SELECT livestream_id, external_id, title, description, published_at, status, created_at
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
        return new Livestream(
            livestream_id: (int) $results->data['livestream_id'],
            external_id: $results->data['external_id'],
            title: $results->data['title'] ?? '',
            description: $results->data['description'] ?? '',
            published_at: $results->data['published_at'],
            status: $results->data['status'],
            created_at: $results->data['created_at']
        );
    }


}
