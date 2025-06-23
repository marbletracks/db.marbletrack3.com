<?php

namespace Domain;

use Database\DbInterface;
use Database\PhotoRepository;
use Media\Photo;

trait HasPhotos
{
    private array $photos = []; // array of Photo
    private ?Photo $primaryPhoto = null;

    abstract public function getId(): int;
    abstract public function getDb(): DbInterface;
    abstract public function getPhotoLinkingTable(): string;
    abstract public function getPrimaryKeyColumn(): string;

    public function addPhoto(Photo $photo, bool $isPrimary = false): void {
        $this->photos[] = $photo;
        if ($isPrimary || $this->primaryPhoto === null) {
            $this->primaryPhoto = $photo;
        }
    }

    /** @return Photo[] */
    public function getPhotos(): array {
        return $this->photos;
    }

    public function getPrimaryPhoto(): ?Photo {
        return $this->primaryPhoto;
    }

    public function loadPhotos(): void {
        $table = $this->getPhotoLinkingTable();
        $key = $this->getPrimaryKeyColumn();
        $id = $this->getId();

        $r = $this->getDb()->fetchResults(
            "SELECT photo_id, is_primary FROM {$table} WHERE {$key} = ? ORDER BY photo_sort ASC, photo_id ASC",
            'i',
            [$id]
        );

        $photoIds = array_column($r->data, 'photo_id');
        $photos = (new PhotoRepository($this->getDb()))->findByIds($photoIds);

        foreach ($r->data as $row) {
            $pid = (int)$row['photo_id'];
            $photo = $photos[$pid] ?? null;
            if ($photo) {
                $this->addPhoto($photo, !empty($row['is_primary']));
            }
        }
    }

    /**
     * @param Photo[] $photos
     */
    public function savePhotos(array $photos): void {
        $table = $this->getPhotoLinkingTable();
        $key = $this->getPrimaryKeyColumn();
        $id = $this->getId();

        $this->getDb()->executeSQL("DELETE FROM {$table} WHERE {$key} = ?", 'i', [$id]);

        $sort = 0;
        foreach ($photos as $photo) {
            $this->getDb()->executeSQL(
                "INSERT INTO {$table} ({$key}, photo_id, photo_sort, is_primary) VALUES (?, ?, ?, ?)",
                'iiii',
                [$id, $photo->photo_id, $sort, ($photo === $this->primaryPhoto ? 1 : 0)]
            );
            $sort++;
        }

        $this->photos = $photos;
    }
}



/*   OLD not used since 23 June 2025:
namespace Domain;

trait HasPhotos
{
    public function getURLForCode(string $code): string
    {
        if (empty($code)) {
            return '';
        }

        // If the code is a URL, return it directly
        if (filter_var($code, FILTER_VALIDATE_URL)) {
            return $code;
        }

        // Otherwise, assume it's a local file path and construct the URL
        return "https://d2f8m59m4mubfx.cloudfront.net/W240/{$code}.jpg";
    }
    public function thumbnailFor(string $url, int $maxWidth = 200): string
    {
        if (strpos($url, 'thumb') !== false || strpos($url, 'thumbnail') !== false) {
            return $url;
        }

        if (strpos($url, 'b.robnugen') !== false) {
            if (preg_match('/.*?(_1000)?\./', $url)) {
                return preg_replace('/(_1000)?\./', '.', $url);
            }

            $parts = pathinfo($url);
            return $parts['dirname'] . '/' .
                    $parts['filename'] .
                    '_thumb.' .
                    $parts['extension'];
        }

        // if string includes cloudfront.net start a block
        if (strpos(haystack: $url, needle: 'cloudfront.net') !== false) {
            // Split after cloudfront.net/ and insert W$maxWidth after cloudfront.net/
            $parts = explode(separator: 'cloudfront.net/', string: $url);
            if (count(value: $parts) > 1) {
                return "https://d2f8m59m4mubfx.cloudfront.net/W{$maxWidth}/{$parts[1]}";
            }
        }

        return $url; // fallback
    }
}
    */
