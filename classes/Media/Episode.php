<?php
namespace Media;

final class Episode
{
    public function __construct(
        public int $episode_id,
        public string $title,
        public string $episode_english_description,
        public string $episode_frames,
        public ?int $livestream_id,
        public string $created_at
    ) {
    }
}
