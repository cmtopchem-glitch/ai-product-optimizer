<?php
/* --------------------------------------------------------------
   BackupService.inc.php 2025-11-02
   REDOzone
   http://www.redozone.com
   Copyright (c) 2025 REDOzone
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

if (!class_exists('BackupService')) {
class BackupService
{
    /**
     * Stellt sicher, dass die Backup-Tabelle existiert
     * Erstellt die Tabelle bei Bedarf automatisch
     */
    private static function ensureTableExists()
    {
        // Prüfe ob Tabelle existiert
        $checkQuery = "SHOW TABLES LIKE 'rz_ai_optimizer_backup'";
        $result = xtc_db_query($checkQuery);

        if (xtc_db_num_rows($result) > 0) {
            return; // Tabelle existiert bereits
        }

        // Tabelle existiert nicht - erstelle sie
        $createTableQuery = "CREATE TABLE IF NOT EXISTS `rz_ai_optimizer_backup` (
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

        xtc_db_query($createTableQuery);
    }

    /**
     * Erstellt ein Backup der aktuellen Produkttexte
     */
    public static function createBackup($productId)
    {
        // Stelle sicher, dass die Tabelle existiert
        self::ensureTableExists();

        // Hole alle aktiven Sprachen
        $query = "SELECT languages_id FROM languages WHERE status = 1";
        $result = xtc_db_query($query);
        
        $backedUp = 0;
        
        while ($row = xtc_db_fetch_array($result)) {
            $langId = $row['languages_id'];
            
            // Hole aktuelle Produktdaten
            $query = "SELECT products_description, products_meta_title, products_meta_description, 
                      products_meta_keywords, products_keywords
                      FROM products_description 
                      WHERE products_id = '" . (int)$productId . "' 
                      AND language_id = '" . (int)$langId . "'";
            $prodResult = xtc_db_query($query);
            
            if ($prodRow = xtc_db_fetch_array($prodResult)) {
                // Speichere Backup
                $insertQuery = "INSERT INTO rz_ai_optimizer_backup 
                    (products_id, languages_id, products_description, products_meta_title, 
                     products_meta_description, products_meta_keywords, products_keywords, 
                     backup_date, restored) 
                    VALUES (
                        '" . (int)$productId . "',
                        '" . (int)$langId . "',
                        '" . xtc_db_input($prodRow['products_description']) . "',
                        '" . xtc_db_input($prodRow['products_meta_title']) . "',
                        '" . xtc_db_input($prodRow['products_meta_description']) . "',
                        '" . xtc_db_input($prodRow['products_meta_keywords']) . "',
                        '" . xtc_db_input($prodRow['products_keywords']) . "',
                        NOW(),
                        0
                    )";
                
                xtc_db_query($insertQuery);
                $backedUp++;
            }
        }
        
        return $backedUp;
    }
    
    /**
     * Stellt das letzte Backup wieder her
     */
    public static function restoreBackup($productId)
    {
        // Stelle sicher, dass die Tabelle existiert
        self::ensureTableExists();

        // Hole das neueste Backup für dieses Produkt
        $query = "SELECT backup_id, languages_id, products_description, products_meta_title, 
                  products_meta_description, products_meta_keywords, products_keywords
                  FROM rz_ai_optimizer_backup 
                  WHERE products_id = '" . (int)$productId . "' 
                  AND restored = 0
                  ORDER BY backup_date DESC";
        $result = xtc_db_query($query);
        
        $restored = 0;
        $backupIds = [];
        
        while ($row = xtc_db_fetch_array($result)) {
            $langId = $row['languages_id'];
            
            // Stelle Daten wieder her
            $updateQuery = "UPDATE products_description SET 
                products_description = '" . xtc_db_input($row['products_description']) . "',
                products_meta_title = '" . xtc_db_input($row['products_meta_title']) . "',
                products_meta_description = '" . xtc_db_input($row['products_meta_description']) . "',
                products_meta_keywords = '" . xtc_db_input($row['products_meta_keywords']) . "',
                products_keywords = '" . xtc_db_input($row['products_keywords']) . "'
                WHERE products_id = '" . (int)$productId . "' 
                AND language_id = '" . (int)$langId . "'";
            
            xtc_db_query($updateQuery);
            $restored++;
            $backupIds[] = $row['backup_id'];
        }
        
        // Markiere Backups als wiederhergestellt
        if (!empty($backupIds)) {
            $updateQuery = "UPDATE rz_ai_optimizer_backup 
                SET restored = 1 
                WHERE backup_id IN (" . implode(',', $backupIds) . ")";
            xtc_db_query($updateQuery);
        }
        
        return $restored;
    }
    
    /**
     * Prüft ob ein Backup existiert
     */
    public static function hasBackup($productId)
    {
        // Stelle sicher, dass die Tabelle existiert
        self::ensureTableExists();

        $query = "SELECT COUNT(*) as count
                  FROM rz_ai_optimizer_backup
                  WHERE products_id = '" . (int)$productId . "'
                  AND restored = 0";
        $result = xtc_db_query($query);
        $row = xtc_db_fetch_array($result);

        return $row['count'] > 0;
    }
    
    /**
     * Löscht alte Backups (älter als 30 Tage)
     */
    public static function cleanOldBackups()
    {
        $query = "DELETE FROM rz_ai_optimizer_backup
                  WHERE backup_date < DATE_SUB(NOW(), INTERVAL 30 DAY)";
        xtc_db_query($query);
    }

    /**
     * Holt alle Backups für ein Produkt gruppiert nach Backup-Datum
     * @return array Array von Backups mit backup_date, backup_id (vom ersten Eintrag), restored, language_count
     */
    public static function getAllBackups($productId)
    {
        // Stelle sicher, dass die Tabelle existiert
        self::ensureTableExists();

        $query = "SELECT
                    MIN(backup_id) as backup_id,
                    backup_date,
                    restored,
                    COUNT(*) as language_count
                  FROM rz_ai_optimizer_backup
                  WHERE products_id = '" . (int)$productId . "'
                  GROUP BY DATE_FORMAT(backup_date, '%Y-%m-%d %H:%i:%s'), restored
                  ORDER BY backup_date DESC";
        $result = xtc_db_query($query);

        $backups = [];
        while ($row = xtc_db_fetch_array($result)) {
            $backups[] = [
                'backup_id' => $row['backup_id'],
                'backup_date' => $row['backup_date'],
                'restored' => $row['restored'],
                'language_count' => $row['language_count']
            ];
        }

        return $backups;
    }

    /**
     * Löscht ein spezifisches Backup (alle Sprachen mit gleichem Datum)
     * @param int $backupId Die ID eines Backups aus der Gruppe
     * @param int $productId Die Produkt-ID zur Sicherheit
     * @return int Anzahl gelöschter Einträge
     */
    public static function deleteBackup($backupId, $productId)
    {
        // Hole das Backup-Datum des angegebenen Backups
        $query = "SELECT backup_date FROM rz_ai_optimizer_backup
                  WHERE backup_id = '" . (int)$backupId . "'
                  AND products_id = '" . (int)$productId . "'";
        $result = xtc_db_query($query);

        if ($row = xtc_db_fetch_array($result)) {
            $backupDate = $row['backup_date'];

            // Lösche alle Einträge mit diesem Datum und dieser Produkt-ID
            $deleteQuery = "DELETE FROM rz_ai_optimizer_backup
                           WHERE products_id = '" . (int)$productId . "'
                           AND backup_date = '" . xtc_db_input($backupDate) . "'";
            xtc_db_query($deleteQuery);

            return xtc_db_affected_rows();
        }

        return 0;
    }

    /**
     * Stellt ein spezifisches Backup wieder her
     * @param int $backupId Die ID eines Backups aus der Gruppe
     * @param int $productId Die Produkt-ID zur Sicherheit
     * @return int Anzahl wiederhergestellter Sprachen
     */
    public static function restoreSpecificBackup($backupId, $productId)
    {
        // Hole das Backup-Datum des angegebenen Backups
        $query = "SELECT backup_date FROM rz_ai_optimizer_backup
                  WHERE backup_id = '" . (int)$backupId . "'
                  AND products_id = '" . (int)$productId . "'";
        $result = xtc_db_query($query);

        if (!($dateRow = xtc_db_fetch_array($result))) {
            return 0;
        }

        $backupDate = $dateRow['backup_date'];

        // Hole alle Einträge mit diesem Datum
        $query = "SELECT backup_id, languages_id, products_description, products_meta_title,
                  products_meta_description, products_meta_keywords, products_keywords
                  FROM rz_ai_optimizer_backup
                  WHERE products_id = '" . (int)$productId . "'
                  AND backup_date = '" . xtc_db_input($backupDate) . "'
                  ORDER BY languages_id";
        $result = xtc_db_query($query);

        $restored = 0;
        $backupIds = [];

        while ($row = xtc_db_fetch_array($result)) {
            $langId = $row['languages_id'];

            // Stelle Daten wieder her
            $updateQuery = "UPDATE products_description SET
                products_description = '" . xtc_db_input($row['products_description']) . "',
                products_meta_title = '" . xtc_db_input($row['products_meta_title']) . "',
                products_meta_description = '" . xtc_db_input($row['products_meta_description']) . "',
                products_meta_keywords = '" . xtc_db_input($row['products_meta_keywords']) . "',
                products_keywords = '" . xtc_db_input($row['products_keywords']) . "'
                WHERE products_id = '" . (int)$productId . "'
                AND language_id = '" . (int)$langId . "'";

            xtc_db_query($updateQuery);
            $restored++;
            $backupIds[] = $row['backup_id'];
        }

        // Markiere diese Backups als wiederhergestellt
        if (!empty($backupIds)) {
            $updateQuery = "UPDATE rz_ai_optimizer_backup
                SET restored = 1
                WHERE backup_id IN (" . implode(',', $backupIds) . ")";
            xtc_db_query($updateQuery);
        }

        return $restored;
    }
}
}
