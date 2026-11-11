<?php

require_once __DIR__ . '/CountingDb.php';

use Database\DbInterface;

/** A real connection whose Nth INSERT/UPDATE/DELETE throws, to simulate a save dying partway. */
class FailingDb extends CountingDb
{
    private int $writes = 0;

    public function __construct(DbInterface $inner, private int $failOnWrite)
    {
        parent::__construct($inner);
    }

    public function executeSQL($sql, $paramtypes = null, $var1 = null)
    {
        if (preg_match('/^\s*(INSERT|UPDATE|DELETE)\b/i', $sql) && ++$this->writes === $this->failOnWrite) {
            throw new \Database\EDatabaseException("Injected failure on write #{$this->writes}");
        }
        return parent::executeSQL($sql, $paramtypes, $var1);
    }
}
