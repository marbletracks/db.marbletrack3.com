<?php

namespace Database;

use Domain\HasPhotos;
use Physical\Page;

class PageRepository
{
    use HasPhotos;

    private DbInterface $db;
    private string $photoLinkingTable = 'pages_2_photos';
    private string $primaryKeyColumn = 'page_id';
    private int $page_id;

    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    public function getId(): int
    {
        return $this->page_id;
    }

    public function setPageId(int $page_id): void
    {
        $this->page_id = $page_id;
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
        $results = $this->db->fetchResults("SELECT * FROM pages ORDER BY created_at DESC");
        $pages = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $this->page_id = (int) $results->data['page_id'];
            $pages[] = $this->hydrate($results->data);
        }
        return $pages;
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
        $this->page_id = $page_id;
        $results->setRow(0);
        return $this->hydrate($results->data);
    }

    private function hydrate(array $row): Page
    {
        $page = new Page(
            page_id: (int) $row['page_id'],
            notebook_id: (int) $row['notebook_id'],
            number: $row['number'] ?? '',
            created_at: $row['created_at'] ?? ''
        );
        $this->loadPhotos();
        $page->photos = $this->getPhotos();
        return $page;
    }
}