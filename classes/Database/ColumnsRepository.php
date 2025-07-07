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
            <<<SQL
SELECT c.*, w.worker_alias, wn.worker_name,
       COUNT(DISTINCT t.token_id) AS token_count
FROM columns c
LEFT JOIN workers w ON c.worker_id = w.worker_id
LEFT JOIN worker_names wn ON w.worker_id = wn.worker_id AND wn.language_code = 'en'
LEFT JOIN tokens t ON c.column_id = t.column_id
WHERE c.page_id = ?
GROUP BY c.column_id
ORDER BY c.col_sort ASC, c.created_at ASC
SQL,
            'i',
            [$page_id]
        );

        $columns = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $column = $this->hydrate($results->data);
            // Add worker info to column object
            $column->worker_alias = $results->data['worker_alias'] ?? '';
            $column->worker_name = $results->data['worker_name'] ?? '';
            $column->token_count = (int) ($results->data['token_count'] ?? 0);
            $columns[] = $column;
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