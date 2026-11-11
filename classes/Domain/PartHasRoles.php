<?php
namespace Domain;

trait PartHasRoles
{
    public function isRail(): bool
    {
        return $this->is_rail;
    }

    public function isSupport(): bool
    {
        return $this->is_support;
    }

    public function isTrack(): bool
    {
        return $this->is_track;
    }
}