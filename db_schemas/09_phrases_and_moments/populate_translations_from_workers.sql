-- This script populates the `moment_translations` table with data from the legacy
-- `workers_3_moments` linking table. It's designed to be run once to migrate
-- the simple relationships into the new unified structure.

-- It will:
-- 1. Select all records from `workers_3_moments`.
-- 2. Check if a corresponding record already exists in `moment_translations`.
-- 3. If no record exists, it inserts a new one, preserving the original sort_order
--    and leaving the `translated_note` as NULL, so the system falls back to
--    the default moment notes.

INSERT INTO moment_translations (moment_id, perspective_entity_id, perspective_entity_type, translated_note, is_significant, sort_order)
SELECT
    w2m.moment_id,
    w2m.worker_id,
    'worker',
    NULL, -- Set translated_note to NULL to use the default moment note
    0,    -- Default is_significant to false
    w2m.sort_order
FROM
    workers_3_moments w2m
LEFT JOIN
    moment_translations mt ON w2m.moment_id = mt.moment_id AND w2m.worker_id = mt.perspective_entity_id AND mt.perspective_entity_type = 'worker'
WHERE
    mt.moment_id IS NULL;
