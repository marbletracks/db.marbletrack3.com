-- Map landing zone parts to their corresponding tracks
INSERT INTO track_parts (track_id, part_id, part_role)
SELECT
    t.track_id,
    p.part_id,
    'main'
FROM tracks t
JOIN parts p ON p.part_alias = t.track_alias
WHERE t.is_landing_zone = TRUE;

-- Map Outer Spiral track to its main part
INSERT INTO track_parts (track_id, part_id, part_role)
SELECT
    t.track_id,
    p.part_id,
    'main'
FROM tracks t
JOIN parts p ON p.part_alias = 'os'  -- outer spiral part
WHERE t.track_alias = 'outer_spiral';

-- Map Outer Spiral supports to the Outer Spiral track
INSERT INTO track_parts (track_id, part_id, part_role)
SELECT
    t.track_id,
    p.part_id,
    'support'
FROM tracks t
JOIN parts p ON p.part_alias LIKE '%poss'  -- all placed outer spiral supports
WHERE t.track_alias = 'outer_spiral';

-- Map Triple Splitter parts to Triple Splitter System track
INSERT INTO track_parts (track_id, part_id, part_role)
SELECT
    t.track_id,
    p.part_id,
    CASE
        WHEN p.part_alias = 'ts' THEN 'main'
        WHEN p.part_alias LIKE '%support%' OR p.part_alias LIKE '%bar%' THEN 'support'
        WHEN p.part_alias LIKE '%feeder%' OR p.part_alias LIKE '%catcher%' THEN 'connector'
        WHEN p.part_alias LIKE '%guide%' THEN 'guide'
        ELSE 'main'
    END
FROM tracks t
JOIN parts p ON (
    p.part_alias = 'ts' OR  -- main triple splitter
    p.part_alias LIKE 'ts%' OR  -- triple splitter related parts
    p.part_alias LIKE '%triple%splitter%'
)
WHERE t.track_alias = 'triple_splitter_system';
