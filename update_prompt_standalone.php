<?php
/**
 * Standalone Update Script: Fügt product_name zum User-Prompt hinzu
 *
 * WICHTIG: Dieses Script auf Ihren Gambio-Server hochladen!
 *
 * Hochladen nach: /GXModules/REDOzone/AIProductOptimizer/update_prompt_standalone.php
 *
 * Dann im Browser aufrufen:
 * https://ihre-domain.de/GXModules/REDOzone/AIProductOptimizer/update_prompt_standalone.php
 */

// Sicherheit: Nur einmalig ausführbar, dann löschen!
$executed_file = __DIR__ . '/.prompt_updated';
if (file_exists($executed_file)) {
    die('<h2>✓ Script wurde bereits ausgeführt!</h2><p>Bitte löschen Sie diese Datei vom Server:<br><code>' . __FILE__ . '</code></p>');
}

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Prompt Update</title></head><body>";
echo "<h2>AI Product Optimizer - Prompt Update</h2>\n";
echo "<pre>\n";

// Gambio-Konfiguration laden
$gambio_root = dirname(dirname(dirname(dirname(__DIR__))));
$config_file = $gambio_root . '/includes/application_top.php';

if (!file_exists($config_file)) {
    echo "✗ FEHLER: Gambio nicht gefunden.\n";
    echo "Erwarteter Pfad: $config_file\n\n";
    echo "ALTERNATIVE LÖSUNG:\n";
    echo "===================\n";
    echo "Gehen Sie in den Gambio Admin:\n";
    echo "Module → AI Product Optimizer → Konfiguration\n\n";
    echo "Löschen Sie das Feld 'User Prompt' komplett (leer lassen)\n";
    echo "und klicken Sie auf 'Speichern'.\n\n";
    echo "Das System verwendet dann den neuen Default-Prompt mit product_name Support.\n";
    echo "</pre></body></html>";
    exit;
}

chdir($gambio_root);
include($config_file);

// Aktuellen Prompt laden
$query = "SELECT gm_value FROM gm_configuration WHERE gm_key = 'OPENAI_USER_PROMPT' LIMIT 1";
$result = xtc_db_query($query);
$currentPrompt = '';

if ($row = xtc_db_fetch_array($result)) {
    $currentPrompt = $row['gm_value'];
}

if (empty($currentPrompt)) {
    echo "✓ Kein benutzerdefinierter Prompt gespeichert.\n";
    echo "✓ System verwendet bereits den Default-Prompt mit product_name Support.\n";
    echo "\n<strong>Keine Aktion erforderlich!</strong>\n";
} else {
    // Prüfe ob product_name bereits vorhanden
    if (strpos($currentPrompt, '"product_name"') !== false) {
        echo "✓ Prompt enthält bereits 'product_name' Feld.\n";
        echo "\n<strong>Keine Aktion erforderlich!</strong>\n";
    } else {
        echo "⚠ Alter Prompt gefunden (ohne product_name Support).\n\n";
        echo "Führe Update durch...\n\n";

        // Reset: Leeren damit Default verwendet wird
        $query = "UPDATE gm_configuration SET gm_value = '' WHERE gm_key = 'OPENAI_USER_PROMPT'";
        xtc_db_query($query);

        echo "✓ Prompt zurückgesetzt.\n";
        echo "✓ System verwendet jetzt den Default-Prompt mit product_name Support.\n\n";
        echo "<strong style='color: green;'>UPDATE ERFOLGREICH!</strong>\n";

        // Markiere als ausgeführt
        file_put_contents($executed_file, date('Y-m-d H:i:s'));
    }
}

echo "\n";
echo "==============================================\n";
echo "Update abgeschlossen!\n";
echo "==============================================\n\n";
echo "<strong>WICHTIG:</strong> Löschen Sie diese Datei jetzt vom Server:\n";
echo "<code>" . __FILE__ . "</code>\n";
echo "</pre>\n";
echo "</body></html>";
?>
