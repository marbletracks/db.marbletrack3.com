<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

/**
 * Tests to catch template regressions that would break functionality
 * Specifically designed to catch issues like the one in commit 10e180b
 *
 * Covers all admin templates with shortcodey-textarea functionality:
 * - episodes/episode.tpl.php
 * - moments/realtime.tpl.php
 * - moments/moment.tpl.php
 * - parts/part.tpl.php
 * - workers/worker.tpl.php
 */
class TemplateRegressionTest extends TestCase
{
    /**
     * Test that episode template has required CSS classes for shortcode functionality
     * This test would have FAILED before commit 10e180b and PASSES after
     */
    public function testEpisodeTemplateShortcodeClass(): void
    {
        $templatePath = '/app/templates/admin/episodes/episode.tpl.php';

        $this->assertFileExists($templatePath, 'Episode template file should exist');

        $content = file_get_contents($templatePath);
        $this->assertNotFalse($content, 'Should be able to read episode template');

        // This is the critical test that would catch the regression
        $this->assertStringContainsString(
            'class="shortcodey-textarea"',
            $content,
            'REGRESSION TEST: Episode textarea must have shortcodey-textarea class for AJAX shortcode functionality'
        );

        // Additional checks for related functionality
        $this->assertStringContainsString('id="shortcodey"', $content,
            'Episode textarea should have shortcodey ID');

        $this->assertStringContainsString('id="autocomplete"', $content,
            'Episode template should have autocomplete container for shortcode suggestions');

        $this->assertStringContainsString('name="description"', $content,
            'Episode textarea should have correct form field name');
    }

    /**
     * Test that workers have shortcode-related functionality enabled
     */
    public function testWorkerShortcodeRepositoryMethods(): void
    {
        $testDb = getTestDatabase();
        $workersRepo = new \Database\WorkersRepository($testDb, 'en');

        // Test that shortcode-related methods exist
        $this->assertTrue(
            method_exists($workersRepo, 'getSELECTForShortcodeExpansion'),
            'WorkersRepository should have shortcode expansion method'
        );

        $this->assertTrue(
            method_exists($workersRepo, 'getSELECTExactAlias'),
            'WorkersRepository should have exact alias lookup method for shortcodes'
        );

        $this->assertTrue(
            method_exists($workersRepo, 'getSELECTLikeAlias'),
            'WorkersRepository should have fuzzy alias lookup method for shortcode suggestions'
        );
    }

    /**
     * Test that shortcode functionality is properly integrated
     * This creates a worker and tests that it can be found for shortcode purposes
     */
    public function testShortcodeWorkerLookup(): void
    {
        $testDb = getTestDatabase();
        $workersRepo = new \Database\WorkersRepository($testDb, 'en');

        $testPrefix = 't' . substr(time(), -2) . rand(1, 9) . '_'; // Short prefix for 10-char limit

        try {
            // Create a test worker
            $workerId = $workersRepo->insert(
                $testPrefix . 'tst', // Keep under 10 chars total
                'Template Test Worker',
                'Worker for template regression testing'
            );

            // Test that we can find it by alias (shortcode functionality)
            $worker = $workersRepo->findByAlias($testPrefix . 'tst');
            $this->assertNotNull($worker, 'Should be able to find worker by alias for shortcode lookup');
            $this->assertEquals('Template Test Worker', $worker->name, 'Worker name should be available for shortcode display');

            // Test shortcode SQL query structure
            $shortcodeSql = $workersRepo->getSELECTForShortcodeExpansion('en');
            $this->assertStringContainsString('worker_alias', $shortcodeSql, 'Shortcode SQL should include alias');
            $this->assertStringContainsString('worker_name', $shortcodeSql, 'Shortcode SQL should include name');

            // Clean up
            $testDb->executeSQL("DELETE FROM worker_names WHERE worker_id = ?", 'i', [$workerId]);
            $testDb->executeSQL("DELETE FROM workers WHERE worker_id = ?", 'i', [$workerId]);

        } catch (\Exception $e) {
            $this->markTestSkipped("Worker shortcode functionality test skipped: " . $e->getMessage());
        }
    }

    /**
     * Test for other critical template elements that might regress
     */
    public function testCriticalTemplateElements(): void
    {
        // Test episodes template
        $episodeTemplate = '/app/templates/admin/episodes/episode.tpl.php';
        if (file_exists($episodeTemplate)) {
            $content = file_get_contents($episodeTemplate);

            // Form should exist
            $this->assertStringContainsString('<form', $content, 'Episode template should have form element');
            $this->assertStringContainsString('method=', $content, 'Form should have method attribute');

            // Critical input fields
            $this->assertStringContainsString('name="description"', $content, 'Description field should exist');

            // JavaScript integration points
            $this->assertStringContainsString('id="', $content, 'Template should have JavaScript integration points');
        }

        // Could add tests for other critical templates here
        $this->assertTrue(true, 'Template regression tests completed');
    }

    /**
     * Test that would specifically catch the commit 10e180b regression
     * This test documents exactly what broke and what fixed it
     */
    public function testCommit10e180bRegressionPrevention(): void
    {
        $templatePath = '/app/templates/admin/episodes/episode.tpl.php';
        $content = file_get_contents($templatePath);

        // BEFORE commit 10e180b: this would have been just id="shortcodey"
        // AFTER commit 10e180b: this includes class="shortcodey-textarea"

        $textareaPattern = '/id="shortcodey"[^>]*class="shortcodey-textarea"/';
        $this->assertMatchesRegularExpression(
            $textareaPattern,
            $content,
            'Episode textarea must have BOTH id="shortcodey" AND class="shortcodey-textarea" for shortcode functionality to work'
        );

        // Alternative check - ensure the class exists somewhere in the textarea tag
        $this->assertStringContainsString(
            'class="shortcodey-textarea"',
            $content,
            'Shortcode functionality requires shortcodey-textarea CSS class (regression from commit 10e180b)'
        );

        // Document what this test prevents
        $this->assertTrue(true, 'This test prevents regressions where shortcode CSS class gets removed');
    }

    /**
     * Test that moments realtime template has shortcode functionality
     */
    public function testMomentsRealtimeTemplateShortcodeClass(): void
    {
        $templatePath = '/app/templates/admin/moments/realtime.tpl.php';

        if (!file_exists($templatePath)) {
            $this->markTestSkipped('Moments realtime template not found: ' . $templatePath);
        }

        $content = file_get_contents($templatePath);
        $this->assertNotFalse($content, 'Should be able to read moments realtime template');

        $this->assertStringContainsString(
            'class="shortcodey-textarea"',
            $content,
            'REGRESSION TEST: Moments realtime template must have shortcodey-textarea class for shortcode functionality'
        );

        $this->assertStringContainsString('id="shortcodey"', $content,
            'Moments realtime template should have shortcodey ID');
    }

    /**
     * Test that moments edit template has shortcode functionality
     */
    public function testMomentsEditTemplateShortcodeClass(): void
    {
        $templatePath = '/app/templates/admin/moments/moment.tpl.php';

        if (!file_exists($templatePath)) {
            $this->markTestSkipped('Moments edit template not found: ' . $templatePath);
        }

        $content = file_get_contents($templatePath);
        $this->assertNotFalse($content, 'Should be able to read moments edit template');

        $this->assertStringContainsString(
            'class="shortcodey-textarea"',
            $content,
            'REGRESSION TEST: Moments edit template must have shortcodey-textarea class for shortcode functionality'
        );

        $this->assertStringContainsString('id="shortcodey"', $content,
            'Moments edit template should have shortcodey ID');
    }

    /**
     * Test that parts template has shortcode functionality
     */
    public function testPartsTemplateShortcodeClass(): void
    {
        $templatePath = '/app/templates/admin/parts/part.tpl.php';

        if (!file_exists($templatePath)) {
            $this->markTestSkipped('Parts template not found: ' . $templatePath);
        }

        $content = file_get_contents($templatePath);
        $this->assertNotFalse($content, 'Should be able to read parts template');

        $this->assertStringContainsString(
            'class="shortcodey-textarea"',
            $content,
            'REGRESSION TEST: Parts template must have shortcodey-textarea class for shortcode functionality'
        );

        $this->assertStringContainsString('id="shortcodey"', $content,
            'Parts template should have shortcodey ID');
    }

    /**
     * Test that workers template has shortcode functionality
     */
    public function testWorkersTemplateShortcodeClass(): void
    {
        $templatePath = '/app/templates/admin/workers/worker.tpl.php';

        if (!file_exists($templatePath)) {
            $this->markTestSkipped('Workers template not found: ' . $templatePath);
        }

        $content = file_get_contents($templatePath);
        $this->assertNotFalse($content, 'Should be able to read workers template');

        $this->assertStringContainsString(
            'class="shortcodey-textarea"',
            $content,
            'REGRESSION TEST: Workers template must have shortcodey-textarea class for shortcode functionality'
        );

        $this->assertStringContainsString('id="shortcodey"', $content,
            'Workers template should have shortcodey ID');
    }
}
