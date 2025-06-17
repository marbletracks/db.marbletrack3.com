<?php
namespace Physical;

final readonly class Part
{
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
    ) {
    }
}
