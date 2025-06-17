<?php
namespace Physical;

final readonly class Worker
{
    public function __construct(
        public int $worker_id,
        public string $worker_alias,
        public string $name = '',
        public string $description = '',
    ) {
    }
}
