<?php
namespace Domain;

trait PartHasRoles
{
    public function isRail(): bool
    {
        return $this->is_rail ?? false;
    }

    public function isSupport(): bool
    {
        return $this->is_support ?? false;
    }

    public function isTrack(): bool
    {
        return $this->is_track ?? false;
    }
}