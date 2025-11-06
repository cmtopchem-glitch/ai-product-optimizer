<?php
/**
 * Update Script: Fügt product_name zum User-Prompt hinzu
 *
 * Dieses Script aktualisiert den gespeicherten User-Prompt in der Datenbank,
 * damit product_name übersetzt wird.
 *
 * Verwendung:
 * php update_prompt.php
 *
 * Oder im Browser aufrufen:
 * http://ihre-domain.de/GXModules/REDOzone/AIProductOptimizer/update_prompt.php
 */

// Gambio-Konfiguration laden
chdir('../../../');
include('includes/application_top.php');

echo "<h2>AI Product Optimizer - Prompt Update</h2>\n";
echo "<pre>\n";

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
    echo "\nKeine Aktion erforderlich!\n";
} else {
    // Prüfe ob product_name bereits vorhanden
    if (strpos($currentPrompt, '"product_name"') !== false) {
        echo "✓ Prompt enthält bereits 'product_name' Feld.\n";
        echo "\nKeine Aktion erforderlich!\n";
    } else {
        echo "⚠ Alter Prompt gefunden (ohne product_name Support).\n\n";
        echo "Möchten Sie:\n";
        echo "[1] Prompt zurücksetzen (empfohlen - verwendet neuen Default-Prompt)\n";
        echo "[2] Prompt erweitern (versucht product_name automatisch hinzuzufügen)\n\n";

        // Automatische Auswahl im CLI-Modus
        if (php_sapi_name() === 'cli') {
            echo "Geben Sie 1 oder 2 ein: ";
            $choice = trim(fgets(STDIN));
        } else {
            // Browser-Modus: Immer Option 1 (sicherer)
            $choice = '1';
            echo "Automatische Auswahl: Option 1 (Reset)\n\n";
        }

        if ($choice == '1') {
            // Reset: Leeren damit Default verwendet wird
            $query = "UPDATE gm_configuration SET gm_value = '' WHERE gm_key = 'OPENAI_USER_PROMPT'";
            xtc_db_query($query);
            echo "✓ Prompt zurückgesetzt.\n";
            echo "✓ System verwendet jetzt den Default-Prompt mit product_name Support.\n";
        } elseif ($choice == '2') {
            // Versuche product_name hinzuzufügen
            $updatedPrompt = str_replace(
                '"product_description":',
                '"product_name": "Übersetzter Produktname in Zielsprache",'."\n".'  "product_description":',
                $currentPrompt
            );

            // Füge auch die Anforderung zur Übersetzung hinzu
            if (strpos($updatedPrompt, 'Produktname MUSS in die Zielsprache übersetzt werden') === false) {
                $updatedPrompt = str_replace(
                    'Keywords aus dem ORIGINAL-TEXT extrahieren und erweitern!',
                    'Keywords aus dem ORIGINAL-TEXT extrahieren und erweitern!'."\n".'- Produktname MUSS in die Zielsprache übersetzt werden!',
                    $updatedPrompt
                );
            }

            $query = "UPDATE gm_configuration SET gm_value = '" . xtc_db_input($updatedPrompt) . "' WHERE gm_key = 'OPENAI_USER_PROMPT'";
            xtc_db_query($query);
            echo "✓ Prompt erweitert.\n";
            echo "✓ product_name wurde zum JSON-Format hinzugefügt.\n";
        } else {
            echo "✗ Ungültige Auswahl. Abbruch.\n";
        }
    }
}

echo "\n";
echo "==============================================\n";
echo "Update abgeschlossen!\n";
echo "==============================================\n";
echo "</pre>\n";
?>
