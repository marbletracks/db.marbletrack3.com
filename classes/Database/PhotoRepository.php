<?php
namespace Database;

use Database\DbInterface;
use Media\Photo;

/**
 * PhotoRepository saves photos for workers, parts, and notebooks, etc.
 * It does *not* itself have photos (yet) so does not use trait HasPhotos.
 * But trait HasPhotos will use this class I think.
 */
class PhotoRepository
{
    public function __construct(private DbInterface $db)
    {
    }

    public function findById(int $photoId): ?Photo
    {
        $r = $this->db->fetchResults("SELECT photo_id, code, url FROM photos WHERE photo_id = ?", 'i', [$photoId]);
        if ($r->numRows() === 0)
            return null;

        $d = $r->data[0];
        return new Photo((int) $d['photo_id'], $d['code'], $d['url']);
    }

    /** @param int[] $photoIds */
    public function findByIds(array $photoIds): array
    {
        if (empty($photoIds))
            return [];

        print_rob("Finding photos by IDs: " . implode(',', $photoIds) . "\n");
        $placeholders = implode(',', array_fill(0, count($photoIds), '?'));
        $types = str_repeat('i', count($photoIds));
        $results = $this->db->fetchResults("SELECT photo_id, code, url FROM photos WHERE photo_id IN ($placeholders)", $types, [$photoIds]);

        $results->toArray();
        $photos = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $photos[] = $this->hydrate($results->data);
        }
        return $photos;
    }

    public function findAll(): array
    {
        $results = $this->db->fetchResults("SELECT photo_id, code, url FROM photos ORDER BY photo_id DESC");
        $results->toArray();
        $photos = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $photos[] = $this->hydrate($results->data);
        }
        return $photos;
    }
    private function hydrate(array $data): Photo
    {
        return new Photo(
            photo_id: (int) $data['photo_id'],
            code: $data['code'] ?? null,
            url: $data['url'] ?? null
        );
    }
}
