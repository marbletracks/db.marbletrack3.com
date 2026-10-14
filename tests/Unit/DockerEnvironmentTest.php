<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Test to verify Docker environment setup
 */
class DockerEnvironmentTest extends TestCase
{
    public function testPHPVersion(): void
    {
        $this->assertGreaterThanOrEqual('8.1', phpversion(), 'PHP version should be 8.1 or higher');
    }

    public function testDatabaseEnvironmentVariables(): void
    {
        $this->assertEquals('mysql', $_ENV['DB_HOST'] ?? '');
        $this->assertEquals('3306', $_ENV['DB_PORT'] ?? '');
        $this->assertEquals('dbmt3_test', $_ENV['DB_NAME'] ?? '');
        $this->assertEquals('root', $_ENV['DB_USER'] ?? '');
        $this->assertEquals('mt3_test_pass', $_ENV['DB_PASSWORD'] ?? '');
    }

    public function testMySQLExtensionLoaded(): void
    {
        $this->assertTrue(extension_loaded('mysqli'), 'MySQLi extension should be loaded');
        $this->assertTrue(extension_loaded('pdo'), 'PDO extension should be loaded');
        $this->assertTrue(extension_loaded('pdo_mysql'), 'PDO MySQL extension should be loaded');
    }

    public function testDatabaseConnection(): void
    {
        $host = $_ENV['DB_HOST'] ?? 'mysql';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $dbname = $_ENV['DB_NAME'] ?? 'dbmt3_test';
        $username = $_ENV['DB_USER'] ?? 'root';
        $password = $_ENV['DB_PASSWORD'] ?? 'mt3_test_pass';

        try {
            $pdo = new \PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // The real production schema must have loaded
            $count = (int) $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()")->fetchColumn();
            $this->assertGreaterThanOrEqual(40, $count, 'Docker DB should have the full production schema');

            // An empty result is fine; it just must not throw
            $pdo->query("SELECT 1 FROM photos LIMIT 1")->fetchAll();
        } catch (\PDOException $e) {
            $this->fail("Database connection failed: " . $e->getMessage());
        }
    }
}