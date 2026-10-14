<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Support/PhotoFixtures.php';
require_once __DIR__ . '/Support/FailingDb.php';

/** @group step0 */
class Step0ApiPhotoSurvivesFormTest extends TestCase
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

    public function testFormSaveKeepsAPhotoTheApiAddedWhileItWasOpen(): void
    {
        $part = $this->makeRow('part');
        $one = $this->makePhoto('one');
        $two = $this->makePhoto('two');
        $three = $this->makePhoto('three');
        $this->link('parts_2_photos', 'part_id', $part, $one, 1);
        $this->link('parts_2_photos', 'part_id', $part, $two, 2);

        $form = new \Database\PartsRepository($this->db(), 'en');
        $loaded = $form->findById($part);            // Rob opens the edit form

        $api = new \Database\PartsRepository($this->db(), 'en');
        $api->setPartId($part);                       // the API attaches a photo meanwhile
        $api->addPhotosFromUrls([$this->url('three')]);

        $form->setPartId($part);                      // Rob saves without touching photos
        $form->savePhotosFromUrls(array_map(fn ($p) => $p->getUrl(), $loaded->photos));

        $this->assertContains($three, $this->linkedIds('parts_2_photos', 'part_id', $part));
    }
}
