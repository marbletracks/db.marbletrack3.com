<?php
namespace Physical;

use Domain\HasPhotos;

final readonly class Worker
{
    use HasPhotos;
    public function __construct(
        public int $worker_id,
        public string $worker_alias,
        public string $name = '',
        public string $description = '',
        public ?string $primary_image_url = null,
    ) {
    }
}
