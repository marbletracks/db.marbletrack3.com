<?php
namespace Media;

/**
 * Photos are for Worker pictures, Episode images, etc.
 * Photos are NOT Frames
 *
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

    public function getThumbnailUrl(): string
    {
        if ($this->code) {
            return "{$this->cdnPrefix}/W100/{$this->code}.jpg";
        }

        // convert https://b.robnugen.com/art/marble_track_3/construction/2025/2025_jun_23_this_blue_a5_notebook_has_notes_on_what_everyone_did_1000.jpeg
        // to https://b.robnugen.com/art/marble_track_3/construction/2025/thumbs/2025_jun_23_this_blue_a5_notebook_has_notes_on_what_everyone_did.jpeg
        if ($this->url) {
            $parts = explode('/', $this->url);
            $parts[count($parts) - 1] = 'thumbs/' . $parts[count($parts) - 1];
            $thumb_url = implode('/', $parts);
        }
        // remove _1000 from just before extension
        if ($thumb_url) {
            $thumb_url = preg_replace('/(_1000)(\.\w+)$/', '$2', $thumb_url);
        }
        return $thumb_url;
    }
}
