<?php
/* --------------------------------------------------------------
   VisionService.inc.php 2025-11-08
   REDOzone
   http://www.redozone.com
   Copyright (c) 2025 REDOzone
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

if (!class_exists('VisionService')) {
class VisionService
{
    private $apiKey;
    private $projectId;
    private $model;
    private $systemPrompt;
    private $userPromptTemplate;

    /**
     * Constructor
     * @param string $apiKey OpenAI API Key
     * @param string $projectId Optional Project ID
     * @param string $model Vision Model (gpt-4o oder gpt-4o-mini)
     * @param string $systemPrompt System-Prompt für Bildanalyse
     * @param string $userPromptTemplate User-Prompt Template mit Platzhaltern
     */
    public function __construct($apiKey, $projectId = '', $model = 'gpt-4o', $systemPrompt = '', $userPromptTemplate = '')
    {
        $this->apiKey = $apiKey;
        $this->projectId = $projectId;
        $this->model = !empty($model) ? $model : 'gpt-4o';

        // Standardwerte setzen, falls leer
        $this->systemPrompt = !empty($systemPrompt)
            ? $systemPrompt
            : 'Du bist ein Experte für barrierefreie Bildbeschreibungen. Du erstellst präzise, informative ALT-Texte für Produktbilder, die für Screenreader-Nutzer optimal geeignet sind.';

        $this->userPromptTemplate = !empty($userPromptTemplate)
            ? $userPromptTemplate
            : $this->getDefaultUserPrompt();
    }

    /**
     * Gibt den Standard User-Prompt zurück
     * @return string
     */
    private function getDefaultUserPrompt()
    {
        return 'Analysiere dieses Produktbild und erstelle barrierefreie ALT-Texte.

Produkt: {PRODUCT_NAME}

WICHTIG für barrierefreie ALT-Texte:
- Beschreibe WAS auf dem Bild zu sehen ist (nicht WIE es aussieht)
- Fokussiere auf das Produkt und seine erkennbaren Eigenschaften
- Halte die Beschreibung präzise aber informativ (ca. 50-100 Zeichen)
- Vermeide subjektive Bewertungen wie "schön" oder "hochwertig"
- Beschreibe erkennbare Details: Farbe, Form, Material, Anzahl

Erstelle ALT-Texte für folgende Sprachen:
{LANGUAGES}

Antworte NUR mit einem JSON-Objekt in diesem Format:
{
  "de": "ALT-Text in Deutsch",
  "en": "ALT-text in English",
  ...
}';
    }

    /**
     * Generiert barrierefreie ALT-Texte für ein Produktbild in mehreren Sprachen
     *
     * @param string $imageUrl URL zum Bild (muss öffentlich erreichbar sein)
     * @param string $productName Name des Produkts für Kontext
     * @param array $languages Array von Sprachcodes ['de', 'en', 'fr', etc.]
     * @return array Array mit generierten ALT-Texten pro Sprache
     * @throws Exception bei Fehlern
     */
    public function generateAltTexts($imageUrl, $productName, $languages)
    {
        // Erstelle Sprachliste für den Prompt
        $languageNames = [
            'de' => 'Deutsch',
            'en' => 'English',
            'fr' => 'Français',
            'es' => 'Español',
            'it' => 'Italiano',
            'nl' => 'Nederlands',
            'pl' => 'Polski',
            'am' => 'Deutsch' // Fallback für unbekannte Codes
        ];

        $languageList = '';
        foreach ($languages as $lang) {
            $langName = isset($languageNames[$lang]) ? $languageNames[$lang] : $lang;
            $languageList .= "- " . strtoupper($lang) . " (" . $langName . ")\n";
        }

        // Ersetze Platzhalter im User-Prompt-Template
        $userPrompt = $this->userPromptTemplate;
        $userPrompt = str_replace('{PRODUCT_NAME}', $productName, $userPrompt);
        $userPrompt = str_replace('{LANGUAGES}', trim($languageList), $userPrompt);

        // API Call mit Vision
        $data = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->systemPrompt
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $userPrompt
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => $imageUrl,
                                'detail' => 'high' // Hohe Detailstufe für bessere Analyse
                            ]
                        ]
                    ]
                ]
            ],
            'max_tokens' => 500,
            'temperature' => 0.3 // Niedrige Temperatur für konsistente Beschreibungen
        ];

        // Baue API-URL
        $apiUrl = 'https://api.openai.com/v1/chat/completions';
        if (!empty($this->projectId)) {
            $apiUrl .= '?project=' . urlencode($this->projectId);
        }

        // cURL Request
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Vision API kann länger dauern

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new Exception('cURL Error: ' . $curlError);
        }

        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMsg = isset($errorData['error']['message']) ? $errorData['error']['message'] : 'HTTP ' . $httpCode;
            throw new Exception('OpenAI API Error: ' . $errorMsg);
        }

        $result = json_decode($response, true);

        if (!isset($result['choices'][0]['message']['content'])) {
            throw new Exception('Ungültige API-Antwort: Kein Content gefunden');
        }

        $content = $result['choices'][0]['message']['content'];

        // Extrahiere JSON aus der Antwort (falls zusätzlicher Text vorhanden)
        if (preg_match('/\{[^}]+\}/', $content, $matches)) {
            $content = $matches[0];
        }

        $altTexts = json_decode($content, true);

        if (!is_array($altTexts)) {
            throw new Exception('Konnte JSON nicht parsen: ' . substr($content, 0, 200));
        }

        // Validiere dass alle Sprachen vorhanden sind
        foreach ($languages as $lang) {
            if (!isset($altTexts[$lang]) || empty($altTexts[$lang])) {
                throw new Exception('Fehlender ALT-Text für Sprache: ' . $lang);
            }
        }

        return $altTexts;
    }

    /**
     * Konvertiert relativen Bildpfad in absolute URL
     *
     * @param string $imagePath Relativer Pfad (z.B. /images/product_images/...)
     * @param string $shopUrl Basis-URL des Shops
     * @return string Absolute URL
     */
    public static function getAbsoluteImageUrl($imagePath, $shopUrl)
    {
        // Entferne führenden Slash wenn vorhanden
        $imagePath = ltrim($imagePath, '/');

        // Entferne trailing slash von Shop-URL
        $shopUrl = rtrim($shopUrl, '/');

        return $shopUrl . '/' . $imagePath;
    }
}
}
