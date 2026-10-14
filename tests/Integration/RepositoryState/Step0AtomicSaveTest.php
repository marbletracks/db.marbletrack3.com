<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Support/PhotoFixtures.php';
require_once __DIR__ . '/Support/FailingDb.php';

/** @group step0 */
class Step0AtomicSaveTest extends TestCase
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

    public function testAFailedSaveLeavesTheOriginalLinks(): void
    {
        $notebook = $this->makeRow('notebook');
        $one = $this->makePhoto('one');
        $two = $this->makePhoto('two');
        $this->makePhoto('three');                    // exists already, so no INSERT INTO photos
        $this->link('notebooks_2_photos', 'notebook_id', $notebook, $one, 1);
        $this->link('notebooks_2_photos', 'notebook_id', $notebook, $two, 2);

        $repo = new \Database\NotebookRepository(new FailingDb($this->db(), 2));
        $repo->setNotebookId($notebook);
        try {
            // Rob removed photo one and added photo three; the second write dies.
            $repo->savePhotosFromUrls([$this->url('two'), $this->url('three')]);
        } catch (\Database\EDatabaseException $e) {
        }

        $this->assertSame([$one, $two], $this->linkedIds('notebooks_2_photos', 'notebook_id', $notebook));
    }
}
