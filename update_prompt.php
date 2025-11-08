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
    echo "✓ System verwendet bereits den aktuellen Default-Prompt.\n\n";
    echo "Default-Prompt Features:\n";
    echo "  ✓ Produktname-Übersetzung mit Beispielen\n";
    echo "  ✓ HTML-Formatierung (h2, p, ul, li, strong, em)\n";
    echo "  ✓ Zwischenüberschriften-Struktur (2-3 pro Text)\n";
    echo "  ✓ Keyword-Hervorhebung (5-7 wichtige Begriffe)\n";
    echo "  ✓ Media-Tag Preservation\n";
    echo "\nKeine Aktion erforderlich!\n";
} else {
    // Prüfe ob neuer Prompt (mit HTML-Formatierungs-Anweisungen)
    $hasNewFormatting = (strpos($currentPrompt, 'HTML-FORMATIERUNG') !== false ||
                         strpos($currentPrompt, 'KEYWORD-HERVORHEBUNG') !== false);

    if ($hasNewFormatting) {
        echo "✓ Prompt ist bereits aktuell.\n";
        echo "✓ Enthält HTML-Formatierungs- und Keyword-Hervorhebungs-Anweisungen.\n";
        echo "\nKeine Aktion erforderlich!\n";
    } else {
        echo "⚠ Veralteter Prompt gefunden.\n\n";
        echo "NEUER PROMPT BIETET:\n";
        echo "  ✓ Verbesserte Produktname-Übersetzung mit konkreten Beispielen\n";
        echo "  ✓ Pflicht-HTML-Formatierung (h2, p, ul, li, strong, em)\n";
        echo "  ✓ Strukturvorgaben für Zwischenüberschriften (2-3 pro Text)\n";
        echo "  ✓ Keyword-Hervorhebung (5-7 wichtige Begriffe mit <strong>)\n";
        echo "  ✓ Qualitätskriterien-Checkliste\n\n";
        echo "Empfehlung: Prompt zurücksetzen um neue Features zu nutzen.\n\n";
        echo "Möchten Sie:\n";
        echo "[1] Prompt zurücksetzen (empfohlen - verwendet verbesserten Default-Prompt)\n";
        echo "[2] Behalten (aktuellen Prompt nicht ändern)\n\n";

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
            echo "✓ System verwendet jetzt den verbesserten Default-Prompt.\n\n";
            echo "NÄCHSTE SCHRITTE:\n";
            echo "  1. Testen Sie die Optimierung mit einem Produkt\n";
            echo "  2. Achten Sie auf HTML-Formatierung im Output\n";
            echo "  3. Prüfen Sie ob product_name übersetzt wird\n";
            echo "  4. Keywords sollten mit <strong> hervorgehoben sein\n";
        } elseif ($choice == '2') {
            echo "✓ Prompt wurde nicht geändert.\n";
            echo "Hinweis: Um die neuen Features zu nutzen, führen Sie das Script erneut aus und wählen Option 1.\n";
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
