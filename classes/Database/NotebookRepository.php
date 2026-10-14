<?php
namespace Database;

use Physical\Notebook;

class NotebookRepository
{
    private DbInterface $db;
    private PhotoLinks $photoLinks;

    public function __construct(DbInterface $db)
    {
        $this->db = $db;
        $this->photoLinks = new PhotoLinks($db, 'notebooks', 'notebooks_2_photos', 'notebook_id');
    }

    public function findById(int $notebook_id): ?Notebook
    {
        $results = $this->db->fetchResults(
            "SELECT notebook_id, title, created_at FROM notebooks WHERE notebook_id = ?",
            'i',
            [$notebook_id]
        );

        if ($results->numRows() === 0) {
            return null;
        }

        return $this->hydrateAll($results->toArray())[0];
    }

    public function findAll(): array
    {
        $results = $this->db->fetchResults(
            "SELECT notebook_id, title, created_at FROM notebooks ORDER BY notebook_id ASC"
        );

        return $this->hydrateAll($results->toArray());
    }

    public function insert(string $title = null, string $created_at = null): int
    {
        return $this->db->insertFromRecord(
            'notebooks',
            'ss',
            [
                'title' => $title,
                'created_at' => $created_at
            ]
        );
    }

    /**
     * @param int[]    $loadedPhotoIds photo IDs the edit form showed when it loaded
     * @param string[] $urls           the form's photo URLs, in screen order
     */
    public function savePhotosFromForm(int $notebook_id, array $loadedPhotoIds, array $urls): void
    {
        $this->photoLinks->saveFormUrls($notebook_id, $loadedPhotoIds, $urls);
    }

    /** @return Notebook[] */
    private function hydrateAll(array $rows): array
    {
        $photos = $this->photoLinks->forIds(array_column($rows, 'notebook_id'));
        $notebooks = [];
        foreach ($rows as $row) {
            $notebook = $this->hydrate($row);
            $notebook->photos = $photos[$notebook->notebook_id] ?? [];
            $notebooks[] = $notebook;
        }
        return $notebooks;
    }

    private function hydrate(array $row): Notebook
    {
        return new Notebook(
            notebook_id: (int) $row['notebook_id'],
            title: $row['title'] ?? null,
            created_at: $row['created_at'] ?? null
        );
    }
}
