<?php
namespace Domain;

use Database\DbInterface;
use Database\MomentRepository;
use Media\Moment;

trait HasMoments
{
    private array $moments = [];

    abstract public function getId(): int;
    abstract public function getDb(): DbInterface;
    abstract public function getMomentLinkingTable(): string;
    abstract public function getPrimaryKeyColumn(): string;

    public function addMoment(Moment $moment): void {
        $this->moments[] = $moment;
    }

    /** @return Moment[] */
    public function getMoments(): array {
        return $this->moments;
    }

    public function loadMoments(?object $perspective = null): void {
        $this->moments = [];
        $table = $this->getMomentLinkingTable();
        $key = $this->getPrimaryKeyColumn();
        $id = $this->getId();

        $perspective_type = null;
        $perspective_id = null;

        if ($perspective) {
            if ($perspective instanceof \Physical\Worker) {
                $perspective_type = 'worker';
                $perspective_id = $perspective->worker_id;
            } elseif ($perspective instanceof \Physical\Part) {
                $perspective_type = 'part';
                $perspective_id = $perspective->part_id;
            }
        }

        $sql = "";
        $params = [];
        $types = "";

        if ($perspective_type && $perspective_id) {
            $sql = "SELECT m.moment_id, m.frame_start, m.frame_end, m.phrase_id, m.take_id, COALESCE(mt.translated_note, m.notes) AS notes, m.moment_date
                    FROM moments m
                    JOIN {$table} p2m ON m.moment_id = p2m.moment_id
                    LEFT JOIN moment_translations mt ON m.moment_id = mt.moment_id AND mt.perspective_entity_type = ? AND mt.perspective_entity_id = ?
                    WHERE p2m.{$key} = ?
                    ORDER BY p2m.sort_order ASC";
            $types = 'sii';
            $params = [$perspective_type, $perspective_id, $id];
        } else {
            // Fallback to original query if no perspective is provided
            $sql = "SELECT m.moment_id, m.frame_start, m.frame_end, m.phrase_id, m.take_id, m.notes, m.moment_date
                    FROM moments m
                    JOIN {$table} p2m ON m.moment_id = p2m.moment_id
                    WHERE p2m.{$key} = ?
                    ORDER BY p2m.sort_order ASC";
            $types = 'i';
            $params = [$id];
        }

        $results = $this->getDb()->fetchResults($sql, $types, $params);

        $momentRepo = new MomentRepository($this->getDb());
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            // The 'notes' field in the result set is now the correct translated version.
            // We can use the standard findById to hydrate, but we need to override the notes.
            // Actually, it's easier to just hydrate from the row since we have all the data.
            $moment_id = (int)$results->data['moment_id'];
            $momentRepo->setMomentId($moment_id);
            
            // Manually create the moment object to ensure the correct 'notes' field is used.
            $moment = new \Media\Moment(
                moment_id: $moment_id,
                frame_start: isset($results->data['frame_start']) ? (int)$results->data['frame_start'] : null,
                frame_end: isset($results->data['frame_end']) ? (int)$results->data['frame_end'] : null,
                phrase_id: isset($results->data['phrase_id']) ? (int)$results->data['phrase_id'] : null,
                take_id: isset($results->data['take_id']) ? (int)$results->data['take_id'] : null,
                notes: $results->data['notes'] ?? null,
                moment_date: $results->data['moment_date'] ?? null
            );
            // Manually load photos for the moment
            $momentRepo->loadPhotos();
            $moment->photos = $momentRepo->getPhotos();

            $this->addMoment($moment);
        }
    }

    public function saveMoments(array $moment_ids): void {
        $table = $this->getMomentLinkingTable();
        $key = $this->getPrimaryKeyColumn();
        $id = $this->getId();

        $this->getDb()->executeSQL("DELETE FROM {$table} WHERE {$key} = ?", 'i', [$id]);

        foreach ($moment_ids as $index => $moment_id) {
            $this->getDb()->executeSQL(
                "INSERT INTO {$table} ({$key}, moment_id, sort_order) VALUES (?, ?, ?)",
                'iii',
                [$id, (int)$moment_id, $index]
            );
        }
    }
}
