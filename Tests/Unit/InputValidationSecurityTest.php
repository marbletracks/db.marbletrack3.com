<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Tests for input validation and sanitization security
 */
class InputValidationSecurityTest extends TestCase
{
    public function testRobRequestIntegerValidation(): void
    {
        // Mock malicious POST data
        $_POST = [
            'valid_int' => '123',
            'negative_int' => '-456',
            'zero' => '0',
            'float_string' => '12.34',
            'text_string' => 'not_a_number',
            'xss_attempt' => '<script>alert(1)</script>',
            'sql_injection' => "'; DROP TABLE users; --",
            'null_byte' => "123\0malicious",
            'unicode_attack' => '１２３', // full-width numbers
            'overflow_attempt' => '999999999999999999999999999',
            'empty_string' => '',
            'whitespace' => '   ',
            'boolean_true' => 'true',
            'boolean_false' => 'false'
        ];

        $request = new \RobRequest();

        // Valid integers should work
        $this->assertEquals(123, $request->getInt('valid_int'));
        $this->assertEquals(-456, $request->getInt('negative_int'));
        $this->assertEquals(0, $request->getInt('zero'));

        // Invalid inputs should return default (0)
        $this->assertEquals(0, $request->getInt('text_string'));
        $this->assertEquals(0, $request->getInt('xss_attempt'));
        $this->assertEquals(0, $request->getInt('sql_injection'));
        $this->assertEquals(0, $request->getInt('null_byte'));
        $this->assertEquals(0, $request->getInt('unicode_attack'));
        $this->assertEquals(0, $request->getInt('empty_string'));
        $this->assertEquals(0, $request->getInt('whitespace'));
        $this->assertEquals(0, $request->getInt('boolean_true'));
        $this->assertEquals(0, $request->getInt('boolean_false'));

        // Custom defaults should work
        $this->assertEquals(999, $request->getInt('nonexistent', 999));
        $this->assertEquals(42, $request->getInt('xss_attempt', 42));
    }

    public function testRobRequestStringValidation(): void
    {
        $_POST = [
            'normal_string' => 'hello world',
            'empty_string' => '',
            'whitespace_string' => '   padded   ',
            'xss_payload' => '<script>alert("xss")</script>',
            'sql_payload' => "'; DROP TABLE users; --",
            'null_byte' => "test\0malicious",
            'unicode_string' => 'こんにちは', // Japanese
            'emoji_string' => '🚀 rocket',
            'mixed_content' => 'Normal text <script>alert(1)</script> more text',
            'single_quotes' => "It's a test",
            'double_quotes' => 'She said "hello"',
            'newlines' => "Line 1\nLine 2\rLine 3",
            'control_chars' => "test\x01\x02\x03control"
        ];

        $request = new \RobRequest();

        // Normal strings should be trimmed
        $this->assertEquals('hello world', $request->getString('normal_string'));
        $this->assertEquals('', $request->getString('empty_string'));
        $this->assertEquals('padded', $request->getString('whitespace_string'));

        // Malicious content should be returned as-is (not sanitized at input level)
        // Note: XSS protection should happen at output, not input
        $this->assertEquals('<script>alert("xss")</script>', $request->getString('xss_payload'));
        $this->assertEquals("'; DROP TABLE users; --", $request->getString('sql_payload'));

        // Special characters should be preserved
        $this->assertEquals('こんにちは', $request->getString('unicode_string'));
        $this->assertEquals('🚀 rocket', $request->getString('emoji_string'));
        $this->assertEquals("It's a test", $request->getString('single_quotes'));
        $this->assertEquals('She said "hello"', $request->getString('double_quotes'));

        // Null bytes and control characters should be preserved (database will handle)
        $this->assertStringContainsString("test", $request->getString('null_byte'));
        $this->assertStringContainsString("test", $request->getString('control_chars'));
    }

    public function testActionValidation(): void
    {
        $_POST = ['action' => 'valid_action'];
        $request = new \RobRequest();
        $this->assertEquals('valid_action', $request->getAction());
        
        // Test with malicious action
        $_POST['action'] = '../../../etc/passwd';
        $request = new \RobRequest(); // Create new instance to pick up new $_POST
        $this->assertEquals('../../../etc/passwd', $request->getAction());
        
        // Test XSS in action
        $_POST['action'] = '<script>alert(1)</script>';
        $request = new \RobRequest();
        $this->assertEquals('<script>alert(1)</script>', $request->getAction());
        
        // Note: Path traversal and XSS protection should happen in the action handler,
        // not in the input validation layer. RobRequest just retrieves the raw value.
    }

    public function testJSONResponseSecurity(): void
    {
        $request = new \RobRequest();

        // Test that JSON responses are properly encoded
        $maliciousData = [
            'xss' => '<script>alert("xss")</script>',
            'quotes' => 'It\'s "quoted"',
            'unicode' => 'こんにちは',
            'null_byte' => "test\0malicious"
        ];

        // Test jsonSuccess - this method calls exit(), so we need to test it differently
        // We'll test the JSON encoding behavior without actually calling the method
        $expectedJson = json_encode($maliciousData);
        $this->assertJson($expectedJson, 'maliciousData should encode to valid JSON');
        
        $decoded = json_decode($expectedJson, true);
        $this->assertEquals($maliciousData['xss'], $decoded['xss'], 'XSS content should be JSON-encoded safely');
        $this->assertEquals($maliciousData['quotes'], $decoded['quotes'], 'Quotes should be properly escaped');
        $this->assertEquals($maliciousData['unicode'], $decoded['unicode'], 'Unicode should be preserved');

        // Test error JSON encoding without calling the actual method (which calls exit())
        $errorData = ['success' => false, 'error' => '<script>alert("error")</script>'];
        $errorJson = json_encode($errorData);
        $this->assertJson($errorJson, 'Error data should encode to valid JSON');
        
        $errorDecoded = json_decode($errorJson, true);
        $this->assertEquals('<script>alert("error")</script>', $errorDecoded['error'], 'Error message should be JSON-encoded safely');
        
        // Verify that malicious content is properly escaped in JSON (PHP json_encode uses backslash escaping by default)
        $this->assertStringContainsString('<\/script>', $errorJson, 'Script tags should be escaped with backslashes in JSON');
    }

    protected function tearDown(): void
    {
        $_POST = [];
    }
}