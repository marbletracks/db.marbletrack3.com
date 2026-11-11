<?php
namespace Physical;

/**
 * Not readonly because photos are added by the Repo
 */
class Worker
{
    public string $slug;
    /** @var \Media\Photo[] */
    public array $photos = [];     // added by Repository during hydrate
    /** @var \Media\Moment[] */
    public array $moments = [];     // added by Repository during hydrate
    /** @var Token[] */
    public array $tokens = [];      // set by the realtime Moment editor
    public function __construct(
        public int $worker_id,
        public string $worker_alias,
        public int $busy_sort,
        public string $name = '',
        public string $description = '',
        public ?string $primary_image_url = null,
    ) {
        $this->slug = \Utilities::slugify($name);
    }
}
