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

-- Prompt-Library-Tabelle für verschiedene Prompt-Templates
CREATE TABLE IF NOT EXISTS `rz_ai_prompt_library` (
  `prompt_id` int(11) NOT NULL AUTO_INCREMENT,
  `prompt_name` varchar(255) NOT NULL COMMENT 'Name des Prompt-Templates',
  `prompt_description` text DEFAULT NULL COMMENT 'Beschreibung des Prompts',
  `system_prompt` text NOT NULL COMMENT 'System-Prompt für OpenAI',
  `user_prompt` text NOT NULL COMMENT 'User-Prompt Template mit Platzhaltern',
  `language_code` varchar(10) DEFAULT NULL COMMENT 'Sprach-Code (leer = alle Sprachen)',
  `is_default` tinyint(1) DEFAULT 0 COMMENT 'Standard-Prompt Flag',
  `active` tinyint(1) DEFAULT 1 COMMENT 'Aktiv-Status',
  `created_date` datetime NOT NULL COMMENT 'Erstellungsdatum',
  `modified_date` datetime DEFAULT NULL COMMENT 'Letztes Änderungsdatum',
  PRIMARY KEY (`prompt_id`),
  KEY `idx_active` (`active`),
  KEY `idx_default` (`is_default`),
  KEY `idx_language` (`language_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Prompt-Library für AI Product Optimizer';

-- Standard-Prompt einfügen
INSERT INTO `rz_ai_prompt_library` (
  `prompt_name`,
  `prompt_description`,
  `system_prompt`,
  `user_prompt`,
  `language_code`,
  `is_default`,
  `active`,
  `created_date`
) VALUES (
  'Standard SEO Prompt (mit Keywords)',
  'Optimierter Prompt für SEO-Content mit Fokus auf Keywords, Meta-Daten und Produktname-Übersetzung',
  'Du bist ein professioneller E-Commerce SEO-Texter. Du antwortest immer im angeforderten JSON-Format.',
  'Du bist ein E-Commerce SEO-Experte.\n\nPRODUKT: {PRODUCT_NAME}\n{BRAND_LINE}{CATEGORY_LINE}\nORIGINAL-TEXT:\n{ORIGINAL_TEXT}\n\nAUFGABE:\nErstelle SEO-optimierten Content in {LANGUAGE} mit folgenden Elementen:\n\n1. PRODUKTNAME (in Zielsprache)\n   - Übersetze den Produktnamen in die Zielsprache {LANGUAGE}\n   - Behalte Markennamen und Artikelnummern bei\n   - Falls keine Übersetzung nötig, verwende den Originalnamen\n\n2. PRODUKTBESCHREIBUNG (300-500 Wörter)\n   - Verkaufsstarker Text mit klarer Struktur\n   - Vorteile und Nutzen hervorheben\n   - Keywords natürlich integrieren\n   - WICHTIG: Platzhalter im Format [[MEDIA_TAG_X]] MÜSSEN UNVERÄNDERT übernommen werden!\n   - Setze diese Platzhalter an geeignete Stellen in der Beschreibung\n\n3. META TITLE (max. 60 Zeichen)\n   - Prägnant und klickstark\n   - Hauptkeyword am Anfang\n\n4. META DESCRIPTION (max. 160 Zeichen)\n   - Call-to-Action enthalten\n   - Wichtigste USPs nennen\n\n5. META KEYWORDS (10-15 Begriffe, Komma-separiert)\n   - Hauptkeyword: Produktname bzw. Produkttyp\n   - Verwandte Begriffe: Synonyme, Kategorien\n   - Long-Tail Keywords: 2-3 Wort-Kombinationen\n   - Marken-Keywords falls relevant\n   - Technische Begriffe aus der Beschreibung\n   Beispiel: \"Schaumkanone, Foam Gun, Tornador, Autoreinigung, Hochdruckreiniger, Druckluft Schaumpistole, Fahrzeugpflege, Schaumreiniger, Auto Waschen, Detailing Equipment\"\n\n6. SHOP SUCHWORTE (8-12 Begriffe, Komma-separiert)\n   - Suchbegriffe die Kunden tatsächlich eingeben würden\n   - Umgangssprache und Synonyme\n   - Häufige Tippfehler und Varianten\n   - Kombinationen mit \"kaufen\", \"günstig\", etc.\n   Beispiel: \"schaum pistole, foam lance auto, druckluft schaum, reinigungsschaum auto, schaum gerät waschen, tornador alternative, auto schäumer, fahrzeug schaum reiniger\"\n\nWICHTIG:\n- product_name MUSS IMMER in die Zielsprache übersetzt werden!\n- Platzhalter [[MEDIA_TAG_X]] MÜSSEN EXAKT so in product_description übernommen werden!\n- meta_keywords und search_keywords MÜSSEN gefüllt sein!\n- Beide Felder MÜSSEN mindestens 5 Begriffe enthalten!\n- Keywords aus dem ORIGINAL-TEXT extrahieren und erweitern!\n\nANTWORT-FORMAT (NUR JSON, KEINE MARKDOWN-BLÖCKE):\n{\n  \"product_name\": \"Übersetzter Produktname in Zielsprache {LANGUAGE}\",\n  \"product_description\": \"Optimierter HTML-Text mit <p>, <h2>, <ul>, <strong> UND [[MEDIA_TAG_X]] Platzhaltern\",\n  \"meta_title\": \"SEO Meta-Titel (max 60 Zeichen)\",\n  \"meta_description\": \"Meta-Description (max 160 Zeichen)\",\n  \"meta_keywords\": \"keyword1, keyword2, keyword3, keyword4, keyword5, keyword6, keyword7, keyword8, keyword9, keyword10\",\n  \"search_keywords\": \"suchwort1, synonym1, suchwort2, variante1, suchwort3, kombination1, suchwort4, suchwort5\"\n}\n\nAntworte NUR mit dem JSON-Objekt (keine ```json Blöcke)!',
  NULL,
  1,
  1,
  NOW()
);

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
