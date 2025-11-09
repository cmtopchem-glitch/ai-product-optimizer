<?php
/* --------------------------------------------------------------
   PromptLibraryService.inc.php 2025-11-08
   REDOzone
   http://www.redozone.com
   Copyright (c) 2025 REDOzone
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------
*/

if (!class_exists('PromptLibraryService')) {
class PromptLibraryService
{
    /**
     * Stellt sicher, dass die Prompt-Library-Tabelle existiert
     * Erstellt die Tabelle bei Bedarf automatisch
     */
    private static function ensureTableExists()
    {
        // Prüfe ob Tabelle existiert
        $checkQuery = "SHOW TABLES LIKE 'rz_ai_prompt_library'";
        $result = xtc_db_query($checkQuery);

        if (xtc_db_num_rows($result) > 0) {
            return; // Tabelle existiert bereits
        }

        // Tabelle existiert nicht - erstelle sie
        $createTableQuery = "CREATE TABLE IF NOT EXISTS `rz_ai_prompt_library` (
          `prompt_id` int(11) NOT NULL AUTO_INCREMENT,
          `prompt_type` varchar(20) NOT NULL DEFAULT 'product' COMMENT 'Typ: product oder vision',
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
          KEY `idx_prompt_type` (`prompt_type`),
          KEY `idx_is_active` (`is_active`),
          KEY `idx_is_default` (`is_default`),
          KEY `idx_usage_count` (`usage_count`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Prompt-Bibliothek für AI Product Optimizer'";

        xtc_db_query($createTableQuery);

        // Füge Standard-Prompts ein
        self::insertDefaultPrompts();
    }

    /**
     * Fügt die Standard-Prompts ein, falls noch keine vorhanden sind
     */
    private static function insertDefaultPrompts()
    {
        // Prüfen ob bereits Prompts vorhanden sind
        $result = xtc_db_query("SELECT COUNT(*) as count FROM `rz_ai_prompt_library`");
        $row = xtc_db_fetch_array($result);

        if ($row['count'] > 0) {
            return; // Bereits Prompts vorhanden
        }

        // Standard-Prompts aus default_prompts.sql laden und ausführen
        $sqlFile = DIR_FS_CATALOG . 'GXModules/REDOzone/AIProductOptimizer/default_prompts.sql';

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
     * Holt alle aktiven Prompts aus der Bibliothek
     * @param bool $activeOnly Nur aktive Prompts
     * @param string $promptType Filter nach Typ: 'product', 'vision' oder leer für alle
     * @return array Array von Prompts
     */
    public static function getAllPrompts($activeOnly = true, $promptType = '')
    {
        // Stelle sicher, dass die Tabelle existiert
        self::ensureTableExists();

        $query = "SELECT prompt_id, prompt_type, prompt_label, prompt_description, system_prompt,
                  user_prompt, is_default, is_active, created_at, updated_at,
                  usage_count, last_used_at
                  FROM rz_ai_prompt_library";

        $conditions = [];
        if ($activeOnly) {
            $conditions[] = "is_active = 1";
        }
        if (!empty($promptType)) {
            $conditions[] = "prompt_type = '" . xtc_db_input($promptType) . "'";
        }

        if (count($conditions) > 0) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " ORDER BY is_default DESC, usage_count DESC, prompt_label ASC";

        $result = xtc_db_query($query);

        $prompts = [];
        while ($row = xtc_db_fetch_array($result)) {
            $prompts[] = [
                'prompt_id' => $row['prompt_id'],
                'prompt_type' => $row['prompt_type'],
                'prompt_label' => $row['prompt_label'],
                'prompt_description' => $row['prompt_description'],
                'system_prompt' => $row['system_prompt'],
                'user_prompt' => $row['user_prompt'],
                'is_default' => $row['is_default'],
                'is_active' => $row['is_active'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'usage_count' => $row['usage_count'],
                'last_used_at' => $row['last_used_at']
            ];
        }

        return $prompts;
    }

    /**
     * Holt einen spezifischen Prompt anhand der ID
     * @param int $promptId Die ID des Prompts
     * @return array|null Prompt-Daten oder null wenn nicht gefunden
     */
    public static function getPromptById($promptId)
    {
        // Stelle sicher, dass die Tabelle existiert
        self::ensureTableExists();

        $query = "SELECT prompt_id, prompt_type, prompt_label, prompt_description, system_prompt,
                  user_prompt, is_default, is_active, created_at, updated_at,
                  usage_count, last_used_at
                  FROM rz_ai_prompt_library
                  WHERE prompt_id = '" . (int)$promptId . "'";

        $result = xtc_db_query($query);

        if ($row = xtc_db_fetch_array($result)) {
            return [
                'prompt_id' => $row['prompt_id'],
                'prompt_type' => $row['prompt_type'],
                'prompt_label' => $row['prompt_label'],
                'prompt_description' => $row['prompt_description'],
                'system_prompt' => $row['system_prompt'],
                'user_prompt' => $row['user_prompt'],
                'is_default' => $row['is_default'],
                'is_active' => $row['is_active'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'usage_count' => $row['usage_count'],
                'last_used_at' => $row['last_used_at']
            ];
        }

        return null;
    }

    /**
     * Holt den Standard-Prompt
     * @return array|null Standard-Prompt oder null
     */
    public static function getDefaultPrompt()
    {
        // Stelle sicher, dass die Tabelle existiert
        self::ensureTableExists();

        $query = "SELECT prompt_id, prompt_label, prompt_description, system_prompt,
                  user_prompt, is_default, is_active, created_at, updated_at,
                  usage_count, last_used_at
                  FROM rz_ai_prompt_library
                  WHERE is_default = 1 AND is_active = 1
                  LIMIT 1";

        $result = xtc_db_query($query);

        if ($row = xtc_db_fetch_array($result)) {
            return [
                'prompt_id' => $row['prompt_id'],
                'prompt_label' => $row['prompt_label'],
                'prompt_description' => $row['prompt_description'],
                'system_prompt' => $row['system_prompt'],
                'user_prompt' => $row['user_prompt'],
                'is_default' => $row['is_default'],
                'is_active' => $row['is_active'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'usage_count' => $row['usage_count'],
                'last_used_at' => $row['last_used_at']
            ];
        }

        return null;
    }

    /**
     * Speichert einen neuen Prompt
     * @param string $label Label/Name des Prompts
     * @param string $systemPrompt System Prompt
     * @param string $userPrompt User Prompt Template
     * @param string $description Optionale Beschreibung
     * @param bool $isDefault Ob dies der Standard-Prompt sein soll
     * @param string $promptType Typ des Prompts: 'product' oder 'vision'
     * @return int Die ID des neu erstellten Prompts
     */
    public static function createPrompt($label, $systemPrompt, $userPrompt, $description = '', $isDefault = false, $promptType = 'product')
    {
        // Stelle sicher, dass die Tabelle existiert
        self::ensureTableExists();

        // Wenn dieser Prompt als Standard markiert wird, entferne Default-Flag von allen anderen desselben Typs
        if ($isDefault) {
            self::clearDefaultFlag($promptType);
        }

        $query = "INSERT INTO rz_ai_prompt_library
            (prompt_type, prompt_label, prompt_description, system_prompt, user_prompt,
             is_default, is_active, created_at, usage_count)
            VALUES (
                '" . xtc_db_input($promptType) . "',
                '" . xtc_db_input($label) . "',
                '" . xtc_db_input($description) . "',
                '" . xtc_db_input($systemPrompt) . "',
                '" . xtc_db_input($userPrompt) . "',
                " . ($isDefault ? '1' : '0') . ",
                1,
                NOW(),
                0
            )";

        xtc_db_query($query);
        return xtc_db_insert_id();
    }

    /**
     * Aktualisiert einen existierenden Prompt
     * @param int $promptId Die ID des Prompts
     * @param string $label Label/Name des Prompts
     * @param string $systemPrompt System Prompt
     * @param string $userPrompt User Prompt Template
     * @param string $description Optionale Beschreibung
     * @param bool $isDefault Ob dies der Standard-Prompt sein soll
     * @param bool $isActive Ob der Prompt aktiv ist
     * @return bool Erfolg
     */
    public static function updatePrompt($promptId, $label, $systemPrompt, $userPrompt, $description = '', $isDefault = false, $isActive = true)
    {
        // Wenn dieser Prompt als Standard markiert wird, entferne Default-Flag von allen anderen desselben Typs
        if ($isDefault) {
            // Hole den Typ des aktuellen Prompts
            $currentPrompt = self::getPromptById($promptId);
            if ($currentPrompt) {
                self::clearDefaultFlag($currentPrompt['prompt_type']);
            }
        }

        $query = "UPDATE rz_ai_prompt_library SET
            prompt_label = '" . xtc_db_input($label) . "',
            prompt_description = '" . xtc_db_input($description) . "',
            system_prompt = '" . xtc_db_input($systemPrompt) . "',
            user_prompt = '" . xtc_db_input($userPrompt) . "',
            is_default = " . ($isDefault ? '1' : '0') . ",
            is_active = " . ($isActive ? '1' : '0') . ",
            updated_at = NOW()
            WHERE prompt_id = '" . (int)$promptId . "'";

        xtc_db_query($query);
        return xtc_db_affected_rows() > 0;
    }

    /**
     * Löscht einen Prompt
     * @param int $promptId Die ID des Prompts
     * @return bool Erfolg
     */
    public static function deletePrompt($promptId)
    {
        $query = "DELETE FROM rz_ai_prompt_library
                  WHERE prompt_id = '" . (int)$promptId . "'";

        xtc_db_query($query);
        return xtc_db_affected_rows() > 0;
    }

    /**
     * Setzt einen Prompt als Standard
     * @param int $promptId Die ID des Prompts
     * @return bool Erfolg
     */
    public static function setAsDefault($promptId)
    {
        // Hole den Typ des Prompts
        $prompt = self::getPromptById($promptId);
        if (!$prompt) {
            return false;
        }

        // Entferne Default-Flag von allen Prompts desselben Typs
        self::clearDefaultFlag($prompt['prompt_type']);

        // Setze neuen Default
        $query = "UPDATE rz_ai_prompt_library
                  SET is_default = 1
                  WHERE prompt_id = '" . (int)$promptId . "'";

        xtc_db_query($query);
        return xtc_db_affected_rows() > 0;
    }

    /**
     * Entfernt das Default-Flag von allen Prompts eines bestimmten Typs
     * @param string $promptType Typ der Prompts ('product' oder 'vision'), leer für alle
     */
    private static function clearDefaultFlag($promptType = '')
    {
        $query = "UPDATE rz_ai_prompt_library SET is_default = 0";
        if (!empty($promptType)) {
            $query .= " WHERE prompt_type = '" . xtc_db_input($promptType) . "'";
        }
        xtc_db_query($query);
    }

    /**
     * Erhöht den Verwendungszähler eines Prompts
     * @param int $promptId Die ID des Prompts
     */
    public static function incrementUsageCount($promptId)
    {
        $query = "UPDATE rz_ai_prompt_library
                  SET usage_count = usage_count + 1,
                      last_used_at = NOW()
                  WHERE prompt_id = '" . (int)$promptId . "'";

        xtc_db_query($query);
    }

    /**
     * Aktiviert oder deaktiviert einen Prompt
     * @param int $promptId Die ID des Prompts
     * @param bool $active Der neue Status
     * @return bool Erfolg
     */
    public static function setActive($promptId, $active)
    {
        $query = "UPDATE rz_ai_prompt_library
                  SET is_active = " . ($active ? '1' : '0') . ",
                      updated_at = NOW()
                  WHERE prompt_id = '" . (int)$promptId . "'";

        xtc_db_query($query);
        return xtc_db_affected_rows() > 0;
    }

    /**
     * Prüft ob Prompts in der Bibliothek existieren
     * @return bool
     */
    public static function hasPrompts()
    {
        // Stelle sicher, dass die Tabelle existiert
        self::ensureTableExists();

        $query = "SELECT COUNT(*) as count FROM rz_ai_prompt_library";
        $result = xtc_db_query($query);
        $row = xtc_db_fetch_array($result);

        return $row['count'] > 0;
    }
}
}
