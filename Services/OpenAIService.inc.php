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
    private $apiUrl = 'https://api.openai.com/v1/chat/completions';
    private $model = 'gpt-4o';
    private $systemPrompt;
    private $userPrompt;
    
    public function __construct($apiKey, $model = 'gpt-4o')
    {
        $this->apiKey = $apiKey;
        $this->model = $model;
        $this->loadPrompts();
    }
    
    /**
     * Lädt Prompts aus der Konfiguration
     */
    private function loadPrompts()
    {
        // Lade System-Prompt
        $query = "SELECT gm_value FROM gm_configuration WHERE gm_key = 'OPENAI_SYSTEM_PROMPT' LIMIT 1";
        $result = xtc_db_query($query);
        if ($row = xtc_db_fetch_array($result)) {
            $this->systemPrompt = $row['gm_value'];
        }
        
        // Fallback wenn nicht konfiguriert
        if (empty($this->systemPrompt)) {
            $this->systemPrompt = 'Du bist ein professioneller E-Commerce SEO-Texter. Du antwortest immer im angeforderten JSON-Format.';
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
 * Standard User-Prompt - OPTIMIERT FÜR KEYWORDS
 */
private function getDefaultUserPrompt()
{
    return "Du bist ein E-Commerce SEO-Experte.\n\n" .
           "PRODUKT: {PRODUCT_NAME}\n" .
           "{BRAND_LINE}" .
           "{CATEGORY_LINE}" .
           "\nORIGINAL-TEXT:\n{ORIGINAL_TEXT}\n\n" .
           "AUFGABE:\n" .
           "Erstelle SEO-optimierten Content in {LANGUAGE} mit folgenden Elementen:\n\n" .
           "1. PRODUKTBESCHREIBUNG (300-500 Wörter)\n" .
           "   - Verkaufsstarker Text mit klarer Struktur\n" .
           "   - Vorteile und Nutzen hervorheben\n" .
           "   - Keywords natürlich integrieren\n\n" .
           "2. META TITLE (max. 60 Zeichen)\n" .
           "   - Prägnant und klickstark\n" .
           "   - Hauptkeyword am Anfang\n\n" .
           "3. META DESCRIPTION (max. 160 Zeichen)\n" .
           "   - Call-to-Action enthalten\n" .
           "   - Wichtigste USPs nennen\n\n" .
           "4. META KEYWORDS (10-15 Begriffe, Komma-separiert)\n" .
           "   - Hauptkeyword: Produktname bzw. Produkttyp\n" .
           "   - Verwandte Begriffe: Synonyme, Kategorien\n" .
           "   - Long-Tail Keywords: 2-3 Wort-Kombinationen\n" .
           "   - Marken-Keywords falls relevant\n" .
           "   - Technische Begriffe aus der Beschreibung\n" .
           "   Beispiel: \"Schaumkanone, Foam Gun, Tornador, Autoreinigung, Hochdruckreiniger, Druckluft Schaumpistole, Fahrzeugpflege, Schaumreiniger, Auto Waschen, Detailing Equipment\"\n\n" .
           "5. SHOP SUCHWORTE (8-12 Begriffe, Komma-separiert)\n" .
           "   - Suchbegriffe die Kunden tatsächlich eingeben würden\n" .
           "   - Umgangssprache und Synonyme\n" .
           "   - Häufige Tippfehler und Varianten\n" .
           "   - Kombinationen mit \"kaufen\", \"günstig\", etc.\n" .
           "   Beispiel: \"schaum pistole, foam lance auto, druckluft schaum, reinigungsschaum auto, schaum gerät waschen, tornador alternative, auto schäumer, fahrzeug schaum reiniger\"\n\n" .
           "WICHTIG:\n" .
           "- meta_keywords und search_keywords MÜSSEN gefüllt sein!\n" .
           "- Beide Felder MÜSSEN mindestens 5 Begriffe enthalten!\n" .
           "- Keywords aus dem ORIGINAL-TEXT extrahieren und erweitern!\n\n" .
           "ANTWORT-FORMAT (NUR JSON, KEINE MARKDOWN-BLÖCKE):\n" .
           "{\n" .
           '  "product_description": "Optimierter HTML-Text mit <p>, <h2>, <ul>, <strong>",'."\n" .
           '  "meta_title": "SEO Meta-Titel (max 60 Zeichen)",'."\n" .
           '  "meta_description": "Meta-Description (max 160 Zeichen)",'."\n" .
           '  "meta_keywords": "keyword1, keyword2, keyword3, keyword4, keyword5, keyword6, keyword7, keyword8, keyword9, keyword10",'."\n" .
           '  "search_keywords": "suchwort1, synonym1, suchwort2, variante1, suchwort3, kombination1, suchwort4, suchwort5"'."\n" .
           "}\n\n" .
           "Antworte NUR mit dem JSON-Objekt (keine ```json Blöcke)!";
}
    
    /**
     * Generiert SEO-optimierten Text für ein Produkt
     */
    public function generateSEOContent($productName, $originalText, $languageCode, $additionalData = array())
    {
        // Hole Sprachname dynamisch aus DB
        $languageName = $this->getLanguageName($languageCode);
        
        $prompt = $this->buildPrompt($productName, $originalText, $languageName, $additionalData);
        $response = $this->callOpenAI($prompt);
        
        return $this->parseResponse($response);
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
        
        $ch = curl_init($this->apiUrl);
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
            'description' => $result['product_description'],
            'meta_title' => isset($result['meta_title']) ? $result['meta_title'] : '',
            'meta_description' => isset($result['meta_description']) ? $result['meta_description'] : '',
            'meta_keywords' => isset($result['meta_keywords']) ? $result['meta_keywords'] : '',
            'search_keywords' => isset($result['search_keywords']) ? $result['search_keywords'] : ''
        );
    }
}
}
