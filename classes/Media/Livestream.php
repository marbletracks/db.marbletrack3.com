<?php
namespace Media;

final readonly class Livestream
{
    public function __construct(
        public int $livestream_id,
        public string $youtube_video_id,
        public string $title,
        public string $description,
        public ?string $published_at,
        public string $status,
        public string $created_at
    ) {
    }
}
