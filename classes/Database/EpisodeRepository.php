<?php
namespace Database;

use Database\DbInterface;
use Domain\HasPhotos;
use Media\Episode;

class EpisodeRepository
{
    use HasPhotos;
    private DbInterface $db;

    private string $photoLinkingTable = 'episodes_2_photos';
    private string $primaryKeyColumn = 'episode_id';
    private int $episode_id;

    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    public function getId(): int
    {
        return $this->episode_id;
    }
    public function setEpisodeId(int $episode_id): void
    {
        $this->episode_id = $episode_id;
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
    public function findAll(): array
    {
        $results = $this->db->fetchResults(
            <<<SQL
SELECT episode_id,
title,
episode_english_description,
episode_frames,
livestream_id,
created_at
FROM episodes
ORDER BY created_at DESC
SQL
        );

        $episodes = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $this->episode_id = (int) $results->data['episode_id'];
            $episodes[] = $this->hydrate($results->data);
        }

        return $episodes;
    }

    public function findById(int $episode_id): ?Episode
    {
        $results = $this->db->fetchResults(
            "SELECT
                    episode_id,
                    title,
                    episode_english_description,
                    episode_frames,
                    livestream_id,
                    created_at
                  FROM episodes
                  WHERE episode_id = ?",
            'i',
            [$episode_id]
        );

        if ($results->numRows() === 0) {
            return null;
        }

        $this->episode_id = $episode_id;
        $results->setRow(0);
        return $this->hydrate(row: $results->data);
    }

    public function findByLivestreamId(int $livestream_id): ?Episode
    {
        $results = $this->db->fetchResults(
            "SELECT
                    episode_id,
                    title,
                    episode_english_description,
                    episode_frames,
                    livestream_id,
                    created_at
                  FROM episodes
                  WHERE livestream_id = ?",
            'i',
            [$livestream_id]
        );

        if ($results->numRows() === 0) {
            return null;
        }

        $results->setRow(0);
        $this->episode_id = (int) $results->data['episode_id'];
        return $this->hydrate(row: $results->data);
    }

    public function update(
        int $episode_id,
        string $title,
        string $desc,
        string $episode_frames,
        ?int $livestreamId = null
    ): bool
    {
        $this->db->executeSQL(
            "UPDATE episodes SET
                    title = ?,
                    episode_english_description = ?,
                    episode_frames = ?,
                    livestream_id = ?
                    WHERE episode_id = ?",
            'sssii',
            [$title, $desc, $episode_frames, $livestreamId, $episode_id]
        );
        return $this->db->getAffectedRows() > 0;
    }

    public function insert(
        string $title,
        string $desc,
        string $episode_frames,
        ?int $livestreamId = null
    ): int
    {
        return $this->db->insertFromRecord(
            'episodes',
            'sssi',
            [
                'title' => $title,
                'episode_english_description' => $desc,
                'episode_frames' => $episode_frames,
                'livestream_id' => $livestreamId
            ]
        );
    }

    private function hydrate(array $row): Episode
    {
        $episode = new Episode(
            episode_id: (int) $row['episode_id'],
            title: $row['title'] ?? '',
            episode_english_description: $row['episode_english_description'] ?? '',
            episode_frames: $row['episode_frames'] ?? '',
            livestream_id: isset($row['livestream_id']) ? (int) $row['livestream_id'] : null,
            created_at: $row['created_at']
        );
        $this->loadPhotos();
        $episode->photos = $this->getPhotos();
        return $episode;
    }
}
