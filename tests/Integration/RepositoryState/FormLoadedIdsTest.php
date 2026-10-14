<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Support/PhotoFixtures.php';

/** Each edit form must post back the photo IDs it showed, outside any JS-managed photo list. */
class FormLoadedIdsTest extends TestCase
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

    private function renders(string $template, array $vars): string
    {
        $page = new \Template(getTestConfig());
        $page->setTemplate($template);
        foreach ($vars as $name => $value) {
            $page->set($name, $value);
        }
        return $page->grabTheGoods();
    }

    private function assertPostsLoadedIds(string $html, array $photoIds): void
    {
        preg_match_all('/<input type="hidden" name="loaded_photo_ids\[\]" value="(\d+)">/', $html, $m);
        $this->assertSame(array_map('strval', $photoIds), $m[1]);
        $form = strpos($html, '<form');
        $this->assertNotFalse($form);
        $this->assertGreaterThan($form, strpos($html, 'loaded_photo_ids'));
    }

    public function testNotebookForm(): void
    {
        $id = $this->makeRow('notebook');
        [$a, $b] = [$this->makePhoto('a'), $this->makePhoto('b')];
        $this->link('notebooks_2_photos', 'notebook_id', $id, $b, 1);
        $this->link('notebooks_2_photos', 'notebook_id', $id, $a, 2);

        $html = $this->renders('admin/notebooks/notebook.tpl.php', [
            'errors' => [],
            'notebook' => (new \Database\NotebookRepository($this->db()))->findById($id),
        ]);

        $this->assertPostsLoadedIds($html, [$b, $a]);
    }

    public function testPageForm(): void
    {
        $id = $this->makeRow('page');
        [$a, $b] = [$this->makePhoto('a'), $this->makePhoto('b')];
        $this->link('pages_2_photos', 'page_id', $id, $a, 1);
        $this->link('pages_2_photos', 'page_id', $id, $b, 2);

        $html = $this->renders('admin/notebooks/pages/page.tpl.php', [
            'errors' => [],
            'page' => (new \Database\PageRepository($this->db()))->findById($id),
            'columns' => [],
            'page_title' => 'Edit Page',
        ]);

        $this->assertPostsLoadedIds($html, [$a, $b]);
    }

    public function testEpisodeForm(): void
    {
        $id = $this->makeRow('episode');
        [$a, $b] = [$this->makePhoto('a'), $this->makePhoto('b')];
        $this->link('episodes_2_photos', 'episode_id', $id, $a, 1);
        $this->link('episodes_2_photos', 'episode_id', $id, $b, 2);

        $html = $this->renders('admin/episodes/episode.tpl.php', [
            'errors' => [],
            'episode' => (new \Database\EpisodeRepository($this->db()))->findById($id),
            'defaultTitle' => '',
            'defaultDesc' => '',
            'episode_frames' => '',
            'defaultLivestreamId' => null,
            'streamCode' => '',
        ]);

        $this->assertPostsLoadedIds($html, [$a, $b]);
    }

    public function testMomentForm(): void
    {
        $id = $this->makeRow('moment');
        [$a, $b] = [$this->makePhoto('a'), $this->makePhoto('b')];
        $this->link('moments_2_photos', 'moment_id', $id, $a, 1);
        $this->link('moments_2_photos', 'moment_id', $id, $b, 2);

        $html = $this->renders('admin/moments/moment.tpl.php', [
            'errors' => [],
            'moment' => (new \Database\MomentRepository($this->db()))->findById($id),
            'default_frame_start' => '',
            'default_take_id' => '',
            'translations' => [],
            'takes' => [],
        ]);

        $this->assertPostsLoadedIds($html, [$a, $b]);
    }

    public function testWorkerForm(): void
    {
        $id = $this->makeRow('worker');
        [$a, $b] = [$this->makePhoto('a'), $this->makePhoto('b')];
        $this->link('workers_2_photos', 'worker_id', $id, $a, 1);
        $this->link('workers_2_photos', 'worker_id', $id, $b, 2);

        $html = $this->renders('admin/workers/worker.tpl.php', [
            'errors' => [],
            'worker' => (new \Database\WorkersRepository($this->db(), 'en'))->findById($id),
            'prioritized_moments' => [],
            'other_moments' => [],
        ]);

        $this->assertPostsLoadedIds($html, [$a, $b]);
    }

    public function testPartForm(): void
    {
        $id = $this->makeRow('part');
        [$a, $b] = [$this->makePhoto('a'), $this->makePhoto('b')];
        $this->link('parts_2_photos', 'part_id', $id, $a, 1);
        $this->link('parts_2_photos', 'part_id', $id, $b, 2);

        $html = $this->renders('admin/parts/part.tpl.php', [
            'errors' => [],
            'part' => (new \Database\PartsRepository($this->db(), 'en'))->findById($id),
            'tracks' => [],
            'prioritized_moments' => [],
            'other_moments' => [],
        ]);

        $this->assertPostsLoadedIds($html, [$a, $b]);
    }
}
