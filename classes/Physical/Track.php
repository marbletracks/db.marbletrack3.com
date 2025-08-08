<?php
namespace Physical;

use Domain\TrackHasTypes;

final class Track
{
    use TrackHasTypes;

    public string $slug;
    public array $parts = [];         // added by Repository during hydrate
    public array $upstream_tracks = []; // tracks that feed into this track
    public array $downstream_tracks = []; // tracks this track feeds into

    /**
     * Represents a logical track made up of multiple physical parts.
     * Tracks transport marbles via gravity and can split or merge marble flow.
     *
     * @param int $track_id db:tracks.track_id
     * @param string $track_alias db:tracks.track_alias - URL-safe identifier
     * @param string $track_name db:tracks.track_name - Human-readable name
     * @param string $track_description db:tracks.track_description
     * @param array $marble_sizes_accepted db:tracks.marble_sizes_accepted as array
     * @param bool $is_transport Track transports marbles along its length
     * @param bool $is_splitter Track splits marble flow by size or direction
     * @param bool $is_landing_zone Track is a terminal destination
     */
    public function __construct(
        public int $track_id,
        public string $track_alias,
        public string $track_name = "",
        public string $track_description = "",
        public array $marble_sizes_accepted = [],
        public bool $is_transport = false,
        public bool $is_splitter = false,
        public bool $is_landing_zone = false,
    ) {
        $this->slug = \Utilities::slugify($track_name);
    }

    /**
     * Check if this track accepts a specific marble size
     */
    public function acceptsMarbleSize(string $size): bool
    {
        return in_array($size, $this->marble_sizes_accepted);
    }

    /**
     * Get human-readable list of accepted marble sizes
     */
    public function getMarbleSizesDisplay(): string
    {
        return implode(', ', $this->marble_sizes_accepted);
    }

    /**
     * Get track type description for display
     */
    public function getTypeDescription(): string
    {
        $types = [];
        if ($this->isTransport()) $types[] = 'Transport';
        if ($this->isSplitter()) $types[] = 'Splitter';
        if ($this->isLandingZone()) $types[] = 'Landing Zone';

        return implode(' + ', $types);
    }
}
