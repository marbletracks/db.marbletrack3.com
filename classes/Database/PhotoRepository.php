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

        // make sure photoIds are all integers
        $photoIds = array_map(callback: 'intval', array: $photoIds);
        // get photo
        $photoIds = implode(separator: ',', array: $photoIds);
        $results = $this->db->fetchResults(
            sql: "SELECT photo_id, code, url FROM photos WHERE photo_id IN ($photoIds)"
        );

        $results->toArray();
        $photos = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $photos[] = $this->hydrate($results->data);
        }
        return $photos;
    }

    /** Used when saving photos on a page with user-facing URLs
     * but we need id of photo
     *
     * @param string[] $urls
     * @return Photo[]
     */
    public function findByUrls(array $urls): array
    {
        if (empty($urls)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($urls), '?'));
        $types = str_repeat('s', count($urls));

        // Flatten for bind parameters
        $results = $this->db->fetchResults(
            "SELECT photo_id, code, url FROM photos WHERE url IN ($placeholders)",
            $types,
            $urls
        );

        $results->toArray();
        $photos = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $photos[] = $this->hydrate($results->data);
        }
        return $photos;
    }

    /** @param string[] $urls
     *  @return Photo[]
     */
    public function findOrCreateByUrls(array $urls): array
    {
        if (empty($urls))
            return [];

        // First, find existing
        $existingPhotos = $this->findByUrls($urls);
        $existingMap = [];
        foreach ($existingPhotos as $photo) {
            $existingMap[$photo->getUrl()] = $photo;
        }

        $result = [];
        foreach ($urls as $url) {
            if (isset($existingMap[$url])) {
                $result[] = $existingMap[$url];
            } else {
                // Insert new photo record
                $this->db->executeSQL(
                    "INSERT INTO photos (url) VALUES (?)",
                    's',
                    [$url]
                );
                $photoId = $this->db->insertId();

                $newPhoto = new Photo($photoId, null, $url);
                $result[] = $newPhoto;
            }
        }

        return $result;
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
