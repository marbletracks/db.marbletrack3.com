<?php

declare(strict_types=1);

// Unit test bootstrap: load project classes without prepend.php, which
// connects to the database. Unit tests must not.

$projectRoot = dirname(__DIR__, 2);

require_once $projectRoot . '/classes/Mlaphp/Autoloader.php';
spl_autoload_register([new \Mlaphp\Autoloader(), 'load']);

// The real Config.php is per-server and gitignored; in a fresh clone only the
// sample exists, and it declares the same \Config class.
if (!file_exists($projectRoot . '/classes/Config.php')) {
    require_once $projectRoot . '/classes/ConfigSample.php';
}
