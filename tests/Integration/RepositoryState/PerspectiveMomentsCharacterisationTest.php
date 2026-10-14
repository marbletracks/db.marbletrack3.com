<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Support/PhotoFixtures.php';

class PerspectiveMomentsCharacterisationTest extends TestCase
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

    public function kinds(): array
    {
        return [
            'worker' => ['worker', fn ($db) => new \Database\WorkersRepository($db, 'en')],
            'part' => ['part', fn ($db) => new \Database\PartsRepository($db, 'en')],
            'marble' => ['marble', fn ($db) => new \Database\MarblesRepository($db)],
        ];
    }

    /** @dataProvider kinds */
    public function testMomentsComeInTakeFrameOrderWithFlagsAndPhotos(string $kind, callable $repo): void
    {
        $owner = $this->makeRow($kind);
        $other = $this->makeRow($kind);
        $later = $this->makeMoment(30, 40, 'later');
        $earlier = $this->makeMoment(10, 20, 'earlier');
        $moments = new \Database\MomentRepository($this->db());
        $moments->createTranslationIfNotExists($later, $owner, $kind);
        $moments->createTranslationIfNotExists($earlier, $owner, $kind);
        $moments->createTranslationIfNotExists($earlier, $other, $kind);
        $moments->updateSignificance($later, $owner, $kind, true);
        $photo = $this->makePhoto('moment-photo');
        $this->link('moments_2_photos', 'moment_id', $later, $photo, 1);

        $byId = [];
        foreach ($repo($this->db())->findAll() as $entity) {
            $byId[$entity->{$kind . '_id'}] = $entity;
        }
        $mine = $byId[$owner]->moments;

        $this->assertSame([$earlier, $later], array_map(fn ($m) => $m->moment_id, $mine));
        $this->assertSame([10, 30], array_map(fn ($m) => $m->frame_start, $mine));
        $this->assertSame([false, true], array_map(fn ($m) => $m->is_significant, $mine));
        $this->assertSame([], $this->photoIds($mine[0]->photos));
        $this->assertSame([$photo], $this->photoIds($mine[1]->photos));
        $this->assertSame([$earlier], array_map(fn ($m) => $m->moment_id, $byId[$other]->moments));
        $this->assertSame(
            array_map(fn ($m) => $m->moment_id, $mine),
            array_map(fn ($m) => $m->moment_id, $repo($this->db())->findById($owner)->moments)
        );
    }
}
