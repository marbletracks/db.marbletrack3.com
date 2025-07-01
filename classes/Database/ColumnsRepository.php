<?php

namespace Database;

use Physical\Column;

class ColumnsRepository
{
    private DbInterface $db;

    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    public function findById(int $column_id): ?Column
    {
        $results = $this->db->fetchResults(
            "SELECT * FROM columns WHERE column_id = ?",
            'i',
            [$column_id]
        );

        if ($results->numRows() === 0) {
            return null;
        }

        $results->setRow(0);
        return $this->hydrate($results->data);
    }

    public function findByPageId(int $page_id): array
    {
        $results = $this->db->fetchResults(
            "SELECT * FROM columns WHERE page_id = ? ORDER BY col_sort ASC, created_at ASC",
            'i',
            [$page_id]
        );

        $columns = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $columns[] = $this->hydrate($results->data);
        }

        return $columns;
    }

    public function findAll(): array
    {
        $results = $this->db->fetchResults("SELECT * FROM columns ORDER BY created_at DESC");
        $columns = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $columns[] = $this->hydrate($results->data);
        }
        return $columns;
    }

    public function insert(int $page_id, int $worker_id, string $col_name, int $col_sort = 0): int
    {
        $this->db->executeSQL(
            "INSERT INTO columns (page_id, worker_id, col_name, col_sort, created_at) VALUES (?, ?, ?, ?, NOW())",
            'iisi',
            [$page_id, $worker_id, $col_name, $col_sort]
        );
        return $this->db->insertId();
    }

    public function update(int $column_id, int $page_id, int $worker_id, string $col_name, int $col_sort): void
    {
        $this->db->executeSQL(
            "UPDATE columns SET page_id = ?, worker_id = ?, col_name = ?, col_sort = ? WHERE column_id = ?",
            'iisii',
            [$page_id, $worker_id, $col_name, $col_sort, $column_id]
        );
    }

    public function delete(int $column_id): void
    {
        $this->db->executeSQL(
            "DELETE FROM columns WHERE column_id = ?",
            'i',
            [$column_id]
        );
    }

    private function hydrate(array $row): Column
    {
        return new Column(
            column_id: (int) $row['column_id'],
            page_id: (int) $row['page_id'],
            worker_id: (int) $row['worker_id'],
            col_name: $row['col_name'] ?? '',
            col_sort: (int) $row['col_sort'],
            created_at: $row['created_at'] ?? ''
        );
    }
}