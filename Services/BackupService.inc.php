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

class BackupService
{
    /**
     * Erstellt ein Backup der aktuellen Produkttexte
     */
    public static function createBackup($productId)
    {
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
}
