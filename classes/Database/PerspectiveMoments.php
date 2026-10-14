<?php
namespace Database;

use Media\Moment;
use Media\Photo;

/**
 * Moments seen from Workers', Parts' or Marbles' perspectives, for many of
 * them at once. A moment belongs to a perspective when it has a translation
 * for it; the *_2_moments linking tables are not used.
 */
class PerspectiveMoments
{
    public function __construct(private DbInterface $db)
    {
    }

    /**
     * @param string $type 'worker', 'part' or 'marble'
     * @param int[]  $ids
     * @return array<int, Moment[]> keyed by perspective ID; ordered by take, then frame
     */
    public function forPerspectives(string $type, array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = $this->db->fetchResults(
            "SELECT mt.perspective_entity_id AS perspective_id, m.moment_id, m.frame_start, m.frame_end, m.take_id,
                    COALESCE(mt.translated_note, m.notes) AS notes, m.moment_date, mt.is_significant
             FROM moments m
             JOIN moment_translations mt ON m.moment_id = mt.moment_id
             WHERE mt.perspective_entity_type = ? AND mt.perspective_entity_id IN ($placeholders)
             ORDER BY m.take_id ASC, m.frame_start ASC",
            's' . str_repeat('i', count($ids)),
            array_merge([$type], $ids)
        )->toArray();
        if (empty($rows)) {
            return [];
        }

        $photosByMoment = $this->photosFor(array_map(fn (array $row) => (int) $row['moment_id'], $rows));

        $byPerspective = [];
        foreach ($rows as $row) {
            $moment = new Moment(
                moment_id: (int) $row['moment_id'],
                frame_start: isset($row['frame_start']) ? (int) $row['frame_start'] : null,
                frame_end: isset($row['frame_end']) ? (int) $row['frame_end'] : null,
                take_id: isset($row['take_id']) ? (int) $row['take_id'] : null,
                notes: $row['notes'] ?? null,
                moment_date: $row['moment_date'] ?? null
            );
            $moment->is_significant = (bool) ($row['is_significant'] ?? false);
            $moment->photos = $photosByMoment[$moment->moment_id] ?? [];
            $byPerspective[(int) $row['perspective_id']][] = $moment;
        }
        return $byPerspective;
    }

    /** @return array<int, Photo[]> */
    private function photosFor(array $momentIds): array
    {
        $momentIds = array_values(array_unique($momentIds));
        $placeholders = implode(',', array_fill(0, count($momentIds), '?'));
        $rows = $this->db->fetchResults(
            "SELECT p.photo_id, p.code, p.url, m2p.moment_id
             FROM photos p
             JOIN moments_2_photos m2p ON p.photo_id = m2p.photo_id
             WHERE m2p.moment_id IN ($placeholders)",
            str_repeat('i', count($momentIds)),
            $momentIds
        )->toArray();

        $photos = [];
        foreach ($rows as $row) {
            $photos[(int) $row['moment_id']][] = new Photo(
                photo_id: (int) $row['photo_id'],
                code: $row['code'] ?? null,
                url: $row['url'] ?? null
            );
        }
        return $photos;
    }
}
