<?php

function generateInsertSQL(array $partsList): string
{
    $sqlParts = [];
    $sqlTranslations = [];

    foreach ($partsList as $partName => $data) {
        $alias = $data['prefix'] ?? '';
        $description = $data['description'] ?? $partName;

        if (empty($alias))
            continue;

        // Escape strings for SQL
        $aliasEsc = addslashes($alias);
        $nameEsc = addslashes($partName);
        $descEsc = addslashes($description);

        $sqlParts[] = "INSERT INTO parts (part_alias) VALUES ('$aliasEsc');";
        $sqlTranslations[] = <<<SQL
INSERT INTO part_translations (part_id, language_code, part_name, part_description)
SELECT part_id, 'US', '$nameEsc', '$descEsc' FROM parts WHERE part_alias = '$aliasEsc';
SQL;
    }

    return implode("\n", $sqlParts) . "\n\n" . implode("\n", $sqlTranslations);
}

// Example usage:
$json = file_get_contents('/home/dh_fbrdk3/db.marbletrack3.com/scripts/mt3-snippets.code-snippets'); // JSON file with your list
$partsList = json_decode($json, true);

$sql = generateInsertSQL($partsList);

// Output to file or display
file_put_contents('create_parts_and_translations.sql', $sql);
echo "SQL generated successfully.\n";
