<?php

namespace Database;

class PhrasesRepository
{
    private DbInterface $db;

    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    public function create(string $phrase, array $token_ids, int $moment_id): int
    {
        return $this->db->insertFromRecord(
            'phrases',
            'ssi',
            [
                'phrase' => $phrase,
                'token_json' => json_encode($token_ids),
                'moment_id' => $moment_id,
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
