<?php

namespace Database;

class PhrasesRepository
{
    private DbInterface $db;

    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    public function create(string $phrase, array $token_ids): int
    {
        return $this->db->insertFromRecord(
            'phrases',
            'ss',
            [
                'phrase' => $phrase,
                'token_json' => json_encode($token_ids),
            ]
        );
    }

    public function setMomentId(int $phrase_id, int $moment_id): void
    {
        $this->db->executeSQL(
            "UPDATE phrases SET moment_id = ? WHERE phrase_id = ?",
            'ii',
            [$moment_id, $phrase_id]
        );
    }
}
