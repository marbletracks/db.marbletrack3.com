<?php
namespace Database;

use Database\DbInterface;
use Media\Episode;

class EpisodeRepository
{
    private DbInterface $db;
    private PhotoLinks $photoLinks;

    public function __construct(DbInterface $db)
    {
        $this->db = $db;
        $this->photoLinks = new PhotoLinks($db, 'episodes', 'episodes_2_photos', 'episode_id');
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

        return $this->hydrateAll($results->toArray());
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

        return $this->hydrateAll($results->toArray())[0];
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

        return $this->hydrateAll($results->toArray())[0];
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

    /**
     * @param int[]    $loadedPhotoIds photo IDs the edit form showed when it loaded
     * @param string[] $urls           the form's photo URLs, in screen order
     */
    public function savePhotosFromForm(int $episode_id, array $loadedPhotoIds, array $urls): void
    {
        $this->photoLinks->saveFormUrls($episode_id, $loadedPhotoIds, $urls);
    }

    /** @return Episode[] */
    private function hydrateAll(array $rows): array
    {
        $photos = $this->photoLinks->forIds(array_column($rows, 'episode_id'));
        $episodes = [];
        foreach ($rows as $row) {
            $episode = $this->hydrate($row);
            $episode->photos = $photos[$episode->episode_id] ?? [];
            $episodes[] = $episode;
        }
        return $episodes;
    }

    private function hydrate(array $row): Episode
    {
        return new Episode(
            episode_id: (int) $row['episode_id'],
            title: $row['title'] ?? '',
            episode_english_description: $row['episode_english_description'] ?? '',
            episode_frames: $row['episode_frames'] ?? '',
            livestream_id: isset($row['livestream_id']) ? (int) $row['livestream_id'] : null,
            created_at: $row['created_at']
        );
    }
}
