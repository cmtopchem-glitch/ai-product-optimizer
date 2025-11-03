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
        
        // Konfigurationseinträge erstellen
        $this->_createConfigEntry('MODULE_AI_OPTIMIZER_OPENAI_API_KEY', '', 6, 1);
        $this->_createConfigEntry('MODULE_AI_OPTIMIZER_MODEL', 'gpt-4o', 6, 2);
    }
    
    /**
     * Erstellt die Backup-Tabelle
     */
    private function _createBackupTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `rz_ai_optimizer_backup` (
          `backup_id` int(11) NOT NULL AUTO_INCREMENT,
          `products_id` int(11) NOT NULL,
          `languages_id` int(11) NOT NULL,
          `products_description` text DEFAULT NULL,
          `products_meta_title` varchar(255) DEFAULT NULL,
          `products_meta_description` text DEFAULT NULL,
          `products_meta_keywords` text DEFAULT NULL,
          `products_keywords` text DEFAULT NULL,
          `backup_date` datetime NOT NULL,
          `restored` tinyint(1) DEFAULT 0,
          PRIMARY KEY (`backup_id`),
          KEY `idx_products_id` (`products_id`),
          KEY `idx_backup_date` (`backup_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        xtc_db_query($sql);
    }
    
    /**
     * Deinstallation des Moduls
     */
    public function uninstall()
    {
        // Backup-Tabelle löschen
        xtc_db_query("DROP TABLE IF EXISTS `rz_ai_optimizer_backup`");
        
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
