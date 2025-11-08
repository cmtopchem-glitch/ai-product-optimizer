-- ============================================================
-- AI Product Optimizer - Installation Script
-- Version: 1.0
-- Date: 2025-11-03
-- ============================================================
--
-- Dieses SQL-Script erstellt die notwendige Datenbanktabelle
-- für die Backup-Verwaltung des AI Product Optimizer Moduls
-- für Gambio GX 4.8
--
-- ============================================================

-- Backup-Tabelle für Produkttexte
CREATE TABLE IF NOT EXISTS `rz_ai_optimizer_backup` (
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
  KEY `idx_restored` (`restored`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Backup-Tabelle für AI Product Optimizer';

-- Index für schnelle Abfragen nach nicht-wiederhergestellten Backups
CREATE INDEX `idx_product_restored` ON `rz_ai_optimizer_backup` (`products_id`, `restored`);

-- ============================================================
-- Hinweise:
-- ============================================================
--
-- 1. Diese Tabellen werden automatisch beim Installieren des Moduls
--    über die Gambio Admin-Oberfläche erstellt
--
-- 2. Backups werden automatisch erstellt, wenn ein Produkt
--    mit dem AI Optimizer bearbeitet wird
--
-- 3. Alte Backups (älter als 30 Tage) können mit der Methode
--    BackupService::cleanOldBackups() entfernt werden
--
-- 4. Backups können über die Produktseite im Admin mit dem
--    "Original wiederherstellen" Button wiederhergestellt werden
--
-- 5. Die Prompt-Library ermöglicht das Speichern und Verwalten
--    verschiedener Prompt-Templates für unterschiedliche Anwendungsfälle
--
-- ============================================================

-- ============================================================
-- Prompt Library - Tabelle für gespeicherte Prompt-Templates
-- ============================================================

CREATE TABLE IF NOT EXISTS `rz_ai_prompt_library` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Prompt-Bibliothek für AI Product Optimizer';

-- ============================================================
