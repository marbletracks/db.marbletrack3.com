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
        $page->primaryPhoto = $this->getPrimaryPhoto();
        return $page;
    }
}