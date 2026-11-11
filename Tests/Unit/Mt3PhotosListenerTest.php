<?php

use PHPUnit\Framework\TestCase;

/**
 * The Moment form takes photos back from badmint by postMessage (mg #709). The
 * contract that matters is the origin check; JS has no harness here, so this pins
 * the strings that carry it and that the template wires the script in.
 */
class Mt3PhotosListenerTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__, 2);
    }

    public function testTheListenerAcceptsOnlyBadmintAndOnlyItsMessage(): void
    {
        $js = file_get_contents("{$this->root}/wwwroot/admin/js/mt3-photos.js");
        $this->assertNotFalse($js);
        $this->assertStringContainsString("const BADMINT = 'https://badmint.robnugen.com';", $js);
        $this->assertStringContainsString('if (event.origin !== BADMINT) return;', $js);
        $this->assertStringContainsString("data.type !== 'mt3-photos' || data.v !== 1", $js);
        $this->assertStringContainsString("startsWith('https://b.robnugen.com/')", $js);
    }

    public function testASecondClickFocusesTheOpenPopupInsteadOfReloadingIt(): void
    {
        // Reopening by window name would navigate badmint's window and drop its unsaved queue.
        $js = file_get_contents("{$this->root}/wwwroot/admin/js/mt3-photos.js");
        $this->assertNotFalse($js);
        $this->assertStringContainsString('if (popup && !popup.closed) { popup.focus(); return; }', $js);
        $this->assertStringContainsString('allow popups', $js, 'a blocked popup is reported, not silent');
    }

    public function testTheButtonIsNotInsideTheImageUrlsLabel(): void
    {
        // A <button> inside the <label> becomes its labeled control, so a click on the
        // text or a thumbnail would open (or reload) badmint.
        $tpl = file_get_contents("{$this->root}/templates/admin/moments/moment.tpl.php");
        $this->assertNotFalse($tpl);
        $this->assertSame(1, preg_match('#<label>\s*Image URLs:(.*?)</label>#s', $tpl, $m));
        $this->assertStringNotContainsString('mt3-photos-open', $m[1]);
    }

    public function testTheMomentFormWiresTheButtonAndTheScript(): void
    {
        $tpl = file_get_contents("{$this->root}/templates/admin/moments/moment.tpl.php");
        $this->assertNotFalse($tpl);
        $this->assertStringContainsString('id="mt3-photos-open"', $tpl);
        $this->assertStringContainsString('data-moment-id="<?= (int) ($moment->moment_id ?? 0) ?: \'\' ?>"', $tpl);
        $this->assertStringContainsString('<script src="/admin/js/mt3-photos.js" defer></script>', $tpl);
        $this->assertStringContainsString('window.mt3PhotosPerspectives(data.perspectives || [])', $tpl);
    }
}
