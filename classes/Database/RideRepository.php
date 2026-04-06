<?php
namespace Database;

use Physical\Ride;

class RideRepository
{
    private DbInterface $db;

    public function __construct(DbInterface $db, string $langCode = 'en')
    {
        $this->db = $db;
    }

    /** @return Ride[] */
    public function findAll(): array
    {
        $results = $this->db->fetchResults(
            "SELECT ride_id, ride_alias, ride_name, ride_description, ride_tagline, marble_size, is_complete
             FROM rides ORDER BY ride_id ASC"
        );

        $rides = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $ride = $this->hydrate($results->data);
            $this->loadTracks($ride);
            $rides[] = $ride;
        }
        return $rides;
    }

    public function findByAlias(string $alias): ?Ride
    {
        $results = $this->db->fetchResults(
            "SELECT ride_id, ride_alias, ride_name, ride_description, ride_tagline, marble_size, is_complete
             FROM rides WHERE ride_alias = ?",
            's', [$alias]
        );

        if ($results->numRows() === 0) return null;
        $results->setRow(0);
        $ride = $this->hydrate($results->data);
        $this->loadTracks($ride);
        return $ride;
    }

    private function loadTracks(Ride $ride): void
    {
        $results = $this->db->fetchResults(
            "SELECT rt.sequence_order, rt.experience_note,
                    t.track_id, t.track_alias, t.track_name, t.track_description,
                    t.marble_sizes_accepted
             FROM ride_tracks rt
             JOIN tracks t ON rt.track_id = t.track_id
             WHERE rt.ride_id = ?
             ORDER BY rt.sequence_order ASC",
            'i', [$ride->ride_id]
        );

        $tracks = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $tracks[] = (object) [
                'sequence_order'        => (int) $results->data['sequence_order'],
                'track_id'              => (int) $results->data['track_id'],
                'track_alias'           => $results->data['track_alias'],
                'track_name'            => $results->data['track_name'],
                'track_description'     => $results->data['track_description'],
                'experience_note'       => $results->data['experience_note'],
                'marble_sizes_accepted' => $results->data['marble_sizes_accepted'],
            ];
        }
        $ride->tracks = $tracks;
    }

    private function hydrate(array $row): Ride
    {
        return new Ride(
            ride_id: (int) $row['ride_id'],
            ride_alias: $row['ride_alias'],
            name: $row['ride_name'] ?? '',
            description: $row['ride_description'] ?? '',
            tagline: $row['ride_tagline'] ?? '',
            marble_size: $row['marble_size'] ?? '',
            is_complete: (bool) ($row['is_complete'] ?? false),
        );
    }
}
