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

    public function findAllForEntity(string $name, string $alias): array
    {
        $allMoments = $this->findAll();
        $prioritized = [];
        $other = [];

        foreach ($allMoments as $moment) {
            $note = strtolower($moment->notes ?? '');
            $name = strtolower($name);
            $alias = strtolower($alias);

            if (str_contains($note, $name) || str_contains($note, $alias)) {
                $prioritized[] = $moment;
            } else {
                $other[] = $moment;
            }
        }

        return [$prioritized, $other];
    }

    public function findTranslations(int $moment_id): array
    {
        $results = $this->db->fetchResults(
            "SELECT perspective_entity_type, perspective_entity_id, translated_note FROM moment_translations WHERE moment_id = ?",
            'i',
            [$moment_id]
        );

        $translations = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $row = $results->data;
            $type = $row['perspective_entity_type'];
            $id = (int)$row['perspective_entity_id'];
            $translations[$type][$id] = $row['translated_note'];
        }

        return $translations;
    }

    public function findByPartId(int $part_id): array
    {
        $results = $this->db->fetchResults(
            "SELECT m.moment_id, m.frame_start, m.frame_end, m.phrase_id, m.take_id, m.notes, m.moment_date 
            FROM moments m
            JOIN parts_2_moments p2m ON m.moment_id = p2m.moment_id
            WHERE p2m.part_id = ? 
            ORDER BY p2m.sort_order ASC",
            'i',
            [$part_id]
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

    public function saveTranslations(int $moment_id, array $perspectives): void
    {
        // First, delete all existing translations for this moment
        $this->db->executeSQL("DELETE FROM moment_translations WHERE moment_id = ?", 'i', [$moment_id]);

        // If there are no perspectives, we're done.
        if (empty($perspectives)) {
            return;
        }

        // Now, insert the new translations
        foreach ($perspectives as $type => $entities) {
            foreach ($entities as $id => $note) {
                if (!empty($note)) { // Only insert if the note is not empty
                    $this->db->insertFromRecord(
                        'moment_translations',
                        'isss',
                        [
                            'moment_id' => $moment_id,
                            'perspective_entity_id' => (int)$id,
                            'perspective_entity_type' => $type,
                            'translated_note' => $note,
                        ]
                    );
                }
            }
        }
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
