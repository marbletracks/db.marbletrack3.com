<?php

namespace Media;

/**
 * Not readonly because photos are added by the Repo
 */
class Moment
{
    public array $photos = [];
    public function __construct(
        public int $moment_id,
        public ?int $frame_start,
        public ?int $frame_end,
        public ?int $take_id,
        public ?string $notes,
        public ?string $moment_date
    ) {
    }
}
