<?php
namespace Database;

use Domain\HasPhotos;
use Media\Moment;

class MomentRepository
{
    use HasPhotos;
    private DbInterface $db;
    private string $photoLinkingTable = 'moments_2_photos';
    private string $primaryKeyColumn = 'moment_id';
    private int $moment_id;  // Must be set when the Moment is loaded

    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }
    public function getId(): int
    {
        return $this->moment_id;
    }
    // must do this before saving photos
    // so that HasPhotos knows which moment to save photos for
    public function setMomentId(int $moment_id): void
    {
        $this->moment_id = $moment_id;
    }
    public function getDb(): DbInterface
    {
        return $this->db;
    }
    public function getPhotoLinkingTable(): string
    {
        return $this->photoLinkingTable;
    }
    public function getPrimaryKeyColumn(): string
    {
        return $this->primaryKeyColumn;
    }



    public function findById(int $moment_id): ?Moment
    {
        $results = $this->db->fetchResults(
            "SELECT moment_id, frame_start, frame_end, phrase_id, take_id, notes, moment_date FROM moments WHERE moment_id = ?",
            'i',
            [$moment_id]
        );

        if ($results->numRows() === 0) {
            return null;
        }
        $this->moment_id = $moment_id; // Set the moment_id for HasPhotos

        $results->setRow(0);
        return $this->hydrate($results->data);
    }

    public function findAll(): array
    {
        $results = $this->db->fetchResults(
            "SELECT moment_id, frame_start, frame_end, phrase_id, take_id, notes, moment_date FROM moments ORDER BY moment_id ASC"
        );

        $moments = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $this->moment_id = (int) $results->data['moment_id']; // Set the moment_id for HasPhotos
            $moments[] = $this->hydrate($results->data);
        }

        return $moments;
    }

    public function insert(int $frame_start = null, int $frame_end = null, int $phrase_id = null, int $take_id = null, string $notes = null, string $moment_date = null): int
    {
        return $this->db->insertFromRecord(
            'moments',
            'iiiiss',
            [
                'frame_start' => $frame_start,
                'frame_end' => $frame_end,
                'phrase_id' => $phrase_id,
                'take_id' => $take_id,
                'notes' => $notes,
                'moment_date' => $moment_date
            ]
        );
    }

    private function hydrate(array $row): Moment
    {
        $moment = new Moment(
            moment_id: (int) $row['moment_id'],
            frame_start: isset($row['frame_start']) ? (int)$row['frame_start'] : null,
            frame_end: isset($row['frame_end']) ? (int)$row['frame_end'] : null,
            phrase_id: isset($row['phrase_id']) ? (int)$row['phrase_id'] : null,
            take_id: isset($row['take_id']) ? (int)$row['take_id'] : null,
            notes: $row['notes'] ?? null,
            moment_date: $row['moment_date'] ?? null
        );
        $this->loadPhotos();  // defined in HasPhotos trait
        $moment->photos = $this->getPhotos();  // return an array of photos
        return $moment;
    }
}
