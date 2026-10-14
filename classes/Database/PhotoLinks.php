<?php
namespace Database;

use Media\Photo;

/**
 * Photo links for one kind of row (notebooks, parts, ...).
 *
 * Stateless: every method takes the row's ID, so a save can never land on
 * whichever row a repository happened to load last.
 */
class PhotoLinks
{
    public function __construct(
        private DbInterface $db,
        private string $parentTable,
        private string $linkTable,
        private string $keyColumn
    ) {
    }

    /**
     * @param int[] $ids
     * @return array<int, Photo[]> rows with no photos are absent
     */
    public function forIds(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $links = $this->db->fetchResults(
            "SELECT {$this->keyColumn} AS row_id, photo_id, is_primary
             FROM {$this->linkTable}
             WHERE {$this->keyColumn} IN ($placeholders)
             ORDER BY {$this->keyColumn} ASC, photo_sort ASC, photo_id ASC",
            str_repeat('i', count($ids)),
            $ids
        )->toArray();

        $photos = [];
        $photoIds = array_values(array_unique(array_map(fn (array $link) => (int) $link['photo_id'], $links)));
        foreach ((new PhotoRepository($this->db))->findByIds($photoIds) as $photo) {
            $photos[$photo->photo_id] = $photo;
        }

        $byRow = [];
        foreach ($links as $link) {
            $photoId = (int) $link['photo_id'];
            if (!isset($photos[$photoId])) {
                continue;   // link to a deleted photo; today's loader skips these too
            }
            // is_primary belongs to the link, so each row gets its own copy
            $photo = clone $photos[$photoId];
            $photo->isPrimary = !empty($link['is_primary']);
            $byRow[(int) $link['row_id']][] = $photo;
        }
        return $byRow;
    }

    /** @return Photo[] */
    public function forId(int $id): array
    {
        return $this->forIds([$id])[$id] ?? [];
    }

    /** The last photo flagged primary, else the first: what HasPhotos::getPrimaryPhoto() returned. */
    public static function primaryOf(array $photos): ?Photo
    {
        $primary = null;
        foreach ($photos as $photo) {
            if ($photo->isPrimary) {
                $primary = $photo;
            }
        }
        return $primary ?? ($photos[0] ?? null);
    }

    /**
     * Append photos after the row's current last one, skipping any already linked.
     * Used by the API and uploaders, which only ever add.
     *
     * @param Photo[] $photos
     */
    public function add(int $id, array $photos): void
    {
        $this->atomically(function () use ($id, $photos) {
            $this->assertRowExists($id);
            $current = $this->currentSorts($id);
            $sort = empty($current) ? 1 : max($current) + 1;
            foreach ($photos as $photo) {
                if (isset($current[$photo->photo_id])) {
                    continue;
                }
                $this->db->executeSQL(
                    "INSERT INTO {$this->linkTable} ({$this->keyColumn}, photo_id, photo_sort, is_primary) VALUES (?, ?, ?, ?)",
                    'iiii',
                    [$id, $photo->photo_id, $sort, $photo->isPrimary ? 1 : 0]
                );
                $current[$photo->photo_id] = $sort++;
            }
        });
    }

    /** @param string[] $urls */
    public function addUrls(int $id, array $urls): void
    {
        // photos may be created below, so they must share add()'s transaction
        $this->atomically(function () use ($id, $urls) {
            $this->add($id, (new PhotoRepository($this->db))->findOrCreateByUrls($urls));
        });
    }

    /**
     * Save an edit form's photo list.
     *
     * Removes only photos the form loaded and no longer lists, so a photo
     * attached elsewhere while the form was open survives. The form's order
     * wins; photos the form never saw keep their relative order after it.
     *
     * @param int[] $loadedIds    photo IDs the form showed when it loaded
     * @param int[] $submittedIds photo IDs the form lists now, in screen order
     */
    public function saveFromForm(int $id, array $loadedIds, array $submittedIds): void
    {
        $loaded = array_map('intval', $loadedIds);
        $submitted = array_values(array_unique(array_map('intval', $submittedIds)));

        $this->atomically(function () use ($id, $loaded, $submitted) {
            $this->assertRowExists($id);
            $current = $this->currentSorts($id);

            foreach (array_diff($loaded, $submitted) as $photoId) {
                if (!isset($current[$photoId])) {
                    continue;
                }
                $this->db->executeSQL(
                    "DELETE FROM {$this->linkTable} WHERE {$this->keyColumn} = ? AND photo_id = ?",
                    'ii',
                    [$id, $photoId]
                );
                unset($current[$photoId]);
            }

            $extras = array_values(array_diff(array_keys($current), $submitted));
            $sort = 1;
            foreach (array_merge($submitted, $extras) as $photoId) {
                if (!isset($current[$photoId])) {
                    $this->db->executeSQL(
                        "INSERT INTO {$this->linkTable} ({$this->keyColumn}, photo_id, photo_sort, is_primary) VALUES (?, ?, ?, 0)",
                        'iii',
                        [$id, $photoId, $sort]
                    );
                } elseif ($current[$photoId] !== $sort) {
                    $this->db->executeSQL(
                        "UPDATE {$this->linkTable} SET photo_sort = ? WHERE {$this->keyColumn} = ? AND photo_id = ?",
                        'iii',
                        [$sort, $id, $photoId]
                    );
                }
                $sort++;
            }
        });
    }

    /**
     * saveFromForm() for forms that post URLs. A URL the form displayed for a
     * loaded photo maps back to that photo, even when it is a CDN URL built
     * from the photo's code rather than its url column.
     *
     * @param int[]    $loadedIds
     * @param string[] $urls
     */
    public function saveFormUrls(int $id, array $loadedIds, array $urls): void
    {
        // idsForUrls() may create photos, so it must share saveFromForm()'s transaction
        $this->atomically(function () use ($id, $loadedIds, $urls) {
            $this->saveFromForm($id, $loadedIds, $this->idsForUrls($loadedIds, $urls));
        });
    }

    /** @return int[] one photo ID per non-blank URL, in order */
    private function idsForUrls(array $loadedIds, array $urls): array
    {
        $urls = array_values(array_filter(array_map('trim', $urls), fn (string $url) => $url !== ''));
        if (empty($urls)) {
            return [];
        }

        $photoRepo = new PhotoRepository($this->db);
        $resolved = [];
        foreach ($photoRepo->findByIds(array_map('intval', $loadedIds)) as $photo) {
            $resolved[$photo->getUrl()] = $photo->photo_id;
        }

        $unknown = array_values(array_unique(array_filter($urls, fn (string $url) => !isset($resolved[$url]))));
        foreach ($photoRepo->findOrCreateByUrls($unknown) as $i => $photo) {
            $resolved[$unknown[$i]] = $photo->photo_id;
        }

        return array_map(fn (string $url) => $resolved[$url], $urls);
    }

    /** @return array<int, int> photo_id => photo_sort, in display order */
    private function currentSorts(int $id): array
    {
        $current = [];
        $rows = $this->db->fetchResults(
            "SELECT photo_id, photo_sort FROM {$this->linkTable} WHERE {$this->keyColumn} = ? ORDER BY photo_sort ASC, photo_id ASC",
            'i',
            [$id]
        )->toArray();
        foreach ($rows as $row) {
            $current[(int) $row['photo_id']] = (int) $row['photo_sort'];
        }
        return $current;
    }

    private function assertRowExists(int $id): void
    {
        $found = $this->db->fetchResults(
            "SELECT 1 FROM {$this->parentTable} WHERE {$this->keyColumn} = ?",
            'i',
            [$id]
        );
        if ($found->numRows() === 0) {
            throw new EDatabaseException("No {$this->parentTable} row with {$this->keyColumn} = {$id}");
        }
    }

    /** Run $work in a transaction, joining the caller's if one is already open. */
    private function atomically(callable $work): void
    {
        if ($this->db->inTransaction()) {
            $work();
            return;
        }
        $this->db->beginTransaction();
        try {
            $work();
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
