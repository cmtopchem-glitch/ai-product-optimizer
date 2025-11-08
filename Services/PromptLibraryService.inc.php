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
     * Holt alle aktiven Prompts aus der Bibliothek
     * @return array Array von Prompts
     */
    public static function getAllPrompts($activeOnly = true)
    {
        $query = "SELECT prompt_id, prompt_label, prompt_description, system_prompt,
                  user_prompt, is_default, is_active, created_at, updated_at,
                  usage_count, last_used_at
                  FROM rz_ai_prompt_library";

        if ($activeOnly) {
            $query .= " WHERE is_active = 1";
        }

        $query .= " ORDER BY is_default DESC, usage_count DESC, prompt_label ASC";

        $result = xtc_db_query($query);

        $prompts = [];
        while ($row = xtc_db_fetch_array($result)) {
            $prompts[] = [
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

        return $prompts;
    }

    /**
     * Holt einen spezifischen Prompt anhand der ID
     * @param int $promptId Die ID des Prompts
     * @return array|null Prompt-Daten oder null wenn nicht gefunden
     */
    public static function getPromptById($promptId)
    {
        $query = "SELECT prompt_id, prompt_label, prompt_description, system_prompt,
                  user_prompt, is_default, is_active, created_at, updated_at,
                  usage_count, last_used_at
                  FROM rz_ai_prompt_library
                  WHERE prompt_id = '" . (int)$promptId . "'";

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
     * Holt den Standard-Prompt
     * @return array|null Standard-Prompt oder null
     */
    public static function getDefaultPrompt()
    {
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
     * @return int Die ID des neu erstellten Prompts
     */
    public static function createPrompt($label, $systemPrompt, $userPrompt, $description = '', $isDefault = false)
    {
        // Wenn dieser Prompt als Standard markiert wird, entferne Default-Flag von allen anderen
        if ($isDefault) {
            self::clearDefaultFlag();
        }

        $query = "INSERT INTO rz_ai_prompt_library
            (prompt_label, prompt_description, system_prompt, user_prompt,
             is_default, is_active, created_at, usage_count)
            VALUES (
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
        // Wenn dieser Prompt als Standard markiert wird, entferne Default-Flag von allen anderen
        if ($isDefault) {
            self::clearDefaultFlag();
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
        // Entferne Default-Flag von allen Prompts
        self::clearDefaultFlag();

        // Setze neuen Default
        $query = "UPDATE rz_ai_prompt_library
                  SET is_default = 1
                  WHERE prompt_id = '" . (int)$promptId . "'";

        xtc_db_query($query);
        return xtc_db_affected_rows() > 0;
    }

    /**
     * Entfernt das Default-Flag von allen Prompts
     */
    private static function clearDefaultFlag()
    {
        $query = "UPDATE rz_ai_prompt_library SET is_default = 0";
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
        $query = "SELECT COUNT(*) as count FROM rz_ai_prompt_library";
        $result = xtc_db_query($query);
        $row = xtc_db_fetch_array($result);

        return $row['count'] > 0;
    }
}
}
