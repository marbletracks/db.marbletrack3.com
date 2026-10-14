<?php

namespace Physical;

/**
 * Not readonly because photos are added by the Repo
 */
class Notebook
{
    public array $photos = []; // Media\Photo[]; filled by NotebookRepository

    public function __construct(
        public int $notebook_id,
        public ?string $title,
        public ?string $created_at
    ) {
    }
}
