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
        if ($isPrimary) {
            $photo->isPrimary = true; // Set the primary flag on the photo
        }
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
        $this->photos = [];            // 💡 Clear previously loaded photos
        $this->primaryPhoto = null;    // 💡 Reset primary photo too
        $table = $this->getPhotoLinkingTable();
        $key = $this->getPrimaryKeyColumn();
        $id = $this->getId();

        $results = $this->getDb()->fetchResults(
            "SELECT photo_id, is_primary FROM {$table} WHERE {$key} = ? ORDER BY photo_sort ASC, photo_id ASC",
            'i',
            [$id]
        );
        $results->toArray();
        $photoIds = [];
        $primaryMap = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $photoId = $results->data['photo_id'];
            $photoIds[] = $photoId;
            $primaryMap[$photoId] = !empty($results->data['is_primary']);
        }

        // Get photos from repository (order not guaranteed)
        $photosFromRepo = (new PhotoRepository($this->getDb()))->findByIds($photoIds);

        // Create a map to preserve the sort order
        $photoMap = [];
        foreach ($photosFromRepo as $photo) {
            $photoMap[$photo->photo_id] = $photo;
        }

        // Add photos in the correct sort order and pass through is_primary flag
        foreach ($photoIds as $photoId) {
            $photo = $photoMap[$photoId] ?? null;
            if ($photo) {
                $isPrimary = $primaryMap[$photoId] ?? false;
                $this->addPhoto($photo, $isPrimary);
            }
        }
    }

    public function savePhotosFromUrls(array $urls): void
    {
        $photoRepo = new PhotoRepository($this->getDb());
        $photos = $photoRepo->findOrCreateByUrls($urls);
        $this->savePhotos($photos);
    }

    /**
     * Add photos without deleting existing ones
     * @param Photo[] $photos
     */
    public function addPhotos(array $photos): void {
        $table = $this->getPhotoLinkingTable();
        $key = $this->getPrimaryKeyColumn();
        $id = $this->getId();

        // Get current highest sort value
        $results = $this->getDb()->fetchResults(
            "SELECT MAX(photo_sort) as max_sort FROM {$table} WHERE {$key} = ?",
            'i',
            [$id]
        );
        $maxSort = 0;
        if ($results->numRows() > 0) {
            $results->setRow(0);
            $maxSort = intval($results->data['max_sort'] ?? 0);
        }

        $sort = $maxSort + 1;
        foreach ($photos as $photo) {
            // Check if this photo is already linked to avoid duplicates
            $existingResults = $this->getDb()->fetchResults(
                "SELECT photo_id FROM {$table} WHERE {$key} = ? AND photo_id = ?",
                'ii',
                [$id, $photo->photo_id]
            );

            if ($existingResults->numRows() === 0) {
                // Photo not already linked, add it
                $this->getDb()->executeSQL(
                    "INSERT INTO {$table} ({$key}, photo_id, photo_sort, is_primary) VALUES (?, ?, ?, ?)",
                    'iiii',
                    [$id, $photo->photo_id, $sort, ($photo->isPrimary ? 1 : 0)]
                );
                $sort++;
            }
        }

        // Add to in-memory array
        foreach ($photos as $photo) {
            $this->addPhoto($photo, $photo->isPrimary ?? false);
        }
    }

    /**
     * Replace all photos (deletes existing, then adds new ones)
     * @param Photo[] $photos
     */
    public function savePhotos(array $photos): void {
        $table = $this->getPhotoLinkingTable();
        $key = $this->getPrimaryKeyColumn();
        $id = $this->getId();

        // Delete all existing photos
        $this->getDb()->executeSQL("DELETE FROM {$table} WHERE {$key} = ?", 'i', [$id]);

        // Clear in-memory arrays
        $this->photos = [];
        $this->primaryPhoto = null;

        // Add new photos (now uses addPhotos for DRY code)
        $this->addPhotos($photos);
    }
}
