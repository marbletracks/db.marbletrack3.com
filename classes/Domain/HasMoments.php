<?php
namespace Domain;

use Database\DbInterface;
use Database\MomentRepository;
use Media\Moment;

trait HasMoments
{
    private array $moments = [];

    abstract public function getId(): int;
    abstract public function getDb(): DbInterface;
    abstract public function getMomentLinkingTable(): string;
    abstract public function getPrimaryKeyColumn(): string;

    public function addMoment(Moment $moment): void {
        $this->moments[] = $moment;
    }

    /** @return Moment[] */
    public function getMoments(): array {
        return $this->moments;
    }

    public function loadMoments(): void {
        $this->moments = [];
        $table = $this->getMomentLinkingTable();
        $key = $this->getPrimaryKeyColumn();
        $id = $this->getId();

        $results = $this->getDb()->fetchResults(
            "SELECT m.* FROM moments m JOIN {$table} p2m ON m.moment_id = p2m.moment_id WHERE p2m.{$key} = ? ORDER BY p2m.sort_order ASC",
            'i',
            [$id]
        );

        $momentRepo = new MomentRepository($this->getDb());
        for ($i = 0; $i < $results->numRows(); $i++) {
            $results->setRow($i);
            // Using findById to ensure moments are fully hydrated with their own relations if any (like photos)
            $moment = $momentRepo->findById((int)$results->data['moment_id']);
            if ($moment) {
                $this->addMoment($moment);
            }
        }
    }

    public function saveMoments(array $moment_ids): void {
        $table = $this->getMomentLinkingTable();
        $key = $this->getPrimaryKeyColumn();
        $id = $this->getId();

        $this->getDb()->executeSQL("DELETE FROM {$table} WHERE {$key} = ?", 'i', [$id]);

        foreach ($moment_ids as $index => $moment_id) {
            $this->getDb()->executeSQL(
                "INSERT INTO {$table} ({$key}, moment_id, sort_order) VALUES (?, ?, ?)",
                'iii',
                [$id, (int)$moment_id, $index]
            );
        }
    }
}
