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

        $placeholders = implode(',', array_fill(0, count($photoIds), '?'));
        $types = str_repeat('i', count($photoIds));
        $r = $this->db->fetchResults("SELECT photo_id, code, url FROM photos WHERE photo_id IN ($placeholders)", $types, $photoIds);

        $photos = [];
        foreach ($r->data as $row) {
            $photos[(int) $row['photo_id']] = new Photo((int) $row['photo_id'], $row['code'], $row['url']);
        }
        return $photos;
    }
}
