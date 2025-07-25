<?php
/**
 * Unit tests for Moment class functionality
 */

use PHPUnit\Framework\TestCase;
use Media\Moment;

class MomentTest extends TestCase
{
    /**
     * Test Moment constructor and slug generation
     */
    public function testMomentConstructorAndSlugGeneration()
    {
        // Test with take_id and frame_start
        $moment1 = new Moment(
            moment_id: 1,
            frame_start: 100,
            frame_end: 150,
            take_id: 5,
            notes: 'Test moment',
            moment_date: '2023-01-01'
        );
        
        $this->assertEquals('take-5-frame-100', $moment1->slug);
        $this->assertEquals(1, $moment1->moment_id);
        $this->assertEquals(100, $moment1->frame_start);
        $this->assertEquals(150, $moment1->frame_end);
        $this->assertEquals(5, $moment1->take_id);
        $this->assertEquals('Test moment', $moment1->notes);
        $this->assertEquals('2023-01-01', $moment1->moment_date);
        $this->assertIsArray($moment1->photos);
        $this->assertEmpty($moment1->photos);

        // Test with only take_id
        $moment2 = new Moment(
            moment_id: 2,
            frame_start: null,
            frame_end: null,
            take_id: 3,
            notes: null,
            moment_date: null
        );
        
        $this->assertEquals('take-3', $moment2->slug);

        // Test with only frame_start
        $moment3 = new Moment(
            moment_id: 3,
            frame_start: 200,
            frame_end: null,
            take_id: null,
            notes: null,
            moment_date: null
        );
        
        $this->assertEquals('frame-200', $moment3->slug);

        // Test fallback to moment_id
        $moment4 = new Moment(
            moment_id: 4,
            frame_start: null,
            frame_end: null,
            take_id: null,
            notes: null,
            moment_date: null
        );
        
        $this->assertEquals('moment-4', $moment4->slug);
    }

    /**
     * Test edge cases for slug generation
     */
    public function testSlugGenerationEdgeCases()
    {
        // Test with frame_start but no take_id
        $moment1 = new Moment(
            moment_id: 10,
            frame_start: 0, // Zero should still be considered valid
            frame_end: null,
            take_id: null,
            notes: null,
            moment_date: null
        );
        
        $this->assertEquals('frame-0', $moment1->slug);

        // Test with take_id but no frame_start
        $moment2 = new Moment(
            moment_id: 11,
            frame_start: null,
            frame_end: 500,
            take_id: 0, // Zero should still be considered valid
            notes: null,
            moment_date: null
        );
        
        $this->assertEquals('take-0', $moment2->slug);

        // Test with both take_id and frame_start as zero
        $moment3 = new Moment(
            moment_id: 12,
            frame_start: 0,
            frame_end: null,
            take_id: 0,
            notes: null,
            moment_date: null
        );
        
        $this->assertEquals('take-0-frame-0', $moment3->slug);
    }
}