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

        // If there's no perspective, we cannot load any moments.
        if (!$perspective_type || !$perspective_id) {
            return;
        }

        // This query now uses the existence of a translation as the source of truth,
        // bypassing the `*_2_moments` linking tables.
        // NOTE: This loses the custom `sort_order` from the linking tables. Ordering by moment_id as a fallback.
        $sql = "SELECT m.moment_id, m.frame_start, m.frame_end, m.phrase_id, m.take_id, COALESCE(mt.translated_note, m.notes) AS notes, m.moment_date, mt.is_significant
                FROM moments m
                JOIN moment_translations mt ON m.moment_id = mt.moment_id
                WHERE mt.perspective_entity_type = ? AND mt.perspective_entity_id = ?
                ORDER BY m.take_id ASC, m.frame_start ASC";
        $types = 'si';
        $params = [$perspective_type, $perspective_id];

        // 1. Fetch all moment data into a temporary array
        $results = $this->getDb()->fetchResults($sql, $types, $params);
        $moment_data_rows = [];
        $moment_ids = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $moment_data_rows[] = $results->data;
            $moment_ids[] = (int)$results->data['moment_id'];
        }

        if (empty($moment_data_rows)) {
            return; // No moments, nothing to do
        }

        // 2. Fetch all photos for all moments in a single query
        $photo_sql = "SELECT p.photo_id, p.code, p.url, m2p.moment_id
                      FROM photos p
                      JOIN moments_2_photos m2p ON p.photo_id = m2p.photo_id
                      WHERE m2p.moment_id IN (" . implode(',', array_fill(0, count($moment_ids), '?')) . ")";
        
        $photo_results = $this->getDb()->fetchResults($photo_sql, str_repeat('i', count($moment_ids)), $moment_ids);

        // 3. Group photos by moment_id
        $photos_by_moment_id = [];
        for ($i = 0; $i < $photo_results->numRows(); $i++) {
            $photo_results->setRow($i);
            $photo_data = $photo_results->data;
            $moment_id = (int)$photo_data['moment_id'];
            if (!isset($photos_by_moment_id[$moment_id])) {
                $photos_by_moment_id[$moment_id] = [];
            }
            $photos_by_moment_id[$moment_id][] = new \Media\Photo(
                photo_id: (int) $photo_data['photo_id'],
                code: $photo_data['code'] ?? null,
                url: $photo_data['url'] ?? null
            );
        }

        // 4. Create Moment objects and attach photos
        foreach ($moment_data_rows as $row) {
            $moment_id = (int)$row['moment_id'];
            $moment = new \Media\Moment(
                moment_id: $moment_id,
                frame_start: isset($row['frame_start']) ? (int)$row['frame_start'] : null,
                frame_end: isset($row['frame_end']) ? (int)$row['frame_end'] : null,
                phrase_id: isset($row['phrase_id']) ? (int)$row['phrase_id'] : null,
                take_id: isset($row['take_id']) ? (int)$row['take_id'] : null,
                notes: $row['notes'] ?? null,
                moment_date: $row['moment_date'] ?? null
            );
            $moment->is_significant = (bool)($row['is_significant'] ?? false);
            $moment->photos = $photos_by_moment_id[$moment_id] ?? [];
            $this->addMoment($moment);
        }
    }

    public function saveMoments(array $moment_ids): void {
        $table = $this->getMomentLinkingTable();
        
        // If the linking table is empty, this entity does not use this method.
        if (empty($table)) {
            return;
        }

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
