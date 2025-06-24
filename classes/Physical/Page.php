<?php

namespace Physical;
class Page
{
    public function __construct(
        public int $page_id,
        public int $notebook_id,
        public string $number,
        public string $created_at,
    ) {
    }
}
