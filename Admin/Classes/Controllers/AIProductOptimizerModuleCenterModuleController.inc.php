<?php
if (!class_exists('AIProductOptimizerModuleCenterModuleController')) {
class AIProductOptimizerModuleCenterModuleController extends AbstractModuleCenterModuleController
{
    protected function _init()
    {
    }
    
    public function actionDefault()
    {
        $this->pageTitle = 'AI Product Optimizer - Konfiguration';

        // Lade gespeicherte Werte
        $apiKey = '';
        $projectId = '';
        $model = 'gpt-4o-mini';
        $availableModelsJson = '';
        $systemPrompt = '';
        $userPrompt = '';

        $query = "SELECT gm_key, gm_value FROM gm_configuration WHERE gm_key IN ('OPENAI_API_KEY', 'OPENAI_PROJECT_ID', 'OPENAI_MODEL', 'OPENAI_AVAILABLE_MODELS', 'OPENAI_SYSTEM_PROMPT', 'OPENAI_USER_PROMPT')";
        $result = xtc_db_query($query);
        while ($row = xtc_db_fetch_array($result)) {
            if ($row['gm_key'] == 'OPENAI_API_KEY') {
                $apiKey = $row['gm_value'];
            }
            if ($row['gm_key'] == 'OPENAI_PROJECT_ID') {
                $projectId = $row['gm_value'];
            }
            if ($row['gm_key'] == 'OPENAI_MODEL') {
                $model = $row['gm_value'];
            }
            if ($row['gm_key'] == 'OPENAI_AVAILABLE_MODELS') {
                $availableModelsJson = $row['gm_value'];
            }
            if ($row['gm_key'] == 'OPENAI_SYSTEM_PROMPT') {
                $systemPrompt = $row['gm_value'];
            }
            if ($row['gm_key'] == 'OPENAI_USER_PROMPT') {
                $userPrompt = $row['gm_value'];
            }
        }
        if (!empty($availableModelsJson)) {
            $availableModels = json_decode($availableModelsJson, true);
        }

        $success = $this->_getQueryParameter('success') == '1';
        $error = $this->_getQueryParameter('error') == '1';

        // Prüfe auf Backup-Verzeichnisse im Modul-Pfad
        $backupWarning = $this->_checkForBackupDirectories();

        // Template laden und Variablen zuweisen
        $coo_text_mgr = MainFactory::create_object('LanguageTextManager', array('ai_product_optimizer', $_SESSION['languages_id']));
        $smarty = new Smarty();
        $smarty->template_dir = DIR_FS_CATALOG . 'GXModules/REDOzone/AIProductOptimizer/Admin/Templates/';
        $smarty->compile_dir = DIR_FS_CATALOG . 'cache/smarty/';

        $smarty->assign('pageTitle', $this->pageTitle);
        $smarty->assign('apiKey', $apiKey);
        $smarty->assign('projectId', $projectId);
        $smarty->assign('model', $model);
        $smarty->assign('availableModels', $availableModels);
        $smarty->assign('systemPrompt', $systemPrompt);
        $smarty->assign('userPrompt', $userPrompt);
        $smarty->assign('success', $success);
        $smarty->assign('error', $error);
        $smarty->assign('backupWarning', $backupWarning);

        $html = $smarty->fetch('config_page.html');

        echo $html;
        exit;
    }

    /**
     * Prüft auf problematische Backup-Verzeichnisse im Modul-Pfad
     * @return array|null Warnung mit Details oder null wenn keine gefunden
     */
    private function _checkForBackupDirectories()
    {
        $modulePath = DIR_FS_CATALOG . 'GXModules/REDOzone/';
        $backupDirs = [];

        if (!is_dir($modulePath)) {
            return null;
        }

        // Scanne Verzeichnis nach Backup-Ordnern
        $dirs = scandir($modulePath);
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }

            $fullPath = $modulePath . $dir;
            if (!is_dir($fullPath)) {
                continue;
            }

            // Prüfe auf typische Backup-Muster
            if (preg_match('/AIProductOptimizer.*BACKUP/i', $dir) ||
                preg_match('/AIProductOptimizer_\d{8}/i', $dir) ||
                preg_match('/AIProductOptimizer.*\d{14}/i', $dir)) {
                $backupDirs[] = [
                    'name' => $dir,
                    'path' => $fullPath,
                    'size' => $this->_getDirectorySize($fullPath)
                ];
            }
        }

        if (empty($backupDirs)) {
            return null;
        }

        return [
            'count' => count($backupDirs),
            'directories' => $backupDirs,
            'message' => 'WARNUNG: Es wurden ' . count($backupDirs) . ' Backup-Verzeichnis(se) im Modul-Pfad gefunden. Diese können "duplicate class declaration" Fehler verursachen!'
        ];
    }

    /**
     * Berechnet die Größe eines Verzeichnisses
     * @param string $path Verzeichnispfad
     * @return string Formatierte Größe (z.B. "1.5 MB")
     */
    private function _getDirectorySize($path)
    {
        $size = 0;
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        // Formatiere Größe
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }
    
    public function actionSave()
    {
        $apiKey = $this->_getPostData('openai_key');
        $projectId = $this->_getPostData('project_id');
        $model = $this->_getPostData('model');

        if (empty($apiKey)) {
            header('Location: admin.php?do=AIProductOptimizerModuleCenterModule&error=1');
            exit;
        }

        $this->_saveConfig('OPENAI_API_KEY', $apiKey);
        $this->_saveConfig('OPENAI_PROJECT_ID', $projectId);
        $this->_saveConfig('OPENAI_MODEL', $model);
        $this->_saveConfig('OPENAI_SYSTEM_PROMPT', $this->_getPostData('system_prompt'));
        $this->_saveConfig('OPENAI_USER_PROMPT', $this->_getPostData('user_prompt'));

        header('Location: admin.php?do=AIProductOptimizerModuleCenterModule&success=1');
        exit;
    }
    
    public function actionUpdateModels()
    {
        ob_start();

        try {
            ob_clean();
            // Lade API Key und Project ID
            $query = "SELECT gm_key, gm_value FROM gm_configuration WHERE gm_key IN ('OPENAI_API_KEY', 'OPENAI_PROJECT_ID')";
            $result = xtc_db_query($query);
            $apiKey = '';
            $projectId = '';
            while ($row = xtc_db_fetch_array($result)) {
                if ($row['gm_key'] == 'OPENAI_API_KEY') {
                    $apiKey = $row['gm_value'];
                }
                if ($row['gm_key'] == 'OPENAI_PROJECT_ID') {
                    $projectId = $row['gm_value'];
                }
            }

            if (empty($apiKey)) {
                throw new Exception('Bitte erst API Key speichern');
            }

            // Baue API-URL mit optionalem Project-Parameter
            $apiUrl = 'https://api.openai.com/v1/models';
            if (!empty($projectId)) {
                $apiUrl .= '?project=' . urlencode($projectId);
            }

            // Rufe OpenAI Models API auf
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                throw new Exception('OpenAI API Fehler: HTTP ' . $httpCode);
            }

            $data = json_decode($response, true);

            if (!isset($data['data']) || !is_array($data['data'])) {
                throw new Exception('Ungültige API-Antwort');
            }

            // Filtere nur GPT-Modelle und sortiere
            $models = [];
            foreach ($data['data'] as $model) {
                $id = $model['id'];
                // Nur gpt-4 und gpt-3.5 Modelle
                if (strpos($id, 'gpt-4') === 0 || strpos($id, 'gpt-3.5') === 0) {
                    $models[] = [
                        'id' => $id,
                        'name' => $id,
                        'created' => $model['created']
                    ];
                }
            }

            // Sortiere nach Name
            usort($models, function($a, $b) {
                return strcmp($b['id'], $a['id']);
            });

            // Speichere in DB als JSON
            $modelsJson = json_encode($models);
            $this->_saveConfig('OPENAI_AVAILABLE_MODELS', $modelsJson);
            $this->_saveConfig('OPENAI_MODELS_UPDATED', date('Y-m-d H:i:s'));

            ob_clean();
            $this->_jsonResponse([
                'success' => true,
                'count' => count($models),
                'models' => $models
            ]);

        } catch (Exception $e) {
            ob_clean();
            $this->_jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    private function _saveConfig($key, $value)
    {
        $query = "SELECT gm_configuration_id FROM gm_configuration WHERE gm_key = '" . xtc_db_input($key) . "'";
        $result = xtc_db_query($query);
        
        if (xtc_db_num_rows($result) > 0) {
            $query = "UPDATE gm_configuration SET gm_value = '" . xtc_db_input($value) . "' WHERE gm_key = '" . xtc_db_input($key) . "'";
        } else {
            $query = "INSERT INTO gm_configuration (gm_key, gm_value) VALUES ('" . xtc_db_input($key) . "', '" . xtc_db_input($value) . "')";
        }
        
        xtc_db_query($query);
    }
    
    public function actionGenerate()
    {
        // Start output buffering to catch any stray output
        ob_start();

        try {
            // Clean any previous output
            ob_clean();

            require_once DIR_FS_CATALOG . 'GXModules/REDOzone/AIProductOptimizer/Services/BackupService.inc.php';
            require_once DIR_FS_CATALOG . 'GXModules/REDOzone/AIProductOptimizer/Services/OpenAIService.inc.php';

            $productId = $this->_getPostData('product_id');
            $productName = $this->_getPostData('product_name');
            $originalText = $this->_getPostData('original_text');
            $category = $this->_getPostData('category');
            $brand = $this->_getPostData('brand');
            $promptId = $this->_getPostData('prompt_id'); // Optional: Prompt aus Bibliothek

            // Erstelle Backup vor dem Überschreiben
            if (!empty($productId)) {
                BackupService::createBackup($productId);
            }

            if (empty($productName) || empty($originalText)) {
                throw new Exception('Produktname und Text sind erforderlich');
            }

            // Lade API Key, Project ID und Model
            $query = "SELECT gm_key, gm_value FROM gm_configuration WHERE gm_key IN ('OPENAI_API_KEY', 'OPENAI_PROJECT_ID', 'OPENAI_MODEL')";
            $result = xtc_db_query($query);
            $apiKey = '';
            $projectId = '';
            $model = 'gpt-4o-mini';
            while ($row = xtc_db_fetch_array($result)) {
                if ($row['gm_key'] == 'OPENAI_API_KEY') {
                    $apiKey = $row['gm_value'];
                }
                if ($row['gm_key'] == 'OPENAI_PROJECT_ID') {
                    $projectId = $row['gm_value'];
                }
                if ($row['gm_key'] == 'OPENAI_MODEL') {
                    $model = $row['gm_value'];
                }
            }

            if (empty($apiKey)) {
                throw new Exception('OpenAI API Key nicht konfiguriert');
            }

            $service = new OpenAIService($apiKey, $model, $projectId);

            // Wenn eine Prompt-ID übergeben wurde, verwende diesen Prompt
            if (!empty($promptId)) {
                $service->usePromptFromLibrary($promptId);
            }

            $languages = $this->_getActiveLanguages();
            $results = array();

            foreach ($languages as $lang) {
                $results[$lang] = $service->generateSEOContent(
                    $productName,
                    $originalText,
                    $lang,
                    array('category' => $category, 'brand' => $brand)
                );
            }

            // Clean output buffer before sending JSON
            ob_clean();
            $this->_jsonResponse(array('success' => true, 'data' => $results));
        } catch (Exception $e) {
            // Clean output buffer before sending error JSON
            ob_clean();
            $this->_jsonResponse(array('success' => false, 'error' => $e->getMessage()));
        }
    }
    
    private function _jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    private function _getActiveLanguages()
    {
        $languages = [];
        $query = "SELECT code FROM languages WHERE status = 1 ORDER BY languages_id";
        $result = xtc_db_query($query);
        
        while ($row = xtc_db_fetch_array($result)) {
            $languages[] = $row['code'];
        }
        
        return $languages;
    }
    
    private function _getLanguageMapping()
    {
        $mapping = [];
        $query = "SELECT languages_id, code FROM languages WHERE status = 1 ORDER BY languages_id";
        $result = xtc_db_query($query);
        
        while ($row = xtc_db_fetch_array($result)) {
            $mapping[$row['code']] = (int)$row['languages_id'];
        }
        
        return $mapping;
    }
    
    public function actionRestore()
    {
        ob_start();

        try {
            ob_clean();
            require_once DIR_FS_CATALOG . 'GXModules/REDOzone/AIProductOptimizer/Services/BackupService.inc.php';

            $productId = $this->_getPostData('product_id');

            if (empty($productId)) {
                throw new Exception('Produkt-ID fehlt');
            }

            if (!BackupService::hasBackup($productId)) {
                throw new Exception('Kein Backup vorhanden');
            }

            $restored = BackupService::restoreBackup($productId);

            ob_clean();
            $this->_jsonResponse([
                'success' => true,
                'message' => $restored . ' Sprache(n) wiederhergestellt'
            ]);

        } catch (Exception $e) {
            ob_clean();
            $this->_jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function actionCheckBackup()
    {
        ob_start();

        try {
            ob_clean();
            require_once DIR_FS_CATALOG . 'GXModules/REDOzone/AIProductOptimizer/Services/BackupService.inc.php';

            $productId = $this->_getQueryParameter('product_id');

            if (empty($productId)) {
                throw new Exception('Produkt-ID fehlt');
            }

            $hasBackup = BackupService::hasBackup($productId);

            ob_clean();
            $this->_jsonResponse([
                'success' => true,
                'hasBackup' => $hasBackup,
                'product_id' => $productId
            ]);

        } catch (Exception $e) {
            ob_clean();
            $this->_jsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
                'hasBackup' => false
            ]);
        }
    }

    public function actionGetBackups()
    {
        ob_start();

        try {
            ob_clean();
            require_once DIR_FS_CATALOG . 'GXModules/REDOzone/AIProductOptimizer/Services/BackupService.inc.php';

            $productId = $this->_getQueryParameter('product_id');

            if (empty($productId)) {
                throw new Exception('Produkt-ID fehlt');
            }

            $backups = BackupService::getAllBackups($productId);

            ob_clean();
            $this->_jsonResponse([
                'success' => true,
                'backups' => $backups
            ]);

        } catch (Exception $e) {
            ob_clean();
            $this->_jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function actionDeleteBackup()
    {
        ob_start();

        try {
            ob_clean();
            require_once DIR_FS_CATALOG . 'GXModules/REDOzone/AIProductOptimizer/Services/BackupService.inc.php';

            $backupId = $this->_getPostData('backup_id');
            $productId = $this->_getPostData('product_id');

            if (empty($backupId) || empty($productId)) {
                throw new Exception('Backup-ID und Produkt-ID sind erforderlich');
            }

            $deleted = BackupService::deleteBackup($backupId, $productId);

            if ($deleted > 0) {
                ob_clean();
                $this->_jsonResponse([
                    'success' => true,
                    'message' => 'Backup erfolgreich gelöscht (' . $deleted . ' Einträge)'
                ]);
            } else {
                throw new Exception('Backup konnte nicht gelöscht werden');
            }

        } catch (Exception $e) {
            ob_clean();
            $this->_jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function actionRestoreSpecificBackup()
    {
        ob_start();

        try {
            ob_clean();
            require_once DIR_FS_CATALOG . 'GXModules/REDOzone/AIProductOptimizer/Services/BackupService.inc.php';

            $backupId = $this->_getPostData('backup_id');
            $productId = $this->_getPostData('product_id');

            if (empty($backupId) || empty($productId)) {
                throw new Exception('Backup-ID und Produkt-ID sind erforderlich');
            }

            $restored = BackupService::restoreSpecificBackup($backupId, $productId);

            if ($restored > 0) {
                ob_clean();
                $this->_jsonResponse([
                    'success' => true,
                    'message' => $restored . ' Sprache(n) wiederhergestellt'
                ]);
            } else {
                throw new Exception('Backup konnte nicht wiederhergestellt werden');
            }

        } catch (Exception $e) {
            ob_clean();
            $this->_jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    // ============================================================
    // Prompt Library Actions
    // ============================================================

    /**
     * Gibt alle Prompts aus der Bibliothek zurück
     */
    public function actionGetPrompts()
    {
        ob_start();

        try {
            ob_clean();
            require_once DIR_FS_CATALOG . 'GXModules/REDOzone/AIProductOptimizer/Services/PromptLibraryService.inc.php';

            $activeOnly = $this->_getQueryParameter('active_only') !== '0';
            $prompts = PromptLibraryService::getAllPrompts($activeOnly);

            ob_clean();
            $this->_jsonResponse([
                'success' => true,
                'prompts' => $prompts
            ]);

        } catch (Exception $e) {
            ob_clean();
            $this->_jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Gibt einen spezifischen Prompt zurück
     */
    public function actionGetPrompt()
    {
        ob_start();

        try {
            ob_clean();
            require_once DIR_FS_CATALOG . 'GXModules/REDOzone/AIProductOptimizer/Services/PromptLibraryService.inc.php';

            $promptId = $this->_getQueryParameter('prompt_id');

            if (empty($promptId)) {
                throw new Exception('Prompt-ID fehlt');
            }

            $prompt = PromptLibraryService::getPromptById($promptId);

            if (!$prompt) {
                throw new Exception('Prompt nicht gefunden');
            }

            ob_clean();
            $this->_jsonResponse([
                'success' => true,
                'prompt' => $prompt
            ]);

        } catch (Exception $e) {
            ob_clean();
            $this->_jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Erstellt einen neuen Prompt
     */
    public function actionCreatePrompt()
    {
        ob_start();

        try {
            ob_clean();
            require_once DIR_FS_CATALOG . 'GXModules/REDOzone/AIProductOptimizer/Services/PromptLibraryService.inc.php';

            $label = $this->_getPostData('label');
            $systemPrompt = $this->_getPostData('system_prompt');
            $userPrompt = $this->_getPostData('user_prompt');
            $description = $this->_getPostData('description');
            $isDefault = $this->_getPostData('is_default') == '1';

            if (empty($label) || empty($systemPrompt) || empty($userPrompt)) {
                throw new Exception('Label, System Prompt und User Prompt sind erforderlich');
            }

            $promptId = PromptLibraryService::createPrompt($label, $systemPrompt, $userPrompt, $description, $isDefault);

            ob_clean();
            $this->_jsonResponse([
                'success' => true,
                'prompt_id' => $promptId,
                'message' => 'Prompt erfolgreich erstellt'
            ]);

        } catch (Exception $e) {
            ob_clean();
            $this->_jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Aktualisiert einen existierenden Prompt
     */
    public function actionUpdatePrompt()
    {
        ob_start();

        try {
            ob_clean();
            require_once DIR_FS_CATALOG . 'GXModules/REDOzone/AIProductOptimizer/Services/PromptLibraryService.inc.php';

            $promptId = $this->_getPostData('prompt_id');
            $label = $this->_getPostData('label');
            $systemPrompt = $this->_getPostData('system_prompt');
            $userPrompt = $this->_getPostData('user_prompt');
            $description = $this->_getPostData('description');
            $isDefault = $this->_getPostData('is_default') == '1';
            $isActive = $this->_getPostData('is_active') !== '0';

            if (empty($promptId) || empty($label) || empty($systemPrompt) || empty($userPrompt)) {
                throw new Exception('Prompt-ID, Label, System Prompt und User Prompt sind erforderlich');
            }

            $success = PromptLibraryService::updatePrompt($promptId, $label, $systemPrompt, $userPrompt, $description, $isDefault, $isActive);

            if (!$success) {
                throw new Exception('Prompt konnte nicht aktualisiert werden');
            }

            ob_clean();
            $this->_jsonResponse([
                'success' => true,
                'message' => 'Prompt erfolgreich aktualisiert'
            ]);

        } catch (Exception $e) {
            ob_clean();
            $this->_jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Löscht einen Prompt
     */
    public function actionDeletePrompt()
    {
        ob_start();

        try {
            ob_clean();
            require_once DIR_FS_CATALOG . 'GXModules/REDOzone/AIProductOptimizer/Services/PromptLibraryService.inc.php';

            $promptId = $this->_getPostData('prompt_id');

            if (empty($promptId)) {
                throw new Exception('Prompt-ID fehlt');
            }

            $success = PromptLibraryService::deletePrompt($promptId);

            if (!$success) {
                throw new Exception('Prompt konnte nicht gelöscht werden');
            }

            ob_clean();
            $this->_jsonResponse([
                'success' => true,
                'message' => 'Prompt erfolgreich gelöscht'
            ]);

        } catch (Exception $e) {
            ob_clean();
            $this->_jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Setzt einen Prompt als Standard
     */
    public function actionSetDefaultPrompt()
    {
        ob_start();

        try {
            ob_clean();
            require_once DIR_FS_CATALOG . 'GXModules/REDOzone/AIProductOptimizer/Services/PromptLibraryService.inc.php';

            $promptId = $this->_getPostData('prompt_id');

            if (empty($promptId)) {
                throw new Exception('Prompt-ID fehlt');
            }

            $success = PromptLibraryService::setAsDefault($promptId);

            if (!$success) {
                throw new Exception('Standard-Prompt konnte nicht gesetzt werden');
            }

            ob_clean();
            $this->_jsonResponse([
                'success' => true,
                'message' => 'Standard-Prompt erfolgreich gesetzt'
            ]);

        } catch (Exception $e) {
            ob_clean();
            $this->_jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
}
