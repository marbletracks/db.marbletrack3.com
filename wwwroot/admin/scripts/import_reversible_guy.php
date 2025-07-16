<?php
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

use Database\Database;
use Database\WorkersRepository;
use Database\MomentRepository;

$workersRepo = new WorkersRepository($mla_database, 'en');
$momentRepo = new MomentRepository($mla_database);

// 1. Get the worker
$worker = $workersRepo->findByAlias('rg');

if (!$worker) {
    echo "Worker 'reversible' not found.\n";
    exit(1);
}

echo "Found worker: " . $worker->name . " (ID: " . $worker->worker_id . ")\n";

// 2. Update the worker's description
$description = "After moving this guy around, I noticed that he looks just about the same whether facing forwards or backwards so there was a gag during which he goes just off the set and was magically going back onto the set without having had time to turn around. But via the magic of the fact that this is not real he was already turned around.";
$workersRepo->update($worker->worker_id, $worker->worker_alias, $worker->name, $description);
echo "Updated description for " . $worker->name . "\n";


// 3. Import historical items as moments
$historical_items = [
    [
        'date' => '2019-06-22',
        'note' => 'Reversible Guy held the Lower Zig Zag 3F while autosticks came to become the Lower Zig Zag 3F Lower Support.'
    ],
    [
        'date' => '2018-09-22',
        'note' => 'Here, Reversible Guy is waiting for the glue to dry while he is holding the first support that will hold up the Outer Spiral.'
    ]
];

foreach ($historical_items as $item) {
    $moment_id = $momentRepo->insert(
        notes: $item['note'],
        moment_date: $item['date']
    );

    echo "Created moment with ID: " . $moment_id . "\n";

    $momentRepo->createTranslationIfNotExists($moment_id, $worker->worker_id, 'worker');

    echo "Created translation for moment ID: " . $moment_id . " and worker ID: " . $worker->worker_id . "\n";
}

echo "Import complete.\n";
