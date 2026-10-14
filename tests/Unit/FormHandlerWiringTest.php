<?php

use PHPUnit\Framework\TestCase;

/**
 * Static check that each admin save handler reads the same field name its
 * template posts. A drift would silently disable photo removal.
 */
class FormHandlerWiringTest extends TestCase
{
    /** @dataProvider handlers */
    public function testHandlerAndTemplateAgreeOnLoadedPhotoIds(string $handler): void
    {
        $root = dirname(__DIR__, 2);
        $source = file_get_contents("{$root}/wwwroot/admin/{$handler}");
        $this->assertNotFalse($source, "Cannot read {$handler}");
        $this->assertStringContainsString("postedIds('loaded_photo_ids')", $source, "{$handler} must read loaded_photo_ids");

        // Find the form template through the handler's own setTemplate call.
        $this->assertSame(
            1,
            preg_match('#setTemplate\((?:template_file:\s*)?"(admin/[^"]+\.tpl\.php)"\)#', $source, $m),
            "{$handler} has no admin setTemplate call"
        );
        $template = file_get_contents("{$root}/templates/{$m[1]}");
        $this->assertNotFalse($template, "Cannot read template {$m[1]}");
        $this->assertStringContainsString('name="loaded_photo_ids[]"', $template, "{$m[1]} must post loaded_photo_ids[]");
    }

    public function handlers(): array
    {
        $files = [
            'notebooks/notebook.php',
            'notebooks/pages/page.php',
            'episodes/episode.php',
            'moments/moment.php',
            'workers/worker.php',
            'parts/part.php',
            'tracks/track.php',
            'marbles/marble.php',
        ];
        return array_combine($files, array_map(fn ($f) => [$f], $files));
    }
}
