<?php

use PHPUnit\Framework\TestCase;

/**
 * Photos are uploaded through badmint's /mt3/ page now (mg #709). Nothing in MT3 may
 * post to badmin's bullet.php, which is the last reason that script still exists.
 */
class NothingPostsToBadminTest extends TestCase
{
    public function testNoTemplateOrPageMentionsBulletPhp(): void
    {
        $root = dirname(__DIR__, 2);
        $hits = [];
        foreach (['templates', 'wwwroot'] as $dir) {
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("{$root}/{$dir}"));
            foreach ($it as $file) {
                if ($file->isFile() && preg_match('/\.(php|js|html)$/', $file->getFilename())
                    && strpos((string) file_get_contents($file->getPathname()), 'bullet.php') !== false) {
                    $hits[] = substr($file->getPathname(), strlen($root) + 1);
                }
            }
        }
        $this->assertSame([], $hits, 'bullet.php is still referenced');
    }

    public function testThePartsImagePageSendsToBadmint(): void
    {
        $root = dirname(__DIR__, 2);
        $create = file_get_contents("{$root}/wwwroot/admin/parts/images/create.php");
        $this->assertStringContainsString('https://badmint.robnugen.com/mt3/?part=', $create);
        $this->assertStringNotContainsString('setTemplate', $create, 'create.php is a redirect, not a page');
        $index = file_get_contents("{$root}/templates/admin/parts/images/index.tpl.php");
        $this->assertStringContainsString('https://badmint.robnugen.com/mt3/?part=', $index);
        $this->assertFileDoesNotExist("{$root}/wwwroot/admin/parts/images/save_photos.php");
    }
}
