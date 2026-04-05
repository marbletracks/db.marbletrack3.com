<?php
namespace Physical;

class Marble
{
    public string $slug;

    public function __construct(
        public int $marble_id,
        public string $marble_alias,
        public string $marble_name,
        public ?string $team_name = null,
        public string $size = 'small',
        public string $color = '',
        public int $quantity = 1,
        public ?string $description = null,
    ) {
        $this->slug = \Utilities::slugify($marble_name);
    }
}
