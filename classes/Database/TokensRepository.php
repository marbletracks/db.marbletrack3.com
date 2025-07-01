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