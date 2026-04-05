<?php
declare(strict_types=1);
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

$apiKey = new \Auth\ApiKey($mla_database);
$user_id = $is_logged_in->loggedInID();
$new_raw_key = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim($_POST['action'] ?? '');

    if ($action === 'generate') {
        $label = trim($_POST['label'] ?? '');
        $new_raw_key = $apiKey->generateKey($user_id, $label);
    } elseif ($action === 'revoke') {
        $key_id = (int) ($_POST['key_id'] ?? 0);
        if ($key_id > 0) {
            $apiKey->revokeKey($key_id, $user_id);
        }
        header("Location: /admin/settings/api_keys.php");
        exit;
    }
}

$keys = $apiKey->getKeysForUser($user_id);

$page = new \Template($config);
$page->setTemplate("admin/settings/api_keys.tpl.php");
$page->set("keys", $keys);
$page->set("new_raw_key", $new_raw_key);
$page->set("errors", $errors);
$inner = $page->grabTheGoods();

$layout = new \Template($config);
$layout->setTemplate("layout/admin_base.tpl.php");
$layout->set("page_title", "API Keys");
$layout->set("page_content", $inner);
$layout->echoToScreen();
