<?php
namespace Database;

const CONFIG_DATABASE_OUTPUT_ENCODING = "UTF8";

class Base{
    private static $db;
    private static $testDb;

    // One day \Database\Base should be replaced with a dependency injection wrapper, but not today.
    // e.g. https://github.com/mlaphp/mlaphp/blob/master/src/Mlaphp/Di.php
    private static function initDB(\Config $config): void{
        /** START - Database **/
        if(empty(self::$db)){
            self::$db = new \Database\Database(
                host: $config->dbHost,
                username: $config->dbUser,
                passwd: $config->dbPass,
                dbname: $config->dbName,
                charEncoding: CONFIG_DATABASE_OUTPUT_ENCODING
            );
        }
        /** END - Database **/
    }

    private static function initTestDB(\Config $config): void{
        /** START - Test Database **/
        if(empty(self::$testDb)){
            self::$testDb = new \Database\Database(
                host: $config->dbHost,
                username: $config->dbUser,
                passwd: $config->dbPass,
                dbname: $config->dbName,
                charEncoding: CONFIG_DATABASE_OUTPUT_ENCODING
            );
        }
        /** END - Test Database **/
    }

    public static function getDB(\Config $config) : \Database\Database
    {
        self::initDB($config);
        return self::$db;
    }

    /**
     * Get a test database connection that is separate from the main production database
     * This allows tests to use a different database without affecting the production connection
     */
    public static function getTestDB(\Config $config) : \Database\Database
    {
        self::initTestDB($config);
        return self::$testDb;
    }

    /**
     * Reset the test database connection (useful for testing with different configurations)
     */
    public static function resetTestDB(): void
    {
        self::$testDb = null;
    }

}
