<?php

use PHPUnit\Framework\TestCase;

class SnapshotScriptTest extends TestCase
{
    private function runScript(string $args): array
    {
        exec('php /app/scripts/photo_links_snapshot.php ' . $args . ' 2>&1', $out, $code);
        return [implode("\n", $out), $code];
    }

    public function testDiffReportsOnlyChangedRows(): void
    {
        $dir = sys_get_temp_dir();
        file_put_contents("$dir/a.json", json_encode(['parts_2_photos' => ['1' => [5, 6], '2' => [7]]]));
        file_put_contents("$dir/b.json", json_encode(['parts_2_photos' => ['1' => [6, 5], '2' => [7]]]));

        [$out, $code] = $this->runScript("--diff $dir/a.json $dir/b.json");

        $this->assertSame(1, $code);
        $this->assertStringContainsString('parts_2_photos 1: [5,6] -> [6,5]', $out);
        $this->assertStringNotContainsString('parts_2_photos 2', $out);
        [$same, $sameCode] = $this->runScript("--diff $dir/a.json $dir/a.json");
        $this->assertSame(['', 0], [$same, $sameCode]);
    }

    public function testSnapshotAndReposRunAgainstTheTestDatabase(): void
    {
        [$json, $code] = $this->runScript('');
        $this->assertSame(0, $code);
        $this->assertArrayHasKey('notebooks_2_photos', json_decode($json, true));

        [$out, $reposCode] = $this->runScript('--repos');
        $this->assertSame(0, $reposCode, $out);
        $this->assertStringContainsString('marbles_2_photos:', $out);
    }
}
