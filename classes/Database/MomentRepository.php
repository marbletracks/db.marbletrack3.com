<?php
namespace Database;

use Domain\HasPhotos;
use Media\Moment;

class MomentRepository
{
    use HasPhotos;
    private DbInterface $db;
    private string $langCode;
    private string $photoLinkingTable = 'moments_2_photos';
    private string $primaryKeyColumn = 'moment_id';
    private int $moment_id;  // Must be set when the Moment is loaded

    public function __construct(DbInterface $db, string $langCode = 'en')
    {
        $this->db = $db;
        $this->langCode = $langCode;
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
            "SELECT moment_id, frame_start, frame_end, take_id, notes, moment_date FROM moments WHERE moment_id = ?",
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

    public function findWithinTakeId(int $take_id): array
    {
        $results = $this->db->fetchResults(
            sql: "SELECT
                    moment_id,
                    frame_start,
                    frame_end,
                    take_id,
                    notes,
                    moment_date
                  FROM moments
                  WHERE take_id = ?
                  ORDER BY
                    frame_start ASC",
            paramtypes: 'i',
            var1: [$take_id]
        );

        $moments = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $this->moment_id = (int) $results->data['moment_id']; // Set the moment_id for HasPhotos
            $moments[] = $this->hydrate($results->data);
        }

        return $moments;
    }

    public function findAll(): array
    {
        $results = $this->db->fetchResults(
            sql: "SELECT
                    moment_id,
                    frame_start,
                    frame_end,
                    take_id,
                    notes,
                    moment_date
                  FROM
                    moments
                  ORDER BY
                    take_id ASC,
                    frame_start ASC"
        );

        $moments = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $this->moment_id = (int) $results->data['moment_id']; // Set the moment_id for HasPhotos
            $moments[] = $this->hydrate($results->data);
        }

        return $moments;
    }

    public function findByFilter(string $filter, int $take_id = 0): array
    {
        $filter = trim($filter);
        if (empty($filter)) {
            return $take_id > 0 ? $this->findWithinTakeId($take_id) : $this->findAll();
        }

        // Search both moments.notes and moment_translations.translated_note
        // Priority search:
        // 1. moments.notes LIKE %filter%
        // 2. moment_translations.translated_note LIKE %filter%
        
        $whereClause = "WHERE (m.notes LIKE ? OR mt.translated_note LIKE ?)";
        $params = [
            '%' . $filter . '%',  // priority case 1
            '%' . $filter . '%',  // priority case 2  
            '%' . $filter . '%',  // where case 1
            '%' . $filter . '%'   // where case 2
        ];
        $paramTypes = 'ssss';
        
        // Add take_id filter if provided
        if ($take_id > 0) {
            $whereClause .= " AND m.take_id = ?";
            $params[] = $take_id;
            $paramTypes .= 'i';
        }
        
        $results = $this->db->fetchResults(
            sql: "SELECT DISTINCT
                    m.moment_id,
                    m.frame_start,
                    m.frame_end,
                    m.take_id,
                    m.notes,
                    m.moment_date,
                    CASE 
                        WHEN m.notes LIKE ? THEN 1
                        WHEN mt.translated_note LIKE ? THEN 2
                        ELSE 3
                    END as priority
                  FROM moments m
                  LEFT JOIN moment_translations mt ON m.moment_id = mt.moment_id
                  $whereClause
                  ORDER BY priority ASC, m.take_id ASC, m.frame_start ASC",
            paramtypes: $paramTypes,
            var1: $params
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
            "SELECT perspective_entity_type, perspective_entity_id, translated_note, is_significant FROM moment_translations WHERE moment_id = ?",
            'i',
            [$moment_id]
        );

        $translations = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $row = $results->data;
            $type = $row['perspective_entity_type'];
            $id = (int)$row['perspective_entity_id'];
            $translations[$type][$id] = [
                'note' => $row['translated_note'],
                'is_significant' => (bool)$row['is_significant'],
            ];
        }

        return $translations;
    }

    /**
     * Created by Gemini when using moment_translations for Parts as well as Workers
     * @param int $entity_id
     * @param string $entity_type
     * @return int[]
     */
    public function findMomentIdsByEntity(int $entity_id, string $entity_type): array
    {
        $results = $this->db->fetchResults(
            "SELECT moment_id FROM moment_translations WHERE perspective_entity_id = ? AND perspective_entity_type = ?",
            'is',
            [$entity_id, $entity_type]
        );

        $moment_ids = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $moment_ids[] = (int) $results->data['moment_id'];
        }

        return $moment_ids;
    }

    public function createTranslationIfNotExists(int $moment_id, int $perspective_id, string $perspective_type): void
    {
        // Check if a translation already exists
        $r = $this->db->fetchResults(
            "SELECT 1 FROM moment_translations WHERE moment_id = ? AND perspective_entity_id = ? AND perspective_entity_type = ?",
            'iis',
            [$moment_id, $perspective_id, $perspective_type]
        );

        if ($r->numRows() > 0) {
            return; // Translation already exists, do nothing
        }

        // Get the original moment notes to use as a default
        $moment = $this->findById($moment_id);
        if (!$moment || !$moment->notes) {
            return; // Cannot create a translation without a default note
        }

        // Insert the new default translation
        $this->db->insertFromRecord(
            'moment_translations',
            'isss',
            [
                'moment_id' => $moment_id,
                'perspective_entity_id' => $perspective_id,
                'perspective_entity_type' => $perspective_type,
                'translated_note' => $moment->notes,
            ]
        );
    }

    public function updateSignificance(int $moment_id, int $perspective_id, string $perspective_type, bool $is_significant): void
    {
        $this->db->executeSQL(
            "UPDATE moment_translations SET is_significant = ? WHERE moment_id = ? AND perspective_entity_id = ? AND perspective_entity_type = ?",
            'iiis',
            [(int)$is_significant, $moment_id, $perspective_id, $perspective_type]
        );
    }

    public function deleteTranslation(int $moment_id, int $perspective_id, string $perspective_type): void
    {
        $this->db->executeSQL(
            "DELETE FROM moment_translations WHERE moment_id = ? AND perspective_entity_id = ? AND perspective_entity_type = ?",
            'iis',
            [$moment_id, $perspective_id, $perspective_type]
        );
    }

    public function findByPartId(int $part_id): array
    {
        $results = $this->db->fetchResults(
            "SELECT m.moment_id, m.frame_start, m.frame_end, m.take_id, m.notes, m.moment_date
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

    public function insert(int $frame_start = null, int $frame_end = null, int $take_id = null, string $notes = null, string $moment_date = null): int
    {
        return $this->db->insertFromRecord(
            'moments',
            'iisss',
            [
                'frame_start' => $frame_start,
                'frame_end' => $frame_end,
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
            foreach ($entities as $id => $data) {
                $note = $data['note'] ?? null;
                if (!empty($note)) { // Only insert if the note is not empty
                    $is_significant = (int)($data['is_significant'] ?? 0);
                    $this->db->insertFromRecord(
                        'moment_translations',
                        'isssi',
                        [
                            'moment_id' => $moment_id,
                            'perspective_entity_id' => (int)$id,
                            'perspective_entity_type' => $type,
                            'translated_note' => $note,
                            'is_significant' => $is_significant,
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
            take_id: isset($row['take_id']) ? (int)$row['take_id'] : null,
            notes: $row['notes'] ?? null,
            moment_date: $row['moment_date'] ?? null
        );
        $this->loadPhotos();  // defined in HasPhotos trait
        $moment->photos = $this->getPhotos();  // return an array of photos
        return $moment;
    }

    public function findLatestForWorker(int $worker_id, int $limit = 2): array
    {
        $sql = "SELECT m.moment_id, m.frame_start, m.frame_end, m.take_id, COALESCE(mt.translated_note, m.notes) AS notes, m.moment_date, mt.is_significant
                FROM moments m
                JOIN moment_translations mt ON m.moment_id = mt.moment_id
                WHERE mt.perspective_entity_type = 'worker' AND mt.perspective_entity_id = ?
                ORDER BY m.take_id DESC, m.frame_start DESC
                LIMIT ?";

        $results = $this->getDb()->fetchResults($sql, 'ii', [$worker_id, $limit]);

        $moments = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            // Using a simplified hydration here to avoid re-fetching photos etc.
            $moments[] = new \Media\Moment(
                moment_id: (int)$results->data['moment_id'],
                frame_start: isset($results->data['frame_start']) ? (int)$results->data['frame_start'] : null,
                frame_end: isset($results->data['frame_end']) ? (int)$results->data['frame_end'] : null,
                take_id: isset($results->data['take_id']) ? (int)$results->data['take_id'] : null,
                notes: $results->data['notes'] ?? null,
                moment_date: $results->data['moment_date'] ?? null
            );
        }
        return array_reverse($moments);
    }

    public function findSimilarMoments(int $worker_id, int $frame_start, int $frame_end, int $frame_buffer = 5): array
    {
        $sql = "SELECT m.*
                FROM moments m
                JOIN moment_translations mt ON m.moment_id = mt.moment_id
                WHERE mt.perspective_entity_type = 'worker'
                  AND mt.perspective_entity_id = ?
                  AND m.frame_start <= (? + ?)
                  AND m.frame_end >= (? - ?)
                ORDER BY m.frame_start ASC";

        $results = $this->db->fetchResults(
            $sql,
            'iiiii',
            [$worker_id, $frame_end, $frame_buffer, $frame_start, $frame_buffer]
        );

        $moments = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $this->moment_id = (int) $results->data['moment_id'];
            $moments[] = $this->hydrate($results->data);
        }

        return $moments;
    }
}
