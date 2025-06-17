<?php
namespace Database;

const CONFIG_DATABASE_OUTPUT_ENCODING = "UTF8";

class Base{
    private static $db;

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

    public static function getDB(\Config $config) : \Database\Database
    {
        self::initDB($config);
        return self::$db;
    }

}
