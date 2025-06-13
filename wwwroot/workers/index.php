<?php
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

$workers = Worker::loadAllWorkers($mla_database);

$page = new \Template(config: $config);
$page->setTemplate("workers/index.tpl.php");
$page->set(name: "workers", value: $workers);
$page->set(name: "page_title", value: "Meet the Workers of Marble Track 3");
$page->echoToScreen();
