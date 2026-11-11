<?php

use Database\PerspectiveMoments;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Support/PhotoFixtures.php';
require_once __DIR__ . '/Support/CountingDb.php';

class PerspectiveMomentsTest extends TestCase
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

    public function testLoadsManyPerspectivesInTwoQueries(): void
    {
        $a = $this->makeRow('worker');
        $b = $this->makeRow('worker');
        $none = $this->makeRow('worker');
        $late = $this->makeMoment(50, 60, 'late');
        $early = $this->makeMoment(5, 6, 'early');
        $moments = new \Database\MomentRepository($this->db());
        $moments->createTranslationIfNotExists($late, $a, 'worker');
        $moments->createTranslationIfNotExists($early, $a, 'worker');
        $moments->createTranslationIfNotExists($late, $b, 'worker');
        $moments->updateSignificance($late, $b, 'worker', true);
        $photo = $this->makePhoto('m');
        $this->link('moments_2_photos', 'moment_id', $late, $photo, 1);
        $counting = new CountingDb($this->db());

        $byWorker = (new PerspectiveMoments($counting))->forPerspectives('worker', [$a, $b, $none]);

        $this->assertSame(2, $counting->queries);
        $this->assertSame([$early, $late], array_map(fn ($m) => $m->moment_id, $byWorker[$a]));
        $this->assertSame([$late], array_map(fn ($m) => $m->moment_id, $byWorker[$b]));
        $this->assertFalse($byWorker[$a][1]->is_significant);
        $this->assertTrue($byWorker[$b][0]->is_significant);
        $this->assertSame([$photo], $this->photoIds($byWorker[$b][0]->photos));
        $this->assertArrayNotHasKey($none, $byWorker);
        $this->assertSame([], (new PerspectiveMoments($this->db()))->forPerspectives('worker', []));
    }
}
