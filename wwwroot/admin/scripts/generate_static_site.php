<?php
declare(strict_types=1);

// File: /wwwroot/admin/scripts/generate_static_site.php

include_once "/home/dh_fbrdk3/db.marbletrack3.com/prepend.php";

if (!$is_logged_in->isLoggedIn()) {
    header("Location: /login/");
    exit;
}

// Set a higher time limit for this script, as it might take a while to run.
set_time_limit(300);

// 1. Load the configuration
$configFile = $config->app_path . '/prompts/generator_config.yaml';
if (!file_exists($configFile)) {
    die("Error: Configuration file not found at {$configFile}");
}

// Use a simple regex-based parser if PECL yaml is not installed
function simple_yaml_parse(string $yaml_content): array {
    $map = [];
    $lines = explode("\n", $yaml_content);
    $L0_key = '';
    $L1_key = '';

    foreach ($lines as $line) {
        $trimmed_line = trim($line);
        if ($trimmed_line === '' || str_starts_with($trimmed_line, '#')) continue;

        $indent = strlen($line) - strlen(ltrim($line));
        $line_content_without_comment = explode('#', $trimmed_line, 2)[0]; // Strip comments from the content
        $line_content_without_comment = trim($line_content_without_comment);

        if ($indent == 0) { // Level 0 Section (e.g., settings:, entities:)
            $L0_key = rtrim($line_content_without_comment, ':');
            $map[$L0_key] = [];
            $L1_key = ''; // Reset L1_key for new top-level section
        } elseif ($indent == 2) { // Level 1 (e.g.,   Worker: or   language_code: "en")
            if (str_ends_with($line_content_without_comment, ':')) { // It's a new sub-section/entity
                $L1_key = rtrim($line_content_without_comment, ':');
                $map[$L0_key][$L1_key] = [];
            } else { // It's a key-value pair directly under L0
                list($key, $value) = explode(':', $line_content_without_comment, 2);
                $key = trim($key);
                $value = trim($value, '"\' '); // Strip quotes
                $map[$L0_key][$key] = $value;
            }
        } elseif ($indent == 4) { // Level 2 Property (e.g.,     description: "...")
            list($key, $value) = explode(':', $line_content_without_comment, 2);
            $key = trim($key);
            $value = trim($value, '"\' '); // Strip quotes
            $map[$L0_key][$L1_key][$key] = $value;
        }
    }
    return $map;
}

function expandShortCodes(Database\DbInterface $mla_database, string $text, string $langCode): string
{

    $worker_repo = new \Database\WorkersRepository($mla_database, $langCode);
    $parts_repo = new \Database\PartsRepository($mla_database, $langCode);
    $expand_workers = $worker_repo->expandShortcodes($text, "worker", $langCode);
    $expand_parts = $parts_repo->expandShortcodes($expand_workers, "part", $langCode);

    return $expand_parts;
}

$config_data = function_exists('yaml_parse_file') ? yaml_parse_file($configFile) : simple_yaml_parse(file_get_contents($configFile));

if (!$config_data) {
    die("Error: Could not parse YAML configuration.");
}

$output_log = "<pre>\n";

$output_dir_prefix = $config->app_path . '/' . $config_data['settings']['output_directory'];

// 2. Generate Index Pages
if (isset($config_data['indexes'])) {
    foreach ($config_data['indexes'] as $indexName => $index) {
        $output_log .= "Generating index: " . $index['name'] . "...\n";

        $entityName = $index['entity'];
        if (!isset($config_data['entities'][$entityName]['repository'])) {
            $output_log .= "  -> ERROR: Repository not defined for entity '{$entityName}' in config.\n";
            continue;
        }

        $repoName = "\\Database\\" . $config_data['entities'][$entityName]['repository'];
        $repo = new $repoName($mla_database, $config_data['settings']['language_code']);
        $items = $repo->findAll();

        $inner_tpl = new \Template($config);
        $inner_tpl->setTemplate($index['template']);
        $inner_tpl->set(strtolower($entityName) . 's', $items); // e.g., set('workers', $workers)
        $inner_content = $inner_tpl->grabTheGoods();
        $longcode_content = expandShortcodes(
            mla_database: $mla_database,
            text: $inner_content,
            langCode: "en",
        );

        $layout_tpl = new \Template($config);
        $layout_tpl->setTemplate("layout/frontend_base.tpl.php");
        $layout_tpl->set("page_content", $longcode_content);
        $layout_tpl->set("page_title", $index['name']); // Use the index name as title

        $output_path = $output_dir_prefix . $index['path'];
        if ($layout_tpl->saveToFile($output_path)) {
            $output_log .= "  -> Saved to {$output_path}\n";
        } else {
            $output_log .= "  -> ERROR: Failed to save to {$output_path}\n";
        }
    }
}

// 3. Generate Entity (Detail) Pages
if (isset($config_data['entities'])) {
    foreach ($config_data['entities'] as $entityName => $entity) {
        $output_log .= "\nGenerating entity: {$entityName}...\n";

        $repoName = "\\Database\\" . $entity['repository'];
        $repo = new $repoName($mla_database, $config_data['settings']['language_code']);
        $items = $repo->findAll();

        foreach ($items as $item) {
            $path = str_replace('{slug}', $item->slug, $entity['path_schema']);
            $output_path = $output_dir_prefix . $path;


            $inner_tpl = new \Template($config);
            $inner_tpl->setTemplate($entity['template']);
            $inner_tpl->set(strtolower($entityName), $item);
            $inner_content = $inner_tpl->grabTheGoods();
            $longcode_content = expandShortcodes(
                mla_database: $mla_database,
                text: $inner_content,
                langCode: "en",
            );

            $layout_tpl = new \Template($config);
            $layout_tpl->setTemplate("layout/frontend_base.tpl.php");
            $layout_tpl->set("page_content", $longcode_content);
            // Attempt to derive a good page title from the item
            $page_title = $item->name ?? $item->worker_alias ?? $entityName;
            $layout_tpl->set("page_title", $page_title);

            if ($layout_tpl->saveToFile($output_path)) {
                $output_log .= "  -> Saved to {$output_path}\n";
            } else {
                $output_log .= "  -> ERROR: Failed to save to {$output_path}\n";
            }
        }
    }
}

$output_log .= "\nGeneration complete!\n";
$output_log .= "</pre>";

$tpl = new \Template(config: $config);
$tpl->setTemplate(template_file: "admin/generate_site_output.tpl.php");
$tpl->set(name: "log_output", value: $output_log);
$inner = $tpl->grabTheGoods();

$layout = new \Template(config: $config);
$layout->setTemplate(template_file: "layout/admin_base.tpl.php");
$layout->set(name: "page_title", value: "Static Site Generation Log");
$layout->set(name: "page_content", value: htmlspecialchars_decode($inner, ENT_QUOTES));
$layout->echoToScreen();
