<?php
namespace Physical;

/**
 * Not readonly because photos are added by the Repo
 */
class Worker
{
    public string $slug;
    public array $photos = [];
    public array $moments = [];

    public function __construct(
        public int $worker_id,
        public string $worker_alias,
        public string $name = '',
        public string $description = '',
        public ?string $primary_image_url = null,
    ) {
        $this->slug = \Utilities::slugify($name);
    }
}
