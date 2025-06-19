-- To record the current and evolving status of your placed outer spiral supports,
-- while linking each support to its `part_id` and `SSOP` location,
-- here's a robust SQL schema design:

-- 🗃️ New Table: `parts_oss_status`

CREATE TABLE parts_oss_status (
  parts_oss_status_id INT AUTO_INCREMENT PRIMARY KEY,
  part_id INT NOT NULL,
  ssop_label VARCHAR(20) NOT NULL,               -- e.g. 'SSOP130'
  ssop_mm DECIMAL(6,1) NOT NULL,                 -- e.g. 130.0
  height_orig DECIMAL(5,2) NOT NULL,             -- Measured when placed
  height_best DECIMAL(5,2) NOT NULL,             -- From best-fit analysis
  height_now DECIMAL(5,2),                       -- Editable in admin UI
  last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (part_id) REFERENCES parts(part_id) ON DELETE CASCADE
);

-- ✅ Why this works:

-- `part_id`** links each support to your existing `parts` table.
-- `ssop_label` + `ssop_mm`** gives both human-readable and numeric identifiers
--    measured in millimeters from 0 (top right of triple splitter).
-- `height_orig`** captures what it was when installed (your permanent record).
-- `height_best`** records your best-fit slope value (target to aim for).
-- `height_now`** can be adjusted over time by your tools or admin.
-- `last_updated`** helps you track progress.

INSERT INTO parts_oss_status (part_id, ssop_label, ssop_mm, height_orig, height_best, height_now) SELECT part_id, 'SSOP000', 0, 22.0, 25.21, 22.0 FROM parts WHERE part_alias = '0poss';
INSERT INTO parts_oss_status (part_id, ssop_label, ssop_mm, height_orig, height_best, height_now) SELECT part_id, 'SSOP420', 420, 33.0, 33.03, 33.0 FROM parts WHERE part_alias = '1poss';
INSERT INTO parts_oss_status (part_id, ssop_label, ssop_mm, height_orig, height_best, height_now) SELECT part_id, 'SSOP550', 550, 35.0, 35.46, 35.0 FROM parts WHERE part_alias = '2poss';
INSERT INTO parts_oss_status (part_id, ssop_label, ssop_mm, height_orig, height_best, height_now) SELECT part_id, 'SSOP060', 60, 24.0, 26.32, 24.0 FROM parts WHERE part_alias = '3poss';
INSERT INTO parts_oss_status (part_id, ssop_label, ssop_mm, height_orig, height_best, height_now) SELECT part_id, 'SSOP180', 180, 27.0, 28.56, 27.0 FROM parts WHERE part_alias = '4poss';
INSERT INTO parts_oss_status (part_id, ssop_label, ssop_mm, height_orig, height_best, height_now) SELECT part_id, 'SSOP320', 320, 31.5, 31.17, 31.5 FROM parts WHERE part_alias = '5poss';
INSERT INTO parts_oss_status (part_id, ssop_label, ssop_mm, height_orig, height_best, height_now) SELECT part_id, 'SSOP260', 260, 31.0, 30.05, 31.0 FROM parts WHERE part_alias = '6poss';
INSERT INTO parts_oss_status (part_id, ssop_label, ssop_mm, height_orig, height_best, height_now) SELECT part_id, 'SSOP615', 615, 37.0, 36.67, 37.0 FROM parts WHERE part_alias = '7poss';
INSERT INTO parts_oss_status (part_id, ssop_label, ssop_mm, height_orig, height_best, height_now) SELECT part_id, 'SSOP070', 70, 27.0, 26.51, 27.0 FROM parts WHERE part_alias = '8poss'; -- is 9poss on old site
INSERT INTO parts_oss_status (part_id, ssop_label, ssop_mm, height_orig, height_best, height_now) SELECT part_id, 'SSOP130', 130, 27.5, 27.63, 27.5 FROM parts WHERE part_alias = '9poss';
INSERT INTO parts_oss_status (part_id, ssop_label, ssop_mm, height_orig, height_best, height_now) SELECT part_id, 'SSOP830', 830, 40.0, 40.68, 40.0 FROM parts WHERE part_alias = '11poss';
INSERT INTO parts_oss_status (part_id, ssop_label, ssop_mm, height_orig, height_best, height_now) SELECT part_id, 'SSOP810', 810, 40.0, 40.3, 40.0 FROM parts WHERE part_alias = '12poss';
INSERT INTO parts_oss_status (part_id, ssop_label, ssop_mm, height_orig, height_best, height_now) SELECT part_id, 'SSOP670', 670, 38.0, 37.69, 38.0 FROM parts WHERE part_alias = '13poss';
INSERT INTO parts_oss_status (part_id, ssop_label, ssop_mm, height_orig, height_best, height_now) SELECT part_id, 'SSOP039', 39, 26.0, 25.95, 26.0 FROM parts WHERE part_alias = '14poss';
INSERT INTO parts_oss_status (part_id, ssop_label, ssop_mm, height_orig, height_best, height_now) SELECT part_id, 'SSOP001', 1, 25.0, 25.21, 25.0 FROM parts WHERE part_alias = '15poss';
INSERT INTO parts_oss_status (part_id, ssop_label, ssop_mm, height_orig, height_best, height_now) SELECT part_id, 'SSOP040', 40, 26.0, 25.95, 26.0 FROM parts WHERE part_alias = '16poss';
INSERT INTO parts_oss_status (part_id, ssop_label, ssop_mm, height_orig, height_best, height_now) SELECT part_id, 'SSOP370', 370, 32.1, 32.1, 32.1 FROM parts WHERE part_alias = '17poss';
