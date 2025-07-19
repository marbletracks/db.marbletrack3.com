<?php
declare(strict_types=1);

include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

$take_id = intval(value: $mla_request->get['take_id']) ?? 0;

$partRepo = new \Database\PartsRepository($mla_database, "en");
$workerRepo = new \Database\WorkersRepository($mla_database, "en");

$repo = new \Database\MomentRepository($mla_database);
$moments = [];
if($take_id > 0) {
    $moments = $repo->findWithinTakeId(take_id: $take_id);
} else {
    $moments = $repo->findAll();
}

foreach ($moments as $key => $moment) {
    $moment->notes = $partRepo->expandShortcodesForBackend($moment->notes, "part", "en");
    $moment->notes = $workerRepo->expandShortcodesForBackend($moment->notes, "worker", "en");
}
$page = new \Template(config: $config);
$page->setTemplate(template_file: "admin/moments/index.tpl.php");
$page->set(name: "moments", value: $moments);
$page->set(name: "take_id", value: $take_id);
$page->set(name: "page_title", value: "Moment Index");
$inner = $page->grabTheGoods();

$layout = new \Template(config: $config);
$layout->setTemplate(template_file: "layout/admin_base.tpl.php");
$layout->set(name: "page_title", value: "Moment Index");
$layout->set(name: "page_content", value: $inner);
$layout->echoToScreen();
