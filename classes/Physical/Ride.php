<?php

namespace Physical;

class Ride
{
    public string $slug;
    public array $photos = [];
    public array $tracks = [];

    public function __construct(
        public int $ride_id,
        public string $ride_alias,
        public string $name = "",
        public string $description = "",
        public string $tagline = "",
        public string $marble_size = "",
        public bool $is_complete = false,
    ) {
        $this->slug = \Utilities::slugify($name);
    }
}
