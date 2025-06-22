DROP TABLE IF EXISTS part_image_urls;

ALTER TABLE `parts_photos`
ADD `url` VARCHAR(200) CHARACTER SET ascii
COLLATE ascii_general_ci NULL
COMMENT 'in case no code exists'
AFTER `photo_code`;

INSERT INTO `parts_photos` (`part_photo_id`, `part_id`, `photo_code`, `url`, `friendly_name`, `is_primary`, `created_at`)
VALUES
(NULL, '1', '', 'https://b.robnugen.com/art/marble_track_3/track/parts/2019/2019_sep_23_0th_placed_outer_spiral_support_2.jpg', 'Zeroth Outer Spiral Support', 1, CURRENT_TIMESTAMP),
(NULL, '97', '', 'https://b.robnugen.com/art/marble_track_3/track/parts/2025/2025_jun_04_mr_greene_carries_small_marble_saver_1000.jpeg', 'Mr Greene carries the Small Marble Saver', 0, CURRENT_TIMESTAMP);
