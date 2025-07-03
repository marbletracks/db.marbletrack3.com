<?php

declare(strict_types=1);

# Must include here because DH runs FastCGI https://www.phind.com/search?cache=zfj8o8igbqvaj8cm91wp1b7k
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

// Repository knows how to connect to the database
$repo = new \Database\WorkersRepository(
    db: $mla_database,
    langCode: 'en',
);

// Fetch workers
$workers = $repo->findAll();

$page = new \Template(config: $config);
$page->setTemplate("frontend/workers/index.tpl.php");
$page->set(name: "workers", value: $workers);
$page->set(name: "page_title", value: "Meet the Workers of Marble Track 3");
$page->echoToScreen();
