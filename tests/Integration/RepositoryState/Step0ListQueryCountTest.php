<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Support/PhotoFixtures.php';
require_once __DIR__ . '/Support/FailingDb.php';

/** @group step0 */
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
        $before = $this->countQueries($findAll);
        foreach ([1, 2, 3] as $n) {
            $id = $this->makeRow($kind);
            $table = $kind . 's_2_photos';
            $this->link($table, $kind . '_id', $id, $this->makePhoto("{$kind}{$n}"), 1);
        }
        $after = $this->countQueries($findAll);

        $this->assertSame($before, $after, "Three more {$kind}s cost " . ($after - $before) . ' more queries');
    }

    public function lists(): array
    {
        return [
            'notebooks' => ['notebook', fn ($db) => (new \Database\NotebookRepository($db))->findAll()],
            'workers' => ['worker', fn ($db) => (new \Database\WorkersRepository($db, 'en'))->findAll()],
        ];
    }

    private function countQueries(callable $findAll): int
    {
        $db = new CountingDb($this->db());
        $findAll($db);
        return $db->queries;
    }
}
