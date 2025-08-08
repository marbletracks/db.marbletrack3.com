<?php
namespace Domain;

trait TrackHasTypes
{
    public function isTransport(): bool
    {
        return $this->is_transport ?? false;
    }

    public function isSplitter(): bool
    {
        return $this->is_splitter ?? false;
    }

    public function isLandingZone(): bool
    {
        return $this->is_landing_zone ?? false;
    }
}