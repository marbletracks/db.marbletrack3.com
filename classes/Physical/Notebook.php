<?php

namespace Physical;
class Notebook
{
    public function __construct(
        public int $notebook_id,
        public ?string $title,
        public ?string $created_at
    ) {
    }
}
