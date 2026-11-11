<?php
namespace Database;

interface DbInterface {
    // This DbInterface is more of a proof of concept than anything useful, per se
    // I just want a bit of a check on class type as I move toward dependency injection
    /**
     * @param array<string, mixed> $record column => value
     * @return int|string|null the insert id, when the table makes one
     */
    public function insertFromRecord(string $tablename, string $paramtypes, array $record);
    /**
     * @param array<string, mixed> $record column => value
     * @return int|string|null
     */
    public function updateFromRecord(string $tablename, string $paramtypes, array $record, string $where);
    /**
     * @param string $sql
     * @param string|null $paramtypes mysqli bind types, one letter per value
     * @param mixed $var1 a bind value, or an array of them; more may follow
     * @return ResultSetObject
     */
    public function fetchResults($sql, $paramtypes = null, $var1 = null);
    /**
     * @param string $sql
     * @param string|null $paramtypes mysqli bind types, one letter per value
     * @param mixed $var1 a bind value, or an array of them; more may follow
     * @return int|string|null the insert id, when the statement made one
     */
    public function executeSQL($sql, $paramtypes = null, $var1 = null);
    public function insertId(): ?int;
    public function getAffectedRows(): int;
    public function beginTransaction(): void;
    public function commit(): void;
    public function rollBack(): void;
    public function inTransaction(): bool;
}
