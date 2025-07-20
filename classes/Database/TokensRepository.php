<?php

namespace Database;

use Physical\Token;

class TokensRepository
{
    private DbInterface $db;

    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    public function findById(int $token_id): ?Token
    {
        $results = $this->db->fetchResults(
            "SELECT * FROM tokens WHERE token_id = ?",
            'i',
            [$token_id]
        );

        if ($results->numRows() === 0) {
            return null;
        }

        $results->setRow(0);
        return $this->hydrate($results->data);
    }

    public function findByColumnId(int $column_id): array
    {
        $results = $this->db->fetchResults(
            "SELECT * FROM tokens WHERE column_id = ? ORDER BY token_y_pos ASC, token_x_pos ASC",
            'i',
            [$column_id]
        );

        $tokens = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $tokens[] = $this->hydrate($results->data);
        }

        return $tokens;
    }

    /**
     * Helps Realtime Moments page to keep track of tokens per worker.
     * @param int $worker_id
     * @return Token[]
     */
    public function findForWorker(int $worker_id): array
    {
        // First, find the permanent token for each column for this worker.
        // A permanent token is the one with the lowest y_pos in its column.
        $sql_permanent = "SELECT t.token_id
                          FROM tokens t
                          JOIN (
                              SELECT column_id, MIN(token_y_pos) as min_y
                              FROM tokens
                              GROUP BY column_id
                          ) as min_tokens ON t.column_id = min_tokens.column_id AND t.token_y_pos = min_tokens.min_y
                          JOIN columns c ON t.column_id = c.column_id
                          WHERE c.worker_id = ?";
        $permanent_results = $this->db->fetchResults($sql_permanent, 'i', [$worker_id]);
        $permanent_token_ids = [];
        for ($i = 0; $i < $permanent_results->numRows(); $i++) {
            $permanent_results->setRow($i);
            $permanent_token_ids[] = (int)$permanent_results->data['token_id'];
        }

        $params = [$worker_id];
        $types = 'i';

        $permanent_clause = "p.phrase_id IS NULL"; // Default for when there are no permanent tokens
        if (!empty($permanent_token_ids)) {
            $placeholders = implode(',', array_fill(0, count($permanent_token_ids), '?'));
            $permanent_clause = "p.phrase_id IS NULL OR t.token_id IN ($placeholders)";
            $types .= str_repeat('i', count($permanent_token_ids));
            $params = array_merge($params, $permanent_token_ids);
        }


        // Now, get all tokens for the worker that are either permanent, or not yet part of a moment.
        $sql = "SELECT t.*
                FROM tokens t
                JOIN columns c ON t.column_id = c.column_id
                LEFT JOIN phrases p ON JSON_CONTAINS(p.token_json, CAST(t.token_id AS JSON)) AND p.moment_id IS NOT NULL
                WHERE c.worker_id = ?
                AND ($permanent_clause)
                ORDER BY t.token_y_pos ASC, t.token_x_pos ASC";

        $results = $this->db->fetchResults($sql, $types, $params);

        $tokens = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $tokens[] = $this->hydrate($results->data);
        }

        return $tokens;
    }

    public function insert(
        int $column_id,
        string $token_string,
        string $token_date = '',
        int $token_x_pos = 0,
        int $token_y_pos = 0,
        int $token_width = 100,
        int $token_height = 50,
        string $token_color = 'Black'
    ): int {
        $this->db->executeSQL(
            "INSERT INTO tokens (column_id, token_string, token_date, token_x_pos, token_y_pos, token_width, token_height, token_color, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            'issiiiis',
            [$column_id, $token_string, $token_date, $token_x_pos, $token_y_pos, $token_width, $token_height, $token_color]
        );
        return $this->db->insertId();
    }

    public function update(
        int $token_id,
        int $column_id,
        string $token_string,
        string $token_date,
        int $token_x_pos,
        int $token_y_pos,
        int $token_width,
        int $token_height,
        string $token_color
    ): void {
        $this->db->executeSQL(
            "UPDATE tokens SET column_id = ?, token_string = ?, token_date = ?, token_x_pos = ?, token_y_pos = ?, token_width = ?, token_height = ?, token_color = ? WHERE token_id = ?",
            'issiiiisi',
            [$column_id, $token_string, $token_date, $token_x_pos, $token_y_pos, $token_width, $token_height, $token_color, $token_id]
        );
    }

    public function delete(int $token_id): void
    {
        $this->db->executeSQL(
            "DELETE FROM tokens WHERE token_id = ?",
            'i',
            [$token_id]
        );
    }

    public function updatePosition(int $token_id, int $x_pos, int $y_pos): void
    {
        $this->db->executeSQL(
            "UPDATE tokens SET token_x_pos = ?, token_y_pos = ? WHERE token_id = ?",
            'iii',
            [$x_pos, $y_pos, $token_id]
        );
    }

    public function updateSize(int $token_id, int $width, int $height): void
    {
        $this->db->executeSQL(
            "UPDATE tokens SET token_width = ?, token_height = ? WHERE token_id = ?",
            'iii',
            [$width, $height, $token_id]
        );
    }

    private function hydrate(array $row): Token
    {
        return new Token(
            token_id: (int) $row['token_id'],
            column_id: (int) $row['column_id'],
            token_string: $row['token_string'] ?? '',
            token_date: $row['token_date'] ?? '',
            token_x_pos: (int) $row['token_x_pos'],
            token_y_pos: (int) $row['token_y_pos'],
            token_width: (int) $row['token_width'],
            token_height: (int) $row['token_height'],
            token_color: $row['token_color'] ?? 'Black',
            created_at: $row['created_at'] ?? ''
        );
    }
}
