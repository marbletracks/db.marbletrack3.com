<?php

namespace Media;

class Take
{
    public function __construct(
        public int $take_id,
        public string $take_name
    ) {
    }
}
