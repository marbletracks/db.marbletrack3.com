<?php
declare(strict_types=1);

include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

$take_id = intval(value: $mla_request->get['take_id']) ?? 0;
$filter = trim($_GET['filter'] ?? '');

// Missing-data checkboxes (multi-select); whitelist enforced in repo
$missing = [];
if (isset($_GET['missing']) && is_array($_GET['missing'])) {
    $missing = array_values(array_filter($_GET['missing'], 'is_string'));
}

// Sort param; whitelist enforced in repo
$sort = trim($_GET['sort'] ?? '');

$partRepo = new \Database\PartsRepository($mla_database, "en");
$workerRepo = new \Database\WorkersRepository($mla_database, "en");
$marbleRepo = new \Database\MarblesRepository($mla_database);

$repo = new \Database\MomentRepository($mla_database);
$result = $repo->findFiltered($filter, $take_id, $missing, $sort);
$moments = $result['moments'];
$total = $result['total'];

foreach ($moments as $key => $moment) {
    $moment->notes = $partRepo->expandShortcodesForBackend($moment->notes, "part", "en");
    $moment->notes = $workerRepo->expandShortcodesForBackend($moment->notes, "worker", "en");
    $moment->notes = $marbleRepo->expandShortcodesForBackend($moment->notes, "marble", "en");
}
$page = new \Template(config: $config);
$page->setTemplate(template_file: "admin/moments/index.tpl.php");
$page->set(name: "moments", value: $moments);
$page->set(name: "take_id", value: $take_id);
$page->set(name: "filter", value: $filter);
$page->set(name: "missing", value: $missing);
$page->set(name: "sort", value: $sort);
$page->set(name: "total", value: $total);
$page->set(name: "page_title", value: "Moment Index");
$inner = $page->grabTheGoods();

$layout = new \Template(config: $config);
$layout->setTemplate(template_file: "layout/admin_base.tpl.php");
$layout->set(name: "page_title", value: "Moment Index");
$layout->set(name: "page_content", value: $inner);
$layout->echoToScreen();
