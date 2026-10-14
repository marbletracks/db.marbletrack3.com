<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Support/PhotoFixtures.php';
require_once __DIR__ . '/Support/FailingDb.php';

/** @group step0 */
class Step0WrongRowTest extends TestCase
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

    public function testPhotoSaveAfterAListLandsOnTheIntendedNotebook(): void
    {
        $a = $this->makeRow('notebook');
        $this->makeRow('notebook');                 // a later row: findAll() ends on it or after it
        $this->makePhoto('meant-for-a');            // tracked, so cleanup removes it
        $repo = new \Database\NotebookRepository($this->db());

        $repo->setNotebookId($a);                   // the caller means Notebook A...
        $repo->findAll();                           // ...renders a list on the way...
        $repo->savePhotosFromUrls([$this->url('meant-for-a')]);

        $this->assertCount(1, $this->linkedIds('notebooks_2_photos', 'notebook_id', $a),
            'The photo landed on whichever row findAll() walked last, not on Notebook A');
    }
}
