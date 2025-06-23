<?php
namespace Database;

interface DbInterface {
    // This DbInterface is more of a proof of concept than anything useful, per se
    // I just want a bit of a check on class type as I move toward dependency injection
    public function insertFromRecord(string $tablename, string $paramtypes, $record);
    public function fetchResults($sql, $paramtypes = null, $var1 = null);
    public function executeSQL($sql, $paramtypes = null, $var1 = null);
    public function insertId(): ?int;
}
