<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Support/PhotoFixtures.php';

class PhotoLoadingCharacterisationTest extends TestCase
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
            'notebook' => ['notebook', 'notebooks_2_photos', 'notebook_id'],
            'page' => ['page', 'pages_2_photos', 'page_id'],
            'episode' => ['episode', 'episodes_2_photos', 'episode_id'],
            'moment' => ['moment', 'moments_2_photos', 'moment_id'],
            'worker' => ['worker', 'workers_2_photos', 'worker_id'],
            'part' => ['part', 'parts_2_photos', 'part_id'],
            'marble' => ['marble', 'marbles_2_photos', 'marble_id'],
        ];
    }

    private function repoFor(string $kind): object
    {
        $db = $this->db();
        return match ($kind) {
            'notebook' => new \Database\NotebookRepository($db),
            'page' => new \Database\PageRepository($db),
            'episode' => new \Database\EpisodeRepository($db),
            'moment' => new \Database\MomentRepository($db),
            'worker' => new \Database\WorkersRepository($db, 'en'),
            'part' => new \Database\PartsRepository($db, 'en'),
            'marble' => new \Database\MarblesRepository($db),
        };
    }

    /** @dataProvider kinds */
    public function testFindByIdReturnsPhotosInSortThenIdOrder(string $kind, string $table, string $key): void
    {
        $id = $this->makeRow($kind);
        $first = $this->makePhoto('first');
        $second = $this->makePhoto('second');
        $third = $this->makePhoto('third');
        // second and third share sort 2, so photo_id breaks the tie
        $this->link($table, $key, $id, $third, 2);
        $this->link($table, $key, $id, $first, 1);
        $this->link($table, $key, $id, $second, 2);

        $entity = $this->repoFor($kind)->findById($id);

        $this->assertSame([$first, $second, $third], $this->photoIds($entity->photos));
        foreach ($entity->photos as $photo) {
            $this->assertFalse($photo->isPrimary);
        }
    }

    /** @dataProvider kinds */
    public function testFindAllGivesEachRowItsOwnPhotos(string $kind, string $table, string $key): void
    {
        $withPhoto = $this->makeRow($kind);
        $without = $this->makeRow($kind);
        $photo = $this->makePhoto('only');
        $this->link($table, $key, $withPhoto, $photo, 1);

        $byId = [];
        foreach ($this->repoFor($kind)->findAll() as $entity) {
            $byId[$entity->{$key}] = $entity;
        }

        $this->assertSame([$photo], $this->photoIds($byId[$withPhoto]->photos));
        $this->assertSame([], $this->photoIds($byId[$without]->photos));
    }

    public function testIsPrimaryComesFromTheColumn(): void
    {
        $id = $this->makeRow('notebook');
        $plain = $this->makePhoto('plain');
        $flagged = $this->makePhoto('flagged');
        $this->link('notebooks_2_photos', 'notebook_id', $id, $plain, 1);
        $this->link('notebooks_2_photos', 'notebook_id', $id, $flagged, 2, 1);

        $photos = $this->repoFor('notebook')->findById($id)->photos;

        $this->assertFalse($photos[0]->isPrimary);
        $this->assertTrue($photos[1]->isPrimary);
    }

    public function testSharedPhotoCarriesEachRowsOwnPrimaryFlag(): void
    {
        $a = $this->makeRow('notebook');
        $b = $this->makeRow('notebook');
        $shared = $this->makePhoto('shared');
        $this->link('notebooks_2_photos', 'notebook_id', $a, $shared, 1, 1);
        $this->link('notebooks_2_photos', 'notebook_id', $b, $shared, 1, 0);

        $byId = [];
        foreach ($this->repoFor('notebook')->findAll() as $notebook) {
            $byId[$notebook->notebook_id] = $notebook;
        }

        $this->assertTrue($byId[$a]->photos[0]->isPrimary);
        $this->assertFalse($byId[$b]->photos[0]->isPrimary);
    }

    public function testPagePrimaryPhotoIsLastFlaggedElseFirst(): void
    {
        $unflagged = $this->makeRow('page');
        $flaggedPage = $this->makeRow('page');
        $one = $this->makePhoto('one');
        $two = $this->makePhoto('two');
        $this->link('pages_2_photos', 'page_id', $unflagged, $one, 1);
        $this->link('pages_2_photos', 'page_id', $unflagged, $two, 2);
        $this->link('pages_2_photos', 'page_id', $flaggedPage, $one, 1);
        $this->link('pages_2_photos', 'page_id', $flaggedPage, $two, 2, 1);

        $repo = $this->repoFor('page');

        $this->assertSame($one, $repo->findById($unflagged)->primaryPhoto->photo_id);
        $this->assertSame($two, $repo->findById($flaggedPage)->primaryPhoto->photo_id);
    }
}
