<?php
namespace Media;

final readonly class Episode
{
    public function __construct(
        public int $episode_id,
        public string $title,
        public string $episode_english_description,
        public ?int $livestream_id,
        public string $created_at
    ) {
    }
}
