<?php

use Database\DbInterface;

/** Passes everything to a real connection and counts the statements sent. */
class CountingDb implements DbInterface
{
    public int $queries = 0;

    public function __construct(protected DbInterface $inner)
    {
    }

    public function insertFromRecord(string $tablename, string $paramtypes, $record)
    {
        $this->queries++;
        return $this->inner->insertFromRecord($tablename, $paramtypes, $record);
    }

    public function updateFromRecord(string $tablename, string $paramtypes, $record, string $where)
    {
        $this->queries++;
        return $this->inner->updateFromRecord($tablename, $paramtypes, $record, $where);
    }

    public function fetchResults($sql, $paramtypes = null, $var1 = null)
    {
        $this->queries++;
        return $this->inner->fetchResults($sql, $paramtypes, $var1);
    }

    public function executeSQL($sql, $paramtypes = null, $var1 = null)
    {
        $this->queries++;
        return $this->inner->executeSQL($sql, $paramtypes, $var1);
    }

    public function insertId(): ?int
    {
        return $this->inner->insertId();
    }

    public function getAffectedRows(): int
    {
        return $this->inner->getAffectedRows();
    }

    public function beginTransaction(): void
    {
        $this->inner->beginTransaction();
    }

    public function commit(): void
    {
        $this->inner->commit();
    }

    public function rollBack(): void
    {
        $this->inner->rollBack();
    }
}
