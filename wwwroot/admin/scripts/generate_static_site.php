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

echo "<h1>Static Site Generator</h1>";
echo "<pre>";

// 1. Load the configuration
$configFile = $config->app_path . '/prompts/generator_config.yaml';
if (!file_exists($configFile)) {
    die("Error: Configuration file not found at {$configFile}");
}

// Use a simple regex-based parser if PECL yaml is not installed
function simple_yaml_parse(string $yaml_content): array {
    $map = [];
    $current_section = '';
    $current_entity = '';

    foreach (explode("\n", $yaml_content) as $line) {
        if (preg_match('/^(\w+):$/', $line, $matches)) {
            $current_section = $matches[1];
            $map[$current_section] = [];
            $current_entity = '';
        } elseif (preg_match('/^  (\w+):$/', $line, $matches) && ($current_section === 'entities' || $current_section === 'indexes')) {
            $current_entity = $matches[1];
            $map[$current_section][$current_entity] = [];
        } elseif (preg_match('/^    (\w+): (.+)/ ', $line, $matches)) {
            if ($current_entity) {
                $map[$current_section][$current_entity][trim($matches[1])] = trim($matches[2]);
            } else {
                 $map[$current_section][trim($matches[1])] = trim($matches[2]);
            }
        }
    }
    return $map;
}

$config_data = function_exists('yaml_parse_file') ? yaml_parse_file($configFile) : simple_yaml_parse(file_get_contents($configFile));

if (!$config_data) {
    die("Error: Could not parse YAML configuration.");
}

echo "Configuration loaded successfully.\n\n";

$output_dir_prefix = $config->app_path . '/wwwroot/ai';

// 2. Generate Index Pages
if (isset($config_data['indexes'])) {
    foreach ($config_data['indexes'] as $indexName => $index) {
        echo "Generating index: {$indexName}...\n";

        $repoName = "\\Database\\" . $index['repository'];
        $repo = new $repoName($mla_database, $config_data['settings']['language_code']);
        $items = $repo->findAll();

        $tpl = new \Template($config);
        $tpl->setTemplate($index['template']);
        $tpl->set($index['entity'] . 's', $items); // e.g., set('workers', $workers)

        $output_path = $output_dir_prefix . $index['path'];
        if ($tpl->saveToFile($output_path)) {
            echo "  -> Saved to {$output_path}\n";
        } else {
            echo "  -> ERROR: Failed to save to {$output_path}\n";
        }
    }
}

// 3. Generate Entity (Detail) Pages
if (isset($config_data['entities'])) {
    foreach ($config_data['entities'] as $entityName => $entity) {
        echo "\nGenerating entity: {$entityName}...\n";

        $repoName = "\\Database\\" . $entity['repository'];
        $repo = new $repoName($mla_database, $config_data['settings']['language_code']);
        $items = $repo->findAll();

        foreach ($items as $item) {
            $path = str_replace('{slug}', $item->slug, $entity['path_schema']);
            $output_path = $output_dir_prefix . $path;

            $tpl = new \Template($config);
            $tpl->setTemplate($entity['template']);
            $tpl->set(strtolower($entityName), $item);

            if ($tpl->saveToFile($output_path)) {
                echo "  -> Saved to {$output_path}\n";
            } else {
                echo "  -> ERROR: Failed to save to {$output_path}\n";
            }
        }
    }
}

echo "\nGeneration complete!\n";
echo "</pre>";
