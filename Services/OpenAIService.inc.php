<?php
/* --------------------------------------------------------------
   OpenAIService.inc.php 2025-11-02
   REDOzone
   http://www.redozone.com
   Copyright (c) 2025 REDOzone
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

if (!class_exists('OpenAIService')) {
class OpenAIService
{
    private $apiKey;
    private $projectId;
    private $apiUrl = 'https://api.openai.com/v1/chat/completions';
    private $model = 'gpt-4o';
    private $systemPrompt;
    private $userPrompt;

    public function __construct($apiKey, $model = 'gpt-4o', $projectId = '')
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
        $this->projectId = $projectId;
        $this->loadPrompts();
    }
    
    /**
     * Lädt Prompts aus der Konfiguration oder Bibliothek
     * @param int|null $promptId Optional: ID eines Prompts aus der Bibliothek
     */
    private function loadPrompts($promptId = null)
    {
        // Wenn eine Prompt-ID angegeben wurde, lade aus der Bibliothek
        if ($promptId !== null) {
            require_once DIR_FS_CATALOG . 'GXModules/REDOzone/AIProductOptimizer/Services/PromptLibraryService.inc.php';
            $prompt = PromptLibraryService::getPromptById($promptId);

            if ($prompt) {
                $this->systemPrompt = $prompt['system_prompt'];
                $this->userPrompt = $prompt['user_prompt'];
                // Erhöhe Verwendungszähler
                PromptLibraryService::incrementUsageCount($promptId);
                return;
            }
        }

        // Versuche Default-Prompt aus der Bibliothek zu laden
        if (file_exists(DIR_FS_CATALOG . 'GXModules/REDOzone/AIProductOptimizer/Services/PromptLibraryService.inc.php')) {
            require_once DIR_FS_CATALOG . 'GXModules/REDOzone/AIProductOptimizer/Services/PromptLibraryService.inc.php';
            $defaultPrompt = PromptLibraryService::getDefaultPrompt();

            if ($defaultPrompt) {
                $this->systemPrompt = $defaultPrompt['system_prompt'];
                $this->userPrompt = $defaultPrompt['user_prompt'];
                // Erhöhe Verwendungszähler
                PromptLibraryService::incrementUsageCount($defaultPrompt['prompt_id']);
                return;
            }
        }

        // Lade System-Prompt aus Konfiguration
        $query = "SELECT gm_value FROM gm_configuration WHERE gm_key = 'OPENAI_SYSTEM_PROMPT' LIMIT 1";
        $result = xtc_db_query($query);
        if ($row = xtc_db_fetch_array($result)) {
            $this->systemPrompt = $row['gm_value'];
        }

        // Fallback wenn nicht konfiguriert
        if (empty($this->systemPrompt)) {
            $this->systemPrompt = "Du bist ein professioneller E-Commerce SEO-Texter mit strengen Qualitätsrichtlinien:\n\n" .
                "GRUNDREGELN:\n" .
                "1. Antworte IMMER im angeforderten JSON-Format (ohne Markdown-Blöcke)\n" .
                "2. Verwende sauberes, semantisches HTML im product_description Feld\n" .
                "3. Verwende NIEMALS <h1> Tags (nur <h2>, <h3> für Struktur)\n" .
                "4. Erhalte alle vorhandenen <img> und <iframe> Tags aus dem Original-Text\n\n" .
                "INHALTLICHE REGELN:\n" .
                "5. Erfinde KEINE Eigenschaften, Funktionen oder technische Daten\n" .
                "6. Verwende NUR Informationen aus dem bereitgestellten Original-Text\n" .
                "7. Wenn Informationen fehlen, lasse sie weg (keine Spekulationen)\n" .
                "8. Keine Call-to-Action am Textanfang (z.B. \"Entdecken Sie...\", \"Jetzt kaufen...\")\n" .
                "9. Beginne direkt mit informativen, sachlichen Produktinformationen\n\n" .
                "STRUKTURIERUNG:\n" .
                "10. Gliedere längere Texte mit aussagekräftigen <h2> Überschriften\n" .
                "11. Verwende beschreibende Überschriften (nicht \"Einleitung\", \"Fazit\", \"Zusammenfassung\")\n" .
                "12. Gute Beispiele: \"Technische Eigenschaften\", \"Anwendungsbereiche\", \"Lieferumfang\", \"Funktionsweise\"\n" .
                "13. Schlechte Beispiele: \"Übersicht\", \"Details\", \"Mehr Informationen\", \"Produktbeschreibung\"\n\n" .
                "PFLICHTFELDER:\n" .
                "14. Fülle ALLE JSON-Felder vollständig aus\n" .
                "15. meta_keywords MUSS 10-15 relevante Begriffe enthalten\n" .
                "16. search_keywords MUSS 8-12 Suchbegriffe enthalten\n" .
                "17. Beide Keyword-Felder dürfen NIEMALS leer sein\n\n" .
                "QUALITÄT:\n" .
                "18. Schreibe verkaufsstarke, aber sachliche Texte\n" .
                "19. Hebe echte Vorteile und Nutzen hervor (nur wenn im Original belegt)\n" .
                "20. Verwende natürliche, nicht übertriebene Sprache";
        }

        // Lade User-Prompt Template
        $query = "SELECT gm_value FROM gm_configuration WHERE gm_key = 'OPENAI_USER_PROMPT' LIMIT 1";
        $result = xtc_db_query($query);
        if ($row = xtc_db_fetch_array($result)) {
            $this->userPrompt = $row['gm_value'];
        }

        // Fallback wenn nicht konfiguriert
        if (empty($this->userPrompt)) {
            $this->userPrompt = $this->getDefaultUserPrompt();
        }
    }

    /**
     * Setzt einen Prompt aus der Bibliothek
     * @param int $promptId ID des Prompts aus der Bibliothek
     */
    public function usePromptFromLibrary($promptId)
    {
        $this->loadPrompts($promptId);
    }
    

/**
 * Standard User-Prompt - OPTIMIERT FÜR KEYWORDS & HTML-FORMATIERUNG
 */
private function getDefaultUserPrompt()
{
    return "Du bist ein Experte für E-Commerce SEO und Produkttexte.\n\n" .
           "Produkt: {PRODUCT_NAME}\n" .
           "{BRAND_LINE}{CATEGORY_LINE}" .
           "Original-Produkttext:\n{ORIGINAL_TEXT}\n\n" .
           "AUFGABE:\n" .
           "Erstelle einen SEO-optimierten Produkttext in {LANGUAGE}. Beachte folgende Anforderungen:\n\n" .
           "1. PRODUKTNAME (ZWINGEND in Zielsprache übersetzen!)\n" .
           "   - Der Produktname MUSS IMMER in die Zielsprache {LANGUAGE} übersetzt werden!\n" .
           "   - Behalte nur Markennamen (z.B. 'Bosch', 'Tornador') und Artikelnummern bei\n" .
           "   - Übersetze beschreibende Teile des Produktnamens vollständig\n" .
           "   - Beispiele:\n" .
           "     * 'Foam Gun Tornador' → 'Schaumkanone Tornador' (auf Deutsch)\n" .
           "     * 'Foam Gun Tornador' → 'Pistolet à mousse Tornador' (auf Französisch)\n" .
           "     * 'High Pressure Cleaner PRO-2000' → 'Hochdruckreiniger PRO-2000' (auf Deutsch)\n\n" .
           "2. PRODUKTBESCHREIBUNG (300-500 Wörter) - MIT HTML-FORMATIERUNG\n" .
           "   PFLICHT-STRUKTUR für ALLE Sprachen:\n" .
           "   - Beginne NICHT mit Call-to-Action (\"Entdecken Sie...\", \"Jetzt kaufen...\")\n" .
           "   - Beginne direkt mit informativen, sachlichen Produktinformationen\n" .
           "   - Verwende mindestens 2-3 aussagekräftige <h2> Zwischenüberschriften\n" .
           "   - Gute Überschriften: \"Technische Eigenschaften\", \"Anwendungsbereiche\", \"Lieferumfang\"\n" .
           "   - Schlechte Überschriften: \"Einleitung\", \"Fazit\", \"Übersicht\", \"Details\"\n" .
           "   - NIEMALS <h1> Tags verwenden!\n" .
           "   - Alle Absätze in <p> Tags\n" .
           "   - Aufzählungen mit <ul> und <li>\n" .
           "   - Wichtige Begriffe mit <strong> hervorheben\n" .
           "   - Erfinde KEINE Eigenschaften - nur Infos aus Original-Text verwenden\n\n" .
           "   WICHTIG - MEDIA-TAGS:\n" .
           "   - Platzhalter [[MEDIA_TAG_X]] MÜSSEN EXAKT übernommen werden!\n" .
           "   - Setze diese an passende Stellen im Text\n\n" .
           "3. META TITLE (max. 60 Zeichen)\n" .
           "   - Prägnant und klickstark\n" .
           "   - Hauptkeyword am Anfang\n" .
           "   - In Zielsprache {LANGUAGE}\n\n" .
           "4. META DESCRIPTION (max. 160 Zeichen)\n" .
           "   - Überzeugende Beschreibung\n" .
           "   - Wichtigste USPs nennen\n" .
           "   - In Zielsprache {LANGUAGE}\n\n" .
           "5. META KEYWORDS (10-15 Begriffe, Komma-separiert, PFLICHTFELD!)\n" .
           "   - Hauptkeyword: Produktname bzw. Produkttyp\n" .
           "   - Verwandte Begriffe: Synonyme, Kategorien\n" .
           "   - Long-Tail Keywords: 2-3 Wort-Kombinationen\n" .
           "   - In Zielsprache {LANGUAGE}\n" .
           "   - DARF NICHT leer sein!\n\n" .
           "6. SHOP SUCHWORTE (8-12 Begriffe, Komma-separiert, PFLICHTFELD!)\n" .
           "   - Suchbegriffe die Kunden tatsächlich eingeben würden\n" .
           "   - Umgangssprache und Synonyme\n" .
           "   - In Zielsprache {LANGUAGE}\n" .
           "   - DARF NICHT leer sein!\n\n" .
           "QUALITÄTSKRITERIEN:\n" .
           "✓ product_name VOLLSTÄNDIG in {LANGUAGE} übersetzt (nur Marken/Nummern behalten!)\n" .
           "✓ product_description mit validem HTML: <p>, <h2>, <ul>, <li>, <strong>\n" .
           "✓ PFLICHT: Mindestens 2-3 <h2> Überschriften in JEDER Beschreibung - auch in Französisch, Spanisch, etc.!\n" .
           "✓ NIEMALS <h1> verwenden!\n" .
           "✓ Keine Call-to-Action am Textanfang\n" .
           "✓ Platzhalter [[MEDIA_TAG_X]] exakt übernehmen\n" .
           "✓ meta_keywords mit 10-15 Begriffen gefüllt (niemals leer!)\n" .
           "✓ search_keywords mit 8-12 Begriffen gefüllt (niemals leer!)\n" .
           "✓ Alle Texte vollständig in {LANGUAGE}\n\n" .
           "FORMAT DER ANTWORT (NUR JSON, KEINE MARKDOWN-BLÖCKE):\n" .
           "{\n" .
           '  "product_name": "VOLLSTÄNDIG ÜBERSETZTER Produktname in {LANGUAGE}",'."\n" .
           '  "product_description": "<p>Einleitung...</p><h2>Technische Eigenschaften</h2><ul><li>Merkmal 1</li></ul>[[MEDIA_TAG_0]]<h2>Anwendungsbereiche</h2><p>Text...</p>",'."\n" .
           '  "meta_title": "SEO Meta-Titel in {LANGUAGE} (max 60 Zeichen)",'."\n" .
           '  "meta_description": "Meta-Description in {LANGUAGE} (max 160 Zeichen)",'."\n" .
           '  "meta_keywords": "keyword1, keyword2, keyword3, keyword4, keyword5, keyword6, keyword7, keyword8, keyword9, keyword10",'."\n" .
           '  "search_keywords": "suchwort1, suchwort2, suchwort3, suchwort4, suchwort5, suchwort6, suchwort7, suchwort8"'."\n" .
           "}\n\n" .
           "Antworte NUR mit dem JSON-Objekt, ohne zusätzlichen Text oder Markdown-Blöcke!";
}
    
    /**
     * Generiert SEO-optimierten Text für ein Produkt
     */
    public function generateSEOContent($productName, $originalText, $languageCode, $additionalData = array())
    {
        // Hole Sprachname dynamisch aus DB
        $languageName = $this->getLanguageName($languageCode);

        // SCHRITT 1: Extrahiere Media-Tags und ersetze sie mit Platzhaltern
        $mediaStorage = array();
        $textWithPlaceholders = $this->extractMediaTags($originalText, $mediaStorage);

        // SCHRITT 2: Generiere optimierten Content (ohne Media-Tags)
        $prompt = $this->buildPrompt($productName, $textWithPlaceholders, $languageName, $additionalData);
        $response = $this->callOpenAI($prompt);

        // SCHRITT 3: Parse Response
        $result = $this->parseResponse($response);

        // SCHRITT 4: Stelle Media-Tags in der Beschreibung wieder her
        $result['description'] = $this->restoreMediaTags($result['description'], $mediaStorage);

        return $result;
    }

    /**
     * Holt Sprachname aus Datenbank
     */
    private function getLanguageName($languageCode)
    {
        $query = "SELECT name FROM languages WHERE code = '" . xtc_db_input($languageCode) . "' LIMIT 1";
        $result = xtc_db_query($query);

        if ($row = xtc_db_fetch_array($result)) {
            return $row['name'];
        }

        return ucfirst($languageCode); // Fallback
    }
    
    /**
     * Erstellt den Prompt für OpenAI
     */
    private function buildPrompt($productName, $originalText, $language, $additionalData)
    {
        $category = isset($additionalData['category']) ? $additionalData['category'] : '';
        $brand = isset($additionalData['brand']) ? $additionalData['brand'] : '';
        
        // Ersetze Platzhalter im User-Prompt
        $prompt = $this->userPrompt;
        $prompt = str_replace('{PRODUCT_NAME}', $productName, $prompt);
        $prompt = str_replace('{ORIGINAL_TEXT}', $originalText, $prompt);
        $prompt = str_replace('{LANGUAGE}', $language, $prompt);
        
        // Brand und Category optional
        if ($brand) {
            $prompt = str_replace('{BRAND_LINE}', "Marke: {$brand}\n", $prompt);
        } else {
            $prompt = str_replace('{BRAND_LINE}', '', $prompt);
        }
        
        if ($category) {
            $prompt = str_replace('{CATEGORY_LINE}', "Kategorie: {$category}\n", $prompt);
        } else {
            $prompt = str_replace('{CATEGORY_LINE}', '', $prompt);
        }
        
        return $prompt;
    }
    
    /**
     * Extrahiert Media-Tags (img, video, iframe) und ersetzt sie mit Platzhaltern
     * @param string $text Original-Text mit Media-Tags
     * @param array &$storage Referenz zum Array für die gespeicherten Tags
     * @return string Text mit Platzhaltern statt Media-Tags
     */
    private function extractMediaTags($text, &$storage)
    {
        $storage = array();
        $counter = 0;

        // Extrahiere <img> Tags (auch selbstschließend)
        $text = preg_replace_callback(
            '/<img[^>]*>/i',
            function($matches) use (&$storage, &$counter) {
                $placeholder = '[[MEDIA_TAG_' . $counter . ']]';
                $storage[$placeholder] = $matches[0];
                $counter++;
                return $placeholder;
            },
            $text
        );

        // Extrahiere <video>...</video> Tags
        $text = preg_replace_callback(
            '/<video[^>]*>.*?<\/video>/is',
            function($matches) use (&$storage, &$counter) {
                $placeholder = '[[MEDIA_TAG_' . $counter . ']]';
                $storage[$placeholder] = $matches[0];
                $counter++;
                return $placeholder;
            },
            $text
        );

        // Extrahiere <iframe>...</iframe> Tags
        $text = preg_replace_callback(
            '/<iframe[^>]*>.*?<\/iframe>/is',
            function($matches) use (&$storage, &$counter) {
                $placeholder = '[[MEDIA_TAG_' . $counter . ']]';
                $storage[$placeholder] = $matches[0];
                $counter++;
                return $placeholder;
            },
            $text
        );

        return $text;
    }

    /**
     * Stellt Media-Tags aus den Platzhaltern wieder her
     * @param string $text Text mit Platzhaltern
     * @param array $storage Array mit gespeicherten Media-Tags
     * @return string Text mit wiederhergestellten Media-Tags
     */
    private function restoreMediaTags($text, $storage)
    {
        // Ersetze alle Platzhalter wieder durch die Original-Tags
        foreach ($storage as $placeholder => $mediaTag) {
            $text = str_replace($placeholder, $mediaTag, $text);
        }

        return $text;
    }

    /**
     * Ruft die OpenAI API auf
     */
    private function callOpenAI($prompt)
    {
        $data = array(
            'model' => $this->model,
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => $this->systemPrompt
                ),
                array(
                    'role' => 'user',
                    'content' => $prompt
                )
            ),
            'temperature' => 0.7,
            'max_tokens' => 2000
        );

        // Baue API-URL mit optionalem Project-Parameter
        $apiUrl = $this->apiUrl;
        if (!empty($this->projectId)) {
            $apiUrl .= '?project=' . urlencode($this->projectId);
        }

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception('cURL Error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        if ($httpCode !== 200) {
            $error = json_decode($response, true);
            $errorMsg = isset($error['error']['message']) ? $error['error']['message'] : 'HTTP ' . $httpCode;
            throw new Exception('OpenAI API Error: ' . $errorMsg);
        }
        
        return $response;
    }
    
    /**
     * Parst die OpenAI-Antwort
     */
    private function parseResponse($response)
    {
        $data = json_decode($response, true);
        
        if (!isset($data['choices'][0]['message']['content'])) {
            throw new Exception('Ungültige API-Antwort');
        }
        
        $content = $data['choices'][0]['message']['content'];
        $content = trim($content);
        $content = preg_replace('/^```json\s*/', '', $content);
        $content = preg_replace('/\s*```$/', '', $content);
        
        $result = json_decode($content, true);
        
        if (!$result || !isset($result['product_description'])) {
            throw new Exception('Fehler beim Parsen der Antwort');
        }
        
        return array(
            'product_name' => isset($result['product_name']) ? $result['product_name'] : '',
            'description' => $result['product_description'],
            'meta_title' => isset($result['meta_title']) ? $result['meta_title'] : '',
            'meta_description' => isset($result['meta_description']) ? $result['meta_description'] : '',
            'meta_keywords' => isset($result['meta_keywords']) ? $result['meta_keywords'] : '',
            'search_keywords' => isset($result['search_keywords']) ? $result['search_keywords'] : ''
        );
    }
}
}
