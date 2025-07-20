<?php
// File: /wwwroot/admin/moments/realtime.php

declare(strict_types=1);
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

$workers_repo = new \Database\WorkersRepository($mla_database, 'en');
$moment_repo = new \Database\MomentRepository($mla_database);
$tokens_repo = new \Database\TokensRepository($mla_database);
$workers = $workers_repo->findAll();

foreach ($workers as $worker) {
    $worker->moments = $moment_repo->findLatestForWorker($worker->worker_id, 2);
    $tokens = $tokens_repo->findForWorker($worker->worker_id);
    $worker->phrases = \Physical\PhraseBuilder::groupTokensIntoPhrases($tokens);
}

$page = new \Template($config);
$page->setTemplate('admin/moments/realtime.tpl.php');
$page->set('title', 'Realtime Moments');
$page->set('workers', $workers);
$page->set('page_title', 'Realtime Moments');
$inner = $page->grabTheGoods();

$layout = new \Template($config);
$layout->setTemplate("layout/admin_base.tpl.php");
$layout->set("page_title", "Realtime Moments");
$layout->set("page_content", $inner);
$layout->echoToScreen();

