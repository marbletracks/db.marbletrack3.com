<?php

use Database\TrackRepository;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Support/PhotoFixtures.php';

/** Tracks get photos the way every other kind has them: through PhotoLinks (mg #709). */
class TrackPhotosTest extends TestCase
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

    private function repo(): TrackRepository
    {
        return new TrackRepository($this->db());
    }

    private function urls(array $photos): array
    {
        return array_map(fn ($photo) => $photo->getUrl(), $photos);
    }

    public function testATrackWithoutPhotosHasAnEmptyList(): void
    {
        $id = $this->makeRow('track');
        $this->assertSame([], $this->repo()->findById($id)->photos);
    }

    public function testAddPhotosFromUrlsAppendsAndFindByIdLoadsThem(): void
    {
        $id = $this->makeRow('track');
        $this->makePhoto('a');
        $this->makePhoto('b');

        $this->repo()->addPhotosFromUrls($id, [$this->url('a'), $this->url('b')]);
        $this->repo()->addPhotosFromUrls($id, [$this->url('b')]);   // the API's Retry: harmless

        $track = $this->repo()->findById($id);
        $this->assertSame([$this->url('a'), $this->url('b')], $this->urls($track->photos));
        $this->assertSame($this->linkedIds('tracks_2_photos', 'track_id', $id), $this->photoIds($track->photos));
        $this->assertSame($this->urls($track->photos), $this->urls($this->repo()->findByAlias($track->track_alias)->photos));
    }

    public function testFindAllCarriesEachTracksOwnPhotos(): void
    {
        $a = $this->makeRow('track');
        $b = $this->makeRow('track');
        $pa = $this->makePhoto('a');
        $pb = $this->makePhoto('b');
        $this->link('tracks_2_photos', 'track_id', $a, $pa, 1);
        $this->link('tracks_2_photos', 'track_id', $b, $pb, 1);

        $byId = [];
        foreach ($this->repo()->findAll() as $track) {
            $byId[$track->track_id] = $track;
        }

        $this->assertSame([$pa], $this->photoIds($byId[$a]->photos));
        $this->assertSame([$pb], $this->photoIds($byId[$b]->photos));
    }

    public function testFormSaveRemovesOnlyWhatTheFormDeleted(): void
    {
        $id = $this->makeRow('track');
        [$p1, $p2, $api] = [$this->makePhoto('p1'), $this->makePhoto('p2'), $this->makePhoto('api')];
        $this->link('tracks_2_photos', 'track_id', $id, $p1, 1);
        $this->link('tracks_2_photos', 'track_id', $id, $p2, 2);
        $this->link('tracks_2_photos', 'track_id', $id, $api, 3);   // attached by badmint while the form was open

        $this->repo()->savePhotosFromForm($id, [$p1, $p2], [$this->url('p2')]);

        $this->assertSame([$p2, $api], $this->linkedIds('tracks_2_photos', 'track_id', $id));
    }
}
