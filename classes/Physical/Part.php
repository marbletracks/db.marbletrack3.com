<?php
namespace Physical;

use Domain\PartHasRoles;
final readonly class Part
{
    use PartHasRoles;
    /**
     * Represents a physical part of the track itself.
     *
     * @param int $part_id db:parts.part_id
     * @param string $part_alias db.parts.part_alias
     * @param string $name db:part_translations.name
     * @param string $description db:part_translations.description
     */
    public function __construct(
        public int $part_id,
        public string $part_alias,
        public string $name = "",
        public string $description = "",
        public bool $is_rail = false,
        public bool $is_support = false,
        public bool $is_track = false,
    ) {
    }
}
