<?php
/* --------------------------------------------------------------
   AIProductOptimizerModuleCenterModule.inc.php 2024-11-01
   REDOzone
   http://www.redozone.com
   Copyright (c) 2024 REDOzone
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/
/**
 * AI Product Optimizer Module für Gambio GX 4.8
 * Generiert SEO-optimierte Produkttexte mit OpenAI API
 */
if (!class_exists('AIProductOptimizerModuleCenterModule')) {
class AIProductOptimizerModuleCenterModule extends AbstractModuleCenterModule
{
    /**
     * Modul-Initialisierung
     */
    protected function _init()
    {
        $this->title = 'AI Product Optimizer';
        $this->description = 'Generiert SEO-optimierte Produkttexte, Meta-Titel und Meta-Descriptions in mehreren Sprachen mit OpenAI API';
        $this->sortOrder = 1000;
    }
    
    /**
     * Installation des Moduls
     */
    public function install()
    {
        parent::install();

        // Backup-Tabelle erstellen
        $this->_createBackupTable();

        // Prompt-Library-Tabelle erstellen
        $this->_createPromptLibraryTable();

        // Konfigurationseinträge erstellen
        $this->_createConfigEntry('MODULE_AI_OPTIMIZER_OPENAI_API_KEY', '', 6, 1);
        $this->_createConfigEntry('MODULE_AI_OPTIMIZER_MODEL', 'gpt-4o', 6, 2);
        $this->_createConfigEntry('MODULE_AI_OPTIMIZER_PROJECT_ID', '', 6, 3);
    }
    
    /**
     * Erstellt die Backup-Tabelle
     */
    private function _createBackupTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `rz_ai_optimizer_backup` (
          `backup_id` int(11) NOT NULL AUTO_INCREMENT,
          `products_id` int(11) NOT NULL COMMENT 'Produkt-ID aus products Tabelle',
          `languages_id` int(11) NOT NULL COMMENT 'Sprach-ID aus languages Tabelle',
          `products_description` text DEFAULT NULL COMMENT 'Gesicherte Produktbeschreibung',
          `products_meta_title` varchar(255) DEFAULT NULL COMMENT 'Gesicherter Meta-Titel',
          `products_meta_description` text DEFAULT NULL COMMENT 'Gesicherte Meta-Description',
          `products_meta_keywords` text DEFAULT NULL COMMENT 'Gesicherte Meta-Keywords',
          `products_keywords` text DEFAULT NULL COMMENT 'Gesicherte Shop-Suchworte',
          `backup_date` datetime NOT NULL COMMENT 'Zeitpunkt der Backup-Erstellung',
          `restored` tinyint(1) DEFAULT 0 COMMENT 'Flag ob Backup bereits wiederhergestellt wurde',
          PRIMARY KEY (`backup_id`),
          KEY `idx_products_id` (`products_id`),
          KEY `idx_backup_date` (`backup_date`),
          KEY `idx_restored` (`restored`),
          KEY `idx_product_restored` (`products_id`, `restored`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Backup-Tabelle für AI Product Optimizer'";

        xtc_db_query($sql);
    }

    /**
     * Erstellt die Prompt-Library-Tabelle
     */
    private function _createPromptLibraryTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `rz_ai_prompt_library` (
          `prompt_id` int(11) NOT NULL AUTO_INCREMENT,
          `prompt_label` varchar(255) NOT NULL COMMENT 'Benutzerdefiniertes Label/Name für den Prompt',
          `prompt_description` text DEFAULT NULL COMMENT 'Optionale Beschreibung des Prompts',
          `system_prompt` text NOT NULL COMMENT 'System Prompt für OpenAI',
          `user_prompt` longtext NOT NULL COMMENT 'User Prompt Template mit Platzhaltern',
          `is_default` tinyint(1) DEFAULT 0 COMMENT 'Flag ob dies der Standard-Prompt ist',
          `is_active` tinyint(1) DEFAULT 1 COMMENT 'Flag ob der Prompt aktiv/sichtbar ist',
          `created_at` datetime NOT NULL COMMENT 'Erstellungsdatum',
          `updated_at` datetime DEFAULT NULL COMMENT 'Letztes Änderungsdatum',
          `usage_count` int(11) DEFAULT 0 COMMENT 'Anzahl der Verwendungen',
          `last_used_at` datetime DEFAULT NULL COMMENT 'Letzter Verwendungszeitpunkt',
          PRIMARY KEY (`prompt_id`),
          KEY `idx_is_active` (`is_active`),
          KEY `idx_is_default` (`is_default`),
          KEY `idx_usage_count` (`usage_count`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Prompt-Bibliothek für AI Product Optimizer'";

        xtc_db_query($sql);

        // Standard-Prompts einfügen, falls die Tabelle leer ist
        $this->_insertDefaultPrompts();
    }

    /**
     * Fügt die Standard-Prompts ein, falls noch keine vorhanden sind
     */
    private function _insertDefaultPrompts()
    {
        // Prüfen ob bereits Prompts vorhanden sind
        $result = xtc_db_query("SELECT COUNT(*) as count FROM `rz_ai_prompt_library`");
        $row = xtc_db_fetch_array($result);

        if ($row['count'] > 0) {
            return; // Bereits Prompts vorhanden
        }

        // Standard-Prompts aus default_prompts.sql laden und ausführen
        $sqlFile = dirname(dirname(dirname(__FILE__))) . '/default_prompts.sql';

        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);

            // SQL in einzelne Statements aufteilen und ausführen
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                function($statement) {
                    return !empty($statement) &&
                           strpos($statement, '--') !== 0 &&
                           !preg_match('/^--/', $statement);
                }
            );

            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    xtc_db_query($statement);
                }
            }
        }
    }
    
    /**
     * Deinstallation des Moduls
     */
    public function uninstall()
    {
        // Backup-Tabelle löschen
        xtc_db_query("DROP TABLE IF EXISTS `rz_ai_optimizer_backup`");

        // Prompt-Library-Tabelle löschen
        xtc_db_query("DROP TABLE IF EXISTS `rz_ai_prompt_library`");

        // Konfigurationswerte entfernen
        $db = StaticGXCoreLoader::getDatabaseQueryBuilder();
        $db->where('configuration_key', 'LIKE', 'MODULE_AI_OPTIMIZER_%')
           ->delete('configuration');

        parent::uninstall();
    }
    
    /**
     * Erstellt einen Konfigurationseintrag
     */
    private function _createConfigEntry($key, $defaultValue, $groupId, $sortOrder)
    {
        $db = StaticGXCoreLoader::getDatabaseQueryBuilder();
        
        // Lösche existierende Einträge erst
        $db->where('configuration_key', $key)->delete('configuration');
        
        // Dann neu einfügen
        $db->insert('configuration', array(
            'configuration_key' => $key,
            'configuration_value' => $defaultValue,
            'configuration_group_id' => $groupId,
            'sort_order' => $sortOrder,
            'date_added' => date('Y-m-d H:i:s')
        ));
    }
}
}
