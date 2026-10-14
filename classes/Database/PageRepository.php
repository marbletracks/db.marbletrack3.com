<?php

namespace Database;

use Physical\Page;

class PageRepository
{
    private DbInterface $db;
    private PhotoLinks $photoLinks;

    public function __construct(DbInterface $db)
    {
        $this->db = $db;
        $this->photoLinks = new PhotoLinks($db, 'pages', 'pages_2_photos', 'page_id');
    }

    public function findAll(): array
    {
        $results = $this->db->fetchResults("SELECT * FROM pages ORDER BY created_at DESC");
        return $this->hydrateAll($results->toArray());
    }

    public function findById(int $page_id): ?Page
    {
        $results = $this->db->fetchResults(
            "SELECT * FROM pages WHERE page_id = ?",
            'i',
            [$page_id]
        );

        if ($results->numRows() === 0) {
            return null;
        }
        return $this->hydrateAll($results->toArray())[0];
    }

    public function insert(int $notebook_id, string $number, string $created_at): int
    {
        $this->db->executeSQL(
            "INSERT INTO pages (notebook_id, number, created_at) VALUES (?, ?, ?)",
            'iss',
            [$notebook_id, $number, $created_at]
        );
        return $this->db->insertId();
    }

    public function update(int $page_id, int $notebook_id, string $number, string $created_at): void
    {
        $this->db->executeSQL(
            "UPDATE pages SET notebook_id = ?, number = ?, created_at = ? WHERE page_id = ?",
            'issi',
            [$notebook_id, $number, $created_at, $page_id]
        );
    }

    /**
     * @param int[]    $loadedPhotoIds photo IDs the edit form showed when it loaded
     * @param string[] $urls           the form's photo URLs, in screen order
     */
    public function savePhotosFromForm(int $page_id, array $loadedPhotoIds, array $urls): void
    {
        $this->photoLinks->saveFormUrls($page_id, $loadedPhotoIds, $urls);
    }

    /** @return Page[] */
    private function hydrateAll(array $rows): array
    {
        $photos = $this->photoLinks->forIds(array_column($rows, 'page_id'));
        $pages = [];
        foreach ($rows as $row) {
            $page = $this->hydrate($row);
            $page->photos = $photos[$page->page_id] ?? [];
            $page->primaryPhoto = PhotoLinks::primaryOf($page->photos);
            $pages[] = $page;
        }
        return $pages;
    }

    private function hydrate(array $row): Page
    {
        return new Page(
            page_id: (int) $row['page_id'],
            notebook_id: (int) $row['notebook_id'],
            number: $row['number'] ?? '',
            created_at: $row['created_at'] ?? ''
        );
    }
}
