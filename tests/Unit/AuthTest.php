<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Auth\IPBin;

class AuthTest extends TestCase
{
    public function testIPToBinaryConversion(): void
    {
        // Test valid IPv4
        $binary = IPBin::ipToBinary('192.168.1.1');
        $this->assertNotEmpty($binary, 'Valid IPv4 should convert to binary');
        $this->assertEquals(4, strlen($binary), 'IPv4 binary should be 4 bytes');
        
        // Test valid IPv6
        $binary = IPBin::ipToBinary('2001:db8::1');
        $this->assertNotEmpty($binary, 'Valid IPv6 should convert to binary');
        $this->assertEquals(16, strlen($binary), 'IPv6 binary should be 16 bytes');
        
        // Security test: invalid IP should return empty string
        $binary = IPBin::ipToBinary('invalid.ip.address');
        $this->assertEquals('', $binary, 'Invalid IP should return empty string');
        
        // Security test: potential injection attempts
        $binary = IPBin::ipToBinary("192.168.1.1'; DROP TABLE users; --");
        $this->assertEquals('', $binary, 'SQL injection attempt should return empty string');
        
        $binary = IPBin::ipToBinary('<script>alert("xss")</script>');
        $this->assertEquals('', $binary, 'XSS attempt should return empty string');
    }

    public function testBinaryToIPConversion(): void
    {
        // Test IPv4 round-trip
        $originalIP = '192.168.1.100';
        $binary = IPBin::ipToBinary($originalIP);
        $convertedIP = IPBin::binaryToIP($binary);
        $this->assertEquals($originalIP, $convertedIP, 'IPv4 should round-trip correctly');
        
        // Test IPv6 round-trip
        $originalIP = '2001:db8::8a2e:370:7334';
        $binary = IPBin::ipToBinary($originalIP);
        $convertedIP = IPBin::binaryToIP($binary);
        $this->assertEquals($originalIP, $convertedIP, 'IPv6 should round-trip correctly');
        
        // Security test: invalid binary data
        $result = IPBin::binaryToIP('invalid_binary_data');
        $this->assertEquals('', $result, 'Invalid binary should return empty string');
        
        $result = IPBin::binaryToIP('');
        $this->assertEquals('', $result, 'Empty binary should return empty string');
    }

    public function testIPValidationSecurity(): void
    {
        // Test that only valid IPs are accepted
        $validIPs = [
            '127.0.0.1',
            '192.168.1.1',
            '203.0.113.45',
            '::1',
            '2001:db8::1'
        ];
        
        foreach ($validIPs as $ip) {
            $binary = IPBin::ipToBinary($ip);
            $this->assertNotEmpty($binary, "Valid IP $ip should convert to binary");
        }
        
        // Test that invalid/malicious IPs are rejected
        $invalidIPs = [
            'invalid.ip',
            '999.999.999.999',
            '256.256.256.256',
            "192.168.1.1'; DROP TABLE users; --",
            '<script>alert("xss")</script>',
            'javascript:alert(1)',
            '../../etc/passwd',
            'null',
            '',
            ' ',
            '0x7f000001' // hex representation of 127.0.0.1
        ];
        
        foreach ($invalidIPs as $ip) {
            $binary = IPBin::ipToBinary($ip);
            $this->assertEquals('', $binary, "Invalid/malicious IP '$ip' should return empty string");
        }
    }
}