<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Support/PhotoFixtures.php';

class MomentFindersTest extends TestCase
{
    use PhotoFixtures;

    protected function setUp(): void
    {
        $this->fixturesUp();
    }

    protected function tearDown(): void
    {
        $this->cleanupFixtures();
    }

    public function testEveryFinderAttachesEachMomentsOwnPhotos(): void
    {
        $repo = new \Database\MomentRepository($this->db());
        $withPhoto = $this->makeMoment(1000001, 1000002, $this->prefix . 'finder with');
        $without = $this->makeMoment(1000003, 1000004, $this->prefix . 'finder without');
        $photo = $this->makePhoto('finder');
        $this->link('moments_2_photos', 'moment_id', $withPhoto, $photo, 1);

        $lists = [
            'findAll' => $repo->findAll(),
            'findByFilter' => $repo->findByFilter($this->prefix . 'finder'),
            'findFiltered' => $repo->findFiltered($this->prefix . 'finder')['moments'],
        ];
        foreach ($lists as $finder => $moments) {
            $byId = [];
            foreach ($moments as $m) {
                $byId[$m->moment_id] = $m;
            }
            $this->assertSame([$photo], $this->photoIds($byId[$withPhoto]->photos), $finder);
            $this->assertSame([], $this->photoIds($byId[$without]->photos), $finder);
        }
        $this->assertSame([$photo], $this->photoIds($repo->findById($withPhoto)->photos));
        $filtered = $repo->findFiltered($this->prefix . 'finder');
        foreach ($filtered['moments'] as $m) {
            $this->assertIsInt($m->perspective_count);
        }
    }

    /**
     * Finders the brief did not list: by take and similar-for-worker.
     * findByPartId has no callers and parts_2_moments is absent from the test DB,
     * so it is not exercised here.
     */
    public function testTakeAndSimilarFindersFindersAttachEachMomentsOwnPhotos(): void
    {
        $repo = new \Database\MomentRepository($this->db());
        // Tracked first so cleanup (reverse order) deletes it after the moments that reference it.
        $takeId = $this->db()->insertFromRecord('takes', 's', ['take_name' => $this->prefix . 'take']);
        $this->track('takes', 'take_id', $takeId);
        $withPhoto = $this->makeMoment(1000001, 1000002, $this->prefix . 'other with');
        $without = $this->makeMoment(1000003, 1000004, $this->prefix . 'other without');
        $photoA = $this->makePhoto('other_a');
        $photoB = $this->makePhoto('other_b');
        $this->link('moments_2_photos', 'moment_id', $withPhoto, $photoB, 2);
        $this->link('moments_2_photos', 'moment_id', $withPhoto, $photoA, 1);

        foreach ([$withPhoto, $without] as $id) {
            $this->db()->executeSQL('UPDATE moments SET take_id = ? WHERE moment_id = ?', 'ii', [$takeId, $id]);
        }

        $worker = $this->makeRow('worker');
        foreach ([$without, $withPhoto] as $id) {
            $this->db()->executeSQL(
                "INSERT INTO moment_translations (moment_id, perspective_entity_id, perspective_entity_type, translated_note) VALUES (?, ?, 'worker', 'x')",
                'ii',
                [$id, $worker]
            );
        }

        $lists = [
            'findWithinTakeId' => $repo->findWithinTakeId($takeId),
            'findSimilarMoments' => $repo->findSimilarMoments($worker, 1000001, 1000004, 0),
        ];
        foreach ($lists as $finder => $moments) {
            $byId = [];
            foreach ($moments as $m) {
                $byId[$m->moment_id] = $m;
            }
            $this->assertArrayHasKey($withPhoto, $byId, $finder);
            $this->assertArrayHasKey($without, $byId, $finder);
            $this->assertSame([$photoA, $photoB], $this->photoIds($byId[$withPhoto]->photos), $finder);
            $this->assertSame([], $this->photoIds($byId[$without]->photos), $finder);
        }
    }
}
