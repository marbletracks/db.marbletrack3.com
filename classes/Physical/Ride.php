<?php

namespace Physical;

class Ride
{
    public string $slug;
    /** @var \Media\Photo[] */
    public array $photos = [];
    /**
     * One per stop, in ride order: a row of ride_tracks joined to its track.
     * @var array<int, object{sequence_order: int, track_id: int, track_alias: string, track_name: string, technical_description: ?string, visitor_description: ?string, experience_note: ?string, marble_sizes_accepted: ?string}>
     */
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
