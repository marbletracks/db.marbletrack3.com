<?php
namespace Database;

use Physical\Notebook;

class NotebookRepository
{
    private DbInterface $db;

    public function __construct(DbInterface $db)
    {
        $this->db = $db;
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

        $results->setRow(0);
        return $this->hydrate($results->data);
    }

    public function findAll(): array
    {
        $results = $this->db->fetchResults(
            "SELECT notebook_id, title, created_at FROM notebooks ORDER BY notebook_id ASC"
        );

        $notebooks = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $notebooks[] = $this->hydrate($results->data);
        }

        return $notebooks;
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

    private function hydrate(array $row): Notebook
    {
        return new Notebook(
            notebook_id: (int) $row['notebook_id'],
            title: $row['title'] ?? null,
            created_at: $row['created_at'] ?? null
        );
    }
}
