<?php
namespace Database;

use Media\Take;

class TakeRepository
{
    private DbInterface $db;

    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    public function findAll(): array
    {
        $results = $this->db->fetchResults(
            "SELECT take_id, take_name FROM takes ORDER BY take_name ASC"
        );

        $takes = [];
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            $takes[] = $this->hydrate($results->data);
        }

        return $takes;
    }

    private function hydrate(array $row): Take
    {
        return new Take(
            take_id: (int) $row['take_id'],
            take_name: $row['take_name']
        );
    }
}
