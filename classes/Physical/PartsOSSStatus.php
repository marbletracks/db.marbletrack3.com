<?php
namespace Physical;

final readonly class PartsOSSStatus
{
    public function __construct(
        public int $parts_oss_status_id,
        public int $part_id,
        public string $ssop_label,
        public float $ssop_mm,
        public float $height_orig,
        public float $height_best,
        public ?float $height_now,
        public string $last_updated,
    ) {
    }
}
