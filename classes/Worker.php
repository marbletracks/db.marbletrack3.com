<?php

class Worker
{
    private int $worker_id;
    private string $worker_alias;
    private string $worker_name;
    private string $worker_description;
    private string $photo_code = "";

    public function __construct(
        private $di_dbase,
    ) {
    }

    public function loadFromDatabase(int $id): void
    {
        $query = "
            SELECT w.worker_id, w.worker_alias, wn.worker_name, wn.worker_description
            FROM `workers` w
            JOIN `worker_names` wn using (worker_id)
            WHERE w.worker_id = " . intval($id) . "
              AND wn.language_code = 'US'
        ";
        $result_set = $this->di_dbase->fetchResults($query);
        $result_set->toArray();

        if ($result_set->valid()) {
            $this->loadFromArray($result_set->current());
        }
    }

    public function loadFromArray($record): void
    {
        $this->worker_id = $record['worker_id'];
        $this->worker_alias = $record['worker_alias'];
        $this->worker_name = $record['worker_name'];
        $this->worker_description = $record['worker_description'];
        $this->photo_code = $record['photo_code'] ?? "";
    }

    public function getId(): int
    {
        return $this->worker_id;
    }
    public function getAlias(): string
    {
        return $this->worker_alias;
    }
    public function getName(): string
    {
        return $this->worker_name;
    }
    public function getDescription(): string
    {
        return $this->worker_description;
    }
    public function getPhotoCode(): string
    {
        return $this->photo_code;
    }

    public static function loadAllWorkers($di_dbase): array
    {
        // worker_id is sorted by their order of appearance in the movie
        $query = "
            SELECT w.worker_id, w.worker_alias, wn.worker_name, wn.worker_description, wp.photo_code
            FROM `workers` w
            JOIN `worker_names` wn USING (worker_id)
            LEFT JOIN workers_photos wp ON w.worker_id = wp.worker_id AND wp.is_primary = TRUE
            WHERE wn.language_code = 'en'
            ORDER BY wn.worker_id
        ";
        $result_set = $di_dbase->fetchResults($query);
        $result_set->toArray();

        $workers = [];
        foreach ($result_set as $record) {
            $worker = new self($di_dbase);
            $worker->loadFromArray($record);
            $workers[] = $worker;
        }
        return $workers;
    }
}
