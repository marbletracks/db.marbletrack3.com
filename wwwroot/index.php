<?php

# Must include here because DH runs FastCGI https://www.phind.com/search?cache=zfj8o8igbqvaj8cm91wp1b7k
include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

$debugLevel = intval(value: $_GET['debug']) ?? 0;
if($debugLevel > 0) {
    echo "<pre>Debug Level: $debugLevel</pre>";
}

// Create the content template
$page = new \Template(config: $config);
$page->setTemplate("frontend/index.tpl.php");
$page->set(name: "site_version", value: SENTIMENTAL_VERSION);

// Set username if logged in, otherwise empty
if ($is_logged_in->isLoggedIn()) {
    $page->set(name: "username", value: $is_logged_in->getLoggedInUsername());
} else {
    $page->set(name: "username", value: "");
}

$inner = $page->grabTheGoods();

// Create the layout template
$layout = new \Template(config: $config);
$layout->setTemplate("layout/frontend_base.tpl.php");
$layout->set("page_title", "MarbleTrack3 - Home");
$layout->set("page_content", $inner);

// Set username for navigation 
if ($is_logged_in->isLoggedIn()) {
    $layout->set(name: "username", value: $is_logged_in->getLoggedInUsername());
} else {
    $layout->set(name: "username", value: "");
}

$layout->echoToScreen();
