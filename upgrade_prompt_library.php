<?php
/* --------------------------------------------------------------
   upgrade_prompt_library.php 2025-11-08
   REDOzone
   http://www.redozone.com

   Upgrade-Skript für Prompt-Library:
   Erweitert die Tabelle um prompt_type für Vision/ALT-Text-Prompts
   --------------------------------------------------------------
*/

chdir('../../');
require_once('includes/application_top.php');

echo "<h1>Prompt Library Upgrade</h1>\n";
echo "<pre>\n";

// 1. Prüfe ob prompt_type Spalte bereits existiert
$checkSql = "SHOW COLUMNS FROM `rz_ai_prompt_library` LIKE 'prompt_type'";
$result = xtc_db_query($checkSql);

if (xtc_db_num_rows($result) == 0) {
    echo "✓ Adding prompt_type column...\n";

    $alterSql = "ALTER TABLE `rz_ai_prompt_library`
        ADD COLUMN `prompt_type` VARCHAR(20) NOT NULL DEFAULT 'product' COMMENT 'Typ: product oder vision' AFTER `prompt_id`,
        ADD INDEX `idx_prompt_type` (`prompt_type`)";

    xtc_db_query($alterSql);
    echo "  → Column 'prompt_type' added successfully\n";
} else {
    echo "ℹ Column 'prompt_type' already exists\n";
}

// 2. Setze prompt_type für existierende Einträge auf 'product'
$updateSql = "UPDATE `rz_ai_prompt_library` SET `prompt_type` = 'product' WHERE `prompt_type` = '' OR `prompt_type` IS NULL";
xtc_db_query($updateSql);
$affected = xtc_db_affected_rows();
echo "✓ Updated $affected existing entries to type 'product'\n";

echo "\n";
echo "========================================\n";
echo "Upgrade completed successfully!\n";
echo "========================================\n";
echo "</pre>\n";

require_once('includes/application_bottom.php');
?>
