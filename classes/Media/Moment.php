<?php

namespace Media;

/**
 * Not readonly because photos are added by the Repo
 */
class Moment
{
    public array $photos = [];
    public string $slug;
    
    public function __construct(
        public int $moment_id,
        public ?int $frame_start,
        public ?int $frame_end,
        public ?int $take_id,
        public ?string $notes,
        public ?string $moment_date
    ) {
        // Generate a meaningful slug for URL generation
        $slug_parts = [];
        if ($this->take_id) {
            $slug_parts[] = "take-{$this->take_id}";
        }
        if ($this->frame_start) {
            $slug_parts[] = "frame-{$this->frame_start}";
        }
        if (empty($slug_parts)) {
            $slug_parts[] = "moment-{$this->moment_id}";
        }
        $this->slug = implode('-', $slug_parts);
    }
}
