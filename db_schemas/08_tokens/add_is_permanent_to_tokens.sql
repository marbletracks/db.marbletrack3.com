ALTER TABLE `tokens`
ADD COLUMN `is_permanent` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 for permanent, 0 for not';
