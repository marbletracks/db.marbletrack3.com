<?php

use PHPUnit\Framework\TestCase;

/** Each kind's edit page links to badmint's /mt3/ page with itself preselected (mg #709). */
class BadmintLinksTest extends TestCase
{
    /** @dataProvider pages */
    public function testEditPageLinksToBadmintWithItselfPreselected(string $template, string $query): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . "/templates/admin/{$template}");
        $this->assertNotFalse($source);
        $this->assertStringContainsString("https://badmint.robnugen.com/mt3/?{$query}=<?= (int) ", $source);
    }

    public function pages(): array
    {
        return [
            'part' => ['parts/part.tpl.php', 'part'],
            'worker' => ['workers/worker.tpl.php', 'worker'],
            'track' => ['tracks/track.tpl.php', 'track'],
        ];
    }
}
