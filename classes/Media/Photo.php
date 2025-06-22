<?php
namespace Media;

/**
 * code is a FS code (preferred (for thumbnailing magic))
 * url is b.robnugen.com URL with manual thumbnails (99.9% usage now)
 * maybe can user fewer urls in future
 */
class Photo
{
    private string $cdnPrefix = 'https://d2f8m59m4mubfx.cloudfront.net';
    public function __construct(
        public readonly int $photo_id,
        public readonly ?string $code,
        public readonly ?string $url
    ) {
    }

    public function getUrl(): string
    {
        return $this->code ? "{$this->cdnPrefix}/{$this->code}" : $this->url ?? '';
    }
}
