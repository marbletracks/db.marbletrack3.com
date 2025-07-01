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

        // $photoIds = array_column($r->data, 'photo_id');
        $photos = (new PhotoRepository($this->getDb()))->findByIds($photoIds);

        foreach ($photos as $photo) {
            if ($photo) {
                $isPrimary = $primaryMap[$photo->photo_id] ?? false;
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
     * @param Photo[] $photos
     */
    public function savePhotos(array $photos): void {
        // echo "<pre>";
        $table = $this->getPhotoLinkingTable();
        $key = $this->getPrimaryKeyColumn();
        $id = $this->getId();

        $this->getDb()->executeSQL("DELETE FROM {$table} WHERE {$key} = ?", 'i', [$id]);

        // Set the first photo as primary
        $this->primaryPhoto = !empty($photos) ? $photos[0] : null;

        $sort = 0;
        // print_rob($photos,false);
        foreach ($photos as $photo) {
            // print_rob([$id, $photo->photo_id, $sort, ($photo === $this->primaryPhoto ? 1 : 0)],false);
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
