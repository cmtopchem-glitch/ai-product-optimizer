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

    /**
     * Constructor
     * @param string $apiKey OpenAI API Key
     * @param string $projectId Optional Project ID
     */
    public function __construct($apiKey, $projectId = '')
    {
        $this->apiKey = $apiKey;
        $this->projectId = $projectId;
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
        // Erstelle Prompt für Bildanalyse
        $systemPrompt = 'Du bist ein Experte für barrierefreie Bildbeschreibungen. Du erstellst präzise, informative ALT-Texte für Produktbilder, die für Screenreader-Nutzer optimal geeignet sind.';

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

        $userPrompt = "Analysiere dieses Produktbild und erstelle barrierefreie ALT-Texte.\n\n";
        $userPrompt .= "Produkt: " . $productName . "\n\n";
        $userPrompt .= "WICHTIG für barrierefreie ALT-Texte:\n";
        $userPrompt .= "- Beschreibe WAS auf dem Bild zu sehen ist (nicht WIE es aussieht)\n";
        $userPrompt .= "- Fokussiere auf das Produkt und seine erkennbaren Eigenschaften\n";
        $userPrompt .= "- Halte die Beschreibung präzise aber informativ (ca. 50-100 Zeichen)\n";
        $userPrompt .= "- Vermeide subjektive Bewertungen wie 'schön' oder 'hochwertig'\n";
        $userPrompt .= "- Beschreibe erkennbare Details: Farbe, Form, Material, Anzahl\n\n";

        $userPrompt .= "Erstelle ALT-Texte für folgende Sprachen:\n";
        foreach ($languages as $lang) {
            $langName = isset($languageNames[$lang]) ? $languageNames[$lang] : $lang;
            $userPrompt .= "- " . strtoupper($lang) . " (" . $langName . ")\n";
        }

        $userPrompt .= "\nAntworte NUR mit einem JSON-Objekt in diesem Format:\n";
        $userPrompt .= "{\n";
        foreach ($languages as $lang) {
            $userPrompt .= '  "' . $lang . '": "ALT-Text in dieser Sprache",'."\n";
        }
        $userPrompt = rtrim($userPrompt, ",\n") . "\n}\n";

        // API Call mit Vision
        $data = [
            'model' => 'gpt-4o', // GPT-4o unterstützt Vision
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt
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
