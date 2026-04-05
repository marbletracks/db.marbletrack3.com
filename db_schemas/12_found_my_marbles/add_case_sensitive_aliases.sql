-- Change alias columns to case-sensitive collation (utf8mb4_bin)
-- so marble aliases like 'Lb' won't collide with part aliases like 'lb'.
-- All existing aliases are lowercase, so no data impact.

ALTER TABLE parts
    MODIFY part_alias VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL;

ALTER TABLE workers
    MODIFY worker_alias VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL;
