<?php

use Database\PhotoLinks;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Support/PhotoFixtures.php';
require_once __DIR__ . '/Support/FailingDb.php';

class PhotoLinksTest extends TestCase
{
    use PhotoFixtures;

    protected function setUp(): void
    {
        $this->fixturesUp();
    }

    protected function tearDown(): void
    {
        if ($this->db()->inTransaction()) {
            $this->db()->rollBack();
        }
        $this->cleanupFixtures();
    }

    private function links($db = null): PhotoLinks
    {
        return new PhotoLinks($db ?? $this->db(), 'notebooks', 'notebooks_2_photos', 'notebook_id');
    }

    private function linked(int $id): array
    {
        return $this->linkedIds('notebooks_2_photos', 'notebook_id', $id);
    }

    public function testForIdsLoadsEveryRowInTwoQueries(): void
    {
        $a = $this->makeRow('notebook');
        $b = $this->makeRow('notebook');
        $empty = $this->makeRow('notebook');
        $p1 = $this->makePhoto('p1');
        $p2 = $this->makePhoto('p2');
        $this->link('notebooks_2_photos', 'notebook_id', $a, $p2, 2);
        $this->link('notebooks_2_photos', 'notebook_id', $a, $p1, 1);
        $this->link('notebooks_2_photos', 'notebook_id', $b, $p1, 1, 1);
        $counting = new CountingDb($this->db());

        $byId = $this->links($counting)->forIds([$a, $b, $empty]);

        $this->assertSame(2, $counting->queries);
        $this->assertSame([$p1, $p2], $this->photoIds($byId[$a]));
        $this->assertSame([$p1], $this->photoIds($byId[$b]));
        $this->assertArrayNotHasKey($empty, $byId);
        $this->assertFalse($byId[$a][0]->isPrimary);
        $this->assertTrue($byId[$b][0]->isPrimary);
        $this->assertSame([], $this->links()->forIds([]));
    }

    public function testPrimaryOfIsLastFlaggedElseFirst(): void
    {
        $one = new \Media\Photo(1, null, 'a');
        $two = new \Media\Photo(2, null, 'b');
        $this->assertNull(PhotoLinks::primaryOf([]));
        $this->assertSame($one, PhotoLinks::primaryOf([$one, $two]));
        $two->isPrimary = true;
        $this->assertSame($two, PhotoLinks::primaryOf([$one, $two]));
    }

    public function testAddAppendsAndSkipsPhotosAlreadyLinked(): void
    {
        $id = $this->makeRow('notebook');
        $p1 = $this->makePhoto('p1');
        $this->link('notebooks_2_photos', 'notebook_id', $id, $p1, 5);
        $this->makePhoto('new');   // tracked, so cleanup removes it

        $this->links()->addUrls($id, [$this->url('p1'), $this->url('new')]);

        $linked = $this->linked($id);
        $this->assertCount(2, $linked);
        $this->assertSame($p1, $linked[0]);
        $sort = $this->db()->fetchResults(
            'SELECT photo_sort FROM notebooks_2_photos WHERE notebook_id = ? AND photo_id = ?', 'ii', [$id, $linked[1]]
        )->toArray()[0]['photo_sort'];
        $this->assertSame(6, (int) $sort);
    }

    public function testFormSaveRemovesOnlyWhatTheFormDeleted(): void
    {
        $id = $this->makeRow('notebook');
        [$p1, $p2, $api] = [$this->makePhoto('p1'), $this->makePhoto('p2'), $this->makePhoto('api')];
        $this->link('notebooks_2_photos', 'notebook_id', $id, $p1, 1);
        $this->link('notebooks_2_photos', 'notebook_id', $id, $p2, 2);
        $this->link('notebooks_2_photos', 'notebook_id', $id, $api, 3);   // added after the form loaded

        $this->links()->saveFromForm($id, [$p1, $p2], [$p2]);

        $this->assertSame([$p2, $api], $this->linked($id));
    }

    public function testFormOrderWinsAndApiExtrasGoLast(): void
    {
        $id = $this->makeRow('notebook');
        [$p1, $p2, $api] = [$this->makePhoto('p1'), $this->makePhoto('p2'), $this->makePhoto('api')];
        $this->link('notebooks_2_photos', 'notebook_id', $id, $api, 1);
        $this->link('notebooks_2_photos', 'notebook_id', $id, $p1, 2);
        $this->link('notebooks_2_photos', 'notebook_id', $id, $p2, 3);
        $new = $this->makePhoto('new');

        $this->links()->saveFromForm($id, [$p1, $p2], [$new, $p2, $p1]);

        $this->assertSame([$new, $p2, $p1, $api], $this->linked($id));
    }

    public function testClearingFormKeepsApiExtras(): void
    {
        $id = $this->makeRow('notebook');
        [$p1, $api] = [$this->makePhoto('p1'), $this->makePhoto('api')];
        $this->link('notebooks_2_photos', 'notebook_id', $id, $p1, 1);
        $this->link('notebooks_2_photos', 'notebook_id', $id, $api, 2);

        $this->links()->saveFormUrls($id, [$p1], ['', '']);

        $this->assertSame([$api], $this->linked($id));
    }

    public function testMissingLoadedListRemovesNothing(): void
    {
        $id = $this->makeRow('notebook');
        [$p1, $p2] = [$this->makePhoto('p1'), $this->makePhoto('p2')];
        $this->link('notebooks_2_photos', 'notebook_id', $id, $p1, 1);
        $this->link('notebooks_2_photos', 'notebook_id', $id, $p2, 2);
        $new = $this->makePhoto('new');

        $this->links()->saveFormUrls($id, [], [$this->url('p2'), $this->url('new')]);

        $this->assertSame([$p2, $new, $p1], $this->linked($id));
    }

    public function testUnchangedFormKeepsCodedPhoto(): void
    {
        $id = $this->makeRow('notebook');
        $this->db()->executeSQL('INSERT INTO photos (code, url) VALUES (?, ?)', 'ss', [$this->prefix . 'code', $this->url('raw')]);
        $coded = $this->db()->insertId();
        $this->link('notebooks_2_photos', 'notebook_id', $id, $coded, 1);
        $shown = (new \Database\PhotoRepository($this->db()))->findByIds([$coded])[0]->getUrl();   // the CloudFront URL
        $photosBefore = (int) $this->db()->fetchResults('SELECT COUNT(*) AS n FROM photos')->toArray()[0]['n'];

        $this->links()->saveFormUrls($id, [$coded], [$shown]);

        $this->assertSame([$coded], $this->linked($id));
        $this->assertSame($photosBefore, (int) $this->db()->fetchResults('SELECT COUNT(*) AS n FROM photos')->toArray()[0]['n']);
        $this->db()->executeSQL('DELETE FROM photos WHERE photo_id = ?', 'i', [$coded]);
    }

    public function testDuplicateUrlLinksOnce(): void
    {
        $id = $this->makeRow('notebook');
        $this->makePhoto('dup');

        $this->links()->saveFormUrls($id, [], [$this->url('dup'), $this->url('dup')]);

        $this->assertCount(1, $this->linked($id));
    }

    public function testBlankUrlsIgnored(): void
    {
        $id = $this->makeRow('notebook');
        $p1 = $this->makePhoto('p1');

        $this->links()->saveFormUrls($id, [], ['', '  ', $this->url('p1'), '']);

        $this->assertSame([$p1], $this->linked($id));
    }

    public function testFailedFormSaveRollsBackEverything(): void
    {
        $id = $this->makeRow('notebook');
        [$p1, $p2, $p3] = [$this->makePhoto('p1'), $this->makePhoto('p2'), $this->makePhoto('p3')];
        $this->link('notebooks_2_photos', 'notebook_id', $id, $p1, 1);
        $this->link('notebooks_2_photos', 'notebook_id', $id, $p2, 2);

        try {
            // write 1 = DELETE p1, write 2 = UPDATE p2's sort -> dies
            $this->links(new FailingDb($this->db(), 2))->saveFromForm($id, [$p1, $p2], [$p2, $p3]);
            $this->fail('Expected the injected failure');
        } catch (\Database\EDatabaseException $e) {
        }

        $this->assertSame([$p1, $p2], $this->linked($id));
        $this->assertFalse($this->db()->inTransaction());
    }

    public function testJoinsTheCallersTransaction(): void
    {
        $id = $this->makeRow('notebook');
        $p1 = $this->makePhoto('p1');

        $this->db()->beginTransaction();
        $this->links()->saveFromForm($id, [], [$p1]);
        $this->assertTrue($this->db()->inTransaction(), 'PhotoLinks must not commit the caller\'s transaction');
        $this->db()->rollBack();

        $this->assertSame([], $this->linked($id));
    }

    public function testUnknownRowThrows(): void
    {
        $this->expectException(\Database\EDatabaseException::class);
        $this->links()->saveFromForm(2147483000, [], [$this->makePhoto('p1')]);
    }
}
