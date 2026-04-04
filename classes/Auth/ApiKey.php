<?php
/**
 * Manages API key authentication for external agent access.
 * Adapted from mg.robnugen.com pattern for MT3's DbInterface (mysqli).
 */
namespace Auth;

use Database\DbInterface;

class ApiKey
{
    private ?int $last_key_id = null;

    public function __construct(
        private DbInterface $db,
    ) {}

    /**
     * Validates an API key and returns the associated user_id, or null if invalid/inactive.
     * Updates last_used timestamp on success and stores key_id for getLastKeyId().
     */
    public function validateKey(string $raw_key): ?int
    {
        $key_hash = hash('sha256', $raw_key);

        $results = $this->db->fetchResults(
            "SELECT key_id, user_id FROM api_keys WHERE api_key_hash = ? AND is_active = 1 LIMIT 1",
            's',
            [$key_hash]
        );

        if ($results->numRows() === 0) {
            return null;
        }

        $results->setRow(0);
        $row = $results->data;

        $this->last_key_id = (int) $row['key_id'];

        $this->db->executeSQL(
            "UPDATE api_keys SET last_used = NOW() WHERE key_id = ?",
            'i',
            [$this->last_key_id]
        );

        return (int) $row['user_id'];
    }

    /**
     * Returns the key_id from the most recent successful validateKey() call.
     */
    public function getLastKeyId(): ?int
    {
        return $this->last_key_id;
    }

    /**
     * Generates a new API key for the user, stores a SHA-256 hash, and returns the raw key.
     * The raw key is shown to the user once and never stored.
     * Key format: 'sk_' prefix + 61 random chars = 64 chars total.
     */
    public function generateKey(int $user_id, string $label = ''): string
    {
        $raw_key  = 'sk_' . \Utilities::randomString(61);
        $key_hash = hash('sha256', $raw_key);

        $this->db->insertFromRecord(
            'api_keys',
            'iss',
            [
                'user_id'      => $user_id,
                'api_key_hash' => $key_hash,
                'label'        => $label,
            ]
        );

        return $raw_key;
    }

    /**
     * Revokes a key by key_id. Verifies ownership before deactivating.
     */
    public function revokeKey(int $key_id, int $user_id): bool
    {
        $this->db->executeSQL(
            "UPDATE api_keys SET is_active = 0 WHERE key_id = ? AND user_id = ?",
            'ii',
            [$key_id, $user_id]
        );

        return $this->db->getAffectedRows() > 0;
    }

    /**
     * Returns all keys for a user: key_id, label, created_at, last_used, is_active.
     * Never returns the raw key value.
     */
    public function getKeysForUser(int $user_id): array
    {
        $results = $this->db->fetchResults(
            "SELECT key_id, label, created_at, last_used, is_active
             FROM api_keys
             WHERE user_id = ?
             ORDER BY created_at DESC",
            'i',
            [$user_id]
        );

        $keys = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $keys[] = $results->data;
        }

        return $keys;
    }
}
