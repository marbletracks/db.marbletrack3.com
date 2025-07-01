<?php

namespace Physical;

use Media\Photo;

class Page
{
    public array $photos = [];
    public ?Photo $primaryPhoto = null;

    public function __construct(
        public int $page_id,
        public int $notebook_id,
        public string $number,
        public string $created_at,
    ) {
    }
}
