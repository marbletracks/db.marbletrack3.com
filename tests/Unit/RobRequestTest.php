<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class RobRequestTest extends TestCase
{
    private \RobRequest $request;

    protected function setUp(): void
    {
        // Mock request data
        $_POST = [
            'test_int' => '123',
            'test_string' => 'hello world',
            'test_empty' => '',
            'test_zero' => '0',
            'malicious_script' => '<script>alert("xss")</script>',
            'sql_injection' => "'; DROP TABLE users; --"
        ];
        $_GET = [
            'page' => '5',
            'search' => 'test query'
        ];

        $this->request = new \RobRequest();
    }

    public function testGetInt(): void
    {
        $this->assertEquals(123, $this->request->getInt('test_int'));
        $this->assertEquals(0, $this->request->getInt('test_zero'));
        
        // Test default values
        $this->assertEquals(10, $this->request->getInt('nonexistent', 10));
        $this->assertEquals(0, $this->request->getInt('nonexistent'));
        
        // Security test: non-numeric input should return default
        $this->assertEquals(0, $this->request->getInt('malicious_script'));
    }

    public function testGetString(): void
    {
        $this->assertEquals('hello world', $this->request->getString('test_string'));
        $this->assertEquals('', $this->request->getString('test_empty'));
        
        // Test default values
        $this->assertEquals('default', $this->request->getString('nonexistent', 'default'));
        $this->assertEquals('', $this->request->getString('nonexistent'));
        
        // Security test: malicious input should be returned as-is (trimmed)
        // Note: RobRequest doesn't sanitize HTML - that should be done at output
        $this->assertEquals('<script>alert("xss")</script>', $this->request->getString('malicious_script'));
        $this->assertEquals("'; DROP TABLE users; --", $this->request->getString('sql_injection'));
    }

    public function testJsonResponseExists(): void
    {
        // Test that the JSON methods exist and are callable
        $this->assertTrue(method_exists($this->request, 'jsonSuccess'), 'jsonSuccess method should exist');
        $this->assertTrue(method_exists($this->request, 'jsonError'), 'jsonError method should exist');
        
        // Note: We can't easily test these methods because they call exit()
        // In a real application, these would be tested with integration tests
        // or by refactoring to return responses instead of calling exit()
    }

    public function testJsonDataEncoding(): void
    {
        // Test JSON encoding behavior manually (without calling the actual methods)
        $testData = ['message' => 'Success!', 'xss' => '<script>alert("test")</script>'];
        $json = json_encode($testData);
        
        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertEquals('Success!', $decoded['message']);
        $this->assertEquals('<script>alert("test")</script>', $decoded['xss']);
        
        // Verify XSS content is properly escaped in JSON (PHP uses backslash escaping by default)
        $this->assertStringContainsString('<\/script>', $json, 'Script tags should be escaped with backslashes in JSON');
        // Note: The content is still there but safely encoded - this is correct behavior
    }

    protected function tearDown(): void
    {
        $_POST = [];
        $_GET = [];
    }
}