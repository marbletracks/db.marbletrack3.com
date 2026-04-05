-- API keys for external agent authentication
-- Each user can have multiple keys (e.g. one per agent/tool)
-- Raw key format: 'sk_' prefix + 61 random alphanumeric chars
-- Only the SHA-256 hash is stored; raw key is shown once at creation
CREATE TABLE api_keys (
    key_id       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id      INT UNSIGNED NOT NULL,
    api_key_hash CHAR(64) NOT NULL COMMENT 'SHA-256 hex hash of the raw key',
    label        VARCHAR(255) NULL,                          -- human-readable name, e.g. "claude code"
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_used    DATETIME NULL,
    is_active    TINYINT(1) NOT NULL DEFAULT 1,              -- soft delete / revoke

    PRIMARY KEY (key_id),
    UNIQUE KEY uniq_api_key_hash (api_key_hash),
    KEY idx_user_keys (user_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
