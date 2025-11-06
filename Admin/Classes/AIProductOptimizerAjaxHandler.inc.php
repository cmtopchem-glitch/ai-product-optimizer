<?php

if (!class_exists('AIProductOptimizerAjaxHandler')) {
    class AIProductOptimizerAjaxHandler extends AjaxHandler
{
    public function get_permission_status($p_rights_name = null)
    {
        return true;
    }

    public function proceed()
    {
        $action = $this->v_data_array['GET']['action'];
        
        switch($action) {
            case 'generate_description':
                return $this->generateDescription();
            case 'save_api_key':
                return $this->saveApiKey();
            default:
                return $this->v_output_buffer = json_encode(['error' => 'Unknown action']);
        }
    }
    
    private function generateDescription()
    {
        try {
            $productName = $this->v_data_array['POST']['product_name'] ?? '';
            $currentDescription = $this->v_data_array['POST']['current_description'] ?? '';
            
            if (empty($productName)) {
                throw new Exception('Produktname fehlt');
            }
            
            $api_key = gm_get_conf('AIPRODUCTOPTIMIZER_API_KEY');
            if (empty($api_key)) {
                throw new Exception('API Key nicht konfiguriert');
            }
            
            // OpenAI API Call
            $prompt = "Erstelle eine professionelle Produktbeschreibung für: $productName\n\n";
            if (!empty($currentDescription)) {
                $prompt .= "Aktuelle Beschreibung: $currentDescription\n\n";
            }
            $prompt .= "Die Beschreibung sollte SEO-optimiert sein und Kaufanreize schaffen.";
            
            $data = [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'Du bist ein E-Commerce Texter.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => 500,
                'temperature' => 0.7
            ];
            
            $ch = curl_init('https://api.openai.com/v1/chat/completions');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $api_key
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                throw new Exception('OpenAI API Error: ' . $httpCode);
            }
            
            $result = json_decode($response, true);
            $description = $result['choices'][0]['message']['content'] ?? '';
            
            $this->v_output_buffer = json_encode([
                'success' => true,
                'description' => trim($description)
            ]);
            
        } catch (Exception $e) {
            $this->v_output_buffer = json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        
        return $this->v_output_buffer;
    }
    
    private function saveApiKey()
    {
        try {
            $api_key = $this->v_data_array['POST']['api_key'] ?? '';
            
            if (empty($api_key)) {
                throw new Exception('API Key ist leer');
            }
            
            // In Datenbank speichern
            gm_set_conf('AIPRODUCTOPTIMIZER_API_KEY', $api_key);
            
            $this->v_output_buffer = json_encode([
                'success' => true,
                'message' => 'API Key gespeichert'
            ]);
            
        } catch (Exception $e) {
            $this->v_output_buffer = json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        
        return $this->v_output_buffer;
    }
}
}
