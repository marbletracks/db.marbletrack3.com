<?php

class Utilities {

    public static function randomString(
        int $length,
        string $possible = ""
    ): string
    {
        $randString = "";
        // define possible characters
        if (empty($possible)) {
            $possible = "0123456789abcdfghjkmnopqrstuvwxyzABCDEFGHJKLMNOPQRSTUVWXYZ";
        }
        // add random characters
        for ($i = 0; $i < $length; $i++) {
            // pick a random character from the possible ones
            $char = substr($possible, random_int(0, strlen($possible) - 1), 1);
            $randString .= $char;
        }
        return $randString;
    }

    /**
     * Convert a string to a slug for use in URLs
     * 
     * @param string $text The text to slugify
     * @param int $maxLength Maximum length of the resulting slug
     * @return string The slugified text
     */
    public static function slugify(string $text, int $maxLength = 200): string
    {
        // Convert to lowercase
        $slug = strtolower($text);
        
        // Convert accented characters to ASCII equivalents
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug);
        
        // Replace non-alphanumeric characters with dashes
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        
        // Remove leading and trailing dashes
        $slug = trim($slug, '-');
        
        // Limit to max length
        if (strlen($slug) > $maxLength) {
            $slug = substr($slug, 0, $maxLength);
            // Ensure we don't cut off in the middle of a word
            $slug = rtrim($slug, '-');
        }
        
        return $slug;
    }

    /**
     * Used in DBExistaroo::applyMigration() as
     * $path = \Utilities::getSchemaFilePath($this->config->app_path, $versionWithFile);
     *
     * @param string $appPath
     * @param string $versionWithFile
     * @throws \Exception
     * @return bool|string
     */
    public static function getSchemaFilePath(string $appPath, string $versionWithFile): string {
        // Sanitize and validate relative path
        if (empty($versionWithFile)) {
            throw new \Exception("Migration version with file cannot be empty.");
        }
        if (strpos($versionWithFile, '..') !== false) {
            throw new \Exception("Invalid migration path (traversal not allowed): $versionWithFile");
        }
        if (!preg_match('#^[0-9]{2}_[a-zA-Z0-9_-]+/create_[a-zA-Z0-9_-]+\.sql$#', $versionWithFile)) {
            throw new \Exception("Invalid migration path format: $versionWithFile");
        }

        $fullPath = $appPath . "/db_schemas/" . $versionWithFile;

        // Resolve real paths and check containment
        $realBase = realpath($appPath . "/db_schemas");
        $realTarget = realpath($fullPath);

        if (!$realTarget || strpos($realTarget, $realBase) !== 0) {
            throw new \Exception("Resolved path escapes base directory: $versionWithFile");
        }

        if (!file_exists(filename: $realTarget)) {
            throw new \Exception(message: "Migration file does not exist: $realTarget");
        }

        return $realTarget;
    }
}
