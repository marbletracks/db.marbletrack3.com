<?php
namespace Physical;

use Domain\PartHasRoles;
final class Part
{
    use PartHasRoles;
    public string $slug;

    /**
     * Represents a physical part of the track itself.
     *
     * @param int $part_id db:parts.part_id
     * @param string $part_alias db.parts.part_alias
     * @param string $name db:part_translations.name
     * @param string $description db:part_translations.description
     * @param string|null $primary_thumbnail from either parts_photos.photo_code or part_image_urls.image_url
     */
    public function __construct(
        public int $part_id,
        public string $part_alias,
        public string $name = "",
        public string $description = "",
        public ?string $primary_image_url = null,
        public bool $is_rail = false,
        public bool $is_support = false,
        public bool $is_track = false,
    ) {
        $this->slug = \Utilities::slugify($name);
    }
}
