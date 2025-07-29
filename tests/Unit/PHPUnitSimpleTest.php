<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Simple test to verify PHPUnit installation and basic functionality
 */
class PHPUnitSimpleTest extends TestCase
{
    public function testPHPUnitIsWorking(): void
    {
        $this->assertTrue(true, 'PHPUnit is working!');
    }

    public function testBasicAssertions(): void
    {
        $this->assertEquals(4, 2 + 2);
        $this->assertIsString('hello');
        $this->assertIsArray([1, 2, 3]);
    }

    public function testRobRequestExists(): void
    {
        $this->assertTrue(class_exists('RobRequest'), 'RobRequest class should be available');
    }
}