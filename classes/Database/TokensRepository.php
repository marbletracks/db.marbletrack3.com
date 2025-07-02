<?php

namespace Database;

use Physical\Token;

class TokensRepository
{
    public function __construct(private Database $db)
    {
    }

    public function findByColumnId(int $column_id): array
    {
        $results = $this->db->executeSQL(
            "SELECT * FROM tokens WHERE column_id = ? ORDER BY created_at",
            'i',
            [$column_id]
        );

        $tokens = [];
        for ($i = 0; $i < $results->getNumRows(); $i++) {
            $results->setRow($i);
            $tokens[] = $this->hydrate($results->data);
        }

        return $tokens;
    }

    public function findById(int $token_id): ?Token
    {
        $results = $this->db->executeSQL(
            "SELECT * FROM tokens WHERE token_id = ? LIMIT 1",
            'i',
            [$token_id]
        );

        if ($results->getNumRows() === 0) {
            return null;
        }

        $results->setRow(0);
        return $this->hydrate($results->data);
    }

    public function insert(
        int $column_id,
        string $token_string,
        string $token_date,
        int $token_x_pos,
        int $token_y_pos,
        int $token_width = 100,
        int $token_height = 50,
        string $token_color = 'Black'
    ): int {
        return $this->db->insertFromRecord(
            'tokens',
            'issiiiis',
            [
                'column_id' => $column_id,
                'token_string' => $token_string,
                'token_date' => $token_date,
                'token_x_pos' => $token_x_pos,
                'token_y_pos' => $token_y_pos,
                'token_width' => $token_width,
                'token_height' => $token_height,
                'token_color' => $token_color
            ]
        );
    }

    public function update(
        int $token_id,
        string $token_string,
        string $token_date,
        int $token_x_pos,
        int $token_y_pos,
        int $token_width,
        int $token_height,
        string $token_color
    ): void {
        $this->db->executeSQL(
            "UPDATE tokens SET token_string = ?, token_date = ?, token_x_pos = ?, token_y_pos = ?, token_width = ?, token_height = ?, token_color = ? WHERE token_id = ?",
            'ssiiissi',
            [$token_string, $token_date, $token_x_pos, $token_y_pos, $token_width, $token_height, $token_color, $token_id]
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

    private function hydrate(array $row): Token
    {
        return new Token(
            token_id: (int) $row['token_id'],
            column_id: (int) $row['column_id'],
            token_string: $row['token_string'],
            token_date: $row['token_date'] ?? '',
            token_x_pos: (int) $row['token_x_pos'],
            token_y_pos: (int) $row['token_y_pos'],
            token_width: (int) $row['token_width'],
            token_height: (int) $row['token_height'],
            token_color: $row['token_color'],
            created_at: $row['created_at']
        );
    }
}