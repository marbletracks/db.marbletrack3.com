-- Add write permission flag to API keys
-- Default 0 (read-only). Only explicitly granted keys can write.
ALTER TABLE api_keys
    ADD COLUMN can_write TINYINT(1) NOT NULL DEFAULT 0
    COMMENT 'Whether this key can use write endpoints (PATCH, POST, DELETE)';
