<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Support/PhotoFixtures.php';
require_once __DIR__ . '/Support/FailingDb.php';

class Step0ListQueryCountTest extends TestCase
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

    /** @dataProvider lists */
    public function testListQueryCountDoesNotGrowWithRows(string $kind, callable $findAll): void
    {
        // One linked row first: an empty list skips the photo queries entirely,
        // so a zero-row baseline would undercount.
        $seed = $this->makeRow($kind);
        $this->link($kind . 's_2_photos', $kind . '_id', $seed, $this->makePhoto("{$kind}0"), 1);
        [$before, $rowsBefore] = $this->countQueries($findAll);
        foreach ([1, 2, 3] as $n) {
            $id = $this->makeRow($kind);
            $table = $kind . 's_2_photos';
            $this->link($table, $kind . '_id', $id, $this->makePhoto("{$kind}{$n}"), 1);
        }
        [$after, $rowsAfter] = $this->countQueries($findAll);

        // Rows invisible to findAll (e.g. a missing translation) would make the count prove nothing.
        $this->assertSame($rowsBefore + 3, $rowsAfter, "findAll must return the {$kind} fixture rows");

        $this->assertSame($before, $after, "Three more {$kind}s cost " . ($after - $before) . ' more queries');
    }

    public function lists(): array
    {
        return [
            'notebooks' => ['notebook', fn ($db) => (new \Database\NotebookRepository($db))->findAll()],
            'pages' => ['page', fn ($db) => (new \Database\PageRepository($db))->findAll()],
            'episodes' => ['episode', fn ($db) => (new \Database\EpisodeRepository($db))->findAll()],
            'moments' => ['moment', fn ($db) => (new \Database\MomentRepository($db))->findAll()],
            'parts' => ['part', fn ($db) => (new \Database\PartsRepository($db, 'en'))->findAll()],
            'marbles' => ['marble', fn ($db) => (new \Database\MarblesRepository($db))->findAll()],
            'workers' => ['worker', fn ($db) => (new \Database\WorkersRepository($db, 'en'))->findAll()],
        ];
    }

    /** @return array{0:int,1:int} queries issued, rows returned */
    private function countQueries(callable $findAll): array
    {
        $db = new CountingDb($this->db());
        $rows = $findAll($db);
        return [$db->queries, count($rows)];
    }
}
