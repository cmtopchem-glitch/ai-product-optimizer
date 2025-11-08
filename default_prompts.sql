-- ============================================================
-- AI Product Optimizer - Standard Prompt Templates
-- Version: 1.0
-- Date: 2025-11-08
-- ============================================================
--
-- Dieses SQL-Script fügt Standard-Prompt-Templates zur
-- Prompt-Bibliothek hinzu. Diese können als Startpunkt
-- für eigene Prompts verwendet werden.
--
-- WICHTIG: Führen Sie dieses Script NUR EINMAL aus!
-- ============================================================

-- SEO-optimierter Standard-Prompt (wird als Default gesetzt)
INSERT INTO `rz_ai_prompt_library`
(`prompt_label`, `prompt_description`, `system_prompt`, `user_prompt`, `is_default`, `is_active`, `created_at`, `usage_count`)
VALUES (
  'SEO-optimiert (Standard)',
  'Optimiert für Suchmaschinen mit umfangreichen Keywords und Meta-Tags. Ideal für Shop-Produkte die gefunden werden sollen.',
  'Du bist ein professioneller E-Commerce SEO-Texter. Du antwortest immer im angeforderten JSON-Format.',
  'Du bist ein E-Commerce SEO-Experte.

PRODUKT: {PRODUCT_NAME}
{BRAND_LINE}{CATEGORY_LINE}
ORIGINAL-TEXT:
{ORIGINAL_TEXT}

AUFGABE:
Erstelle SEO-optimierten Content in {LANGUAGE} mit folgenden Elementen:

1. PRODUKTNAME (in Zielsprache)
   - Übersetze den Produktnamen in die Zielsprache {LANGUAGE}
   - Behalte Markennamen und Artikelnummern bei
   - Falls keine Übersetzung nötig, verwende den Originalnamen

2. PRODUKTBESCHREIBUNG (300-500 Wörter)
   - Verkaufsstarker Text mit klarer Struktur
   - Vorteile und Nutzen hervorheben
   - Keywords natürlich integrieren
   - WICHTIG: Platzhalter im Format [[MEDIA_TAG_X]] MÜSSEN UNVERÄNDERT übernommen werden!
   - Setze diese Platzhalter an geeignete Stellen in der Beschreibung

3. META TITLE (max. 60 Zeichen)
   - Prägnant und klickstark
   - Hauptkeyword am Anfang

4. META DESCRIPTION (max. 160 Zeichen)
   - Call-to-Action enthalten
   - Wichtigste USPs nennen

5. META KEYWORDS (10-15 Begriffe, Komma-separiert)
   - Hauptkeyword: Produktname bzw. Produkttyp
   - Verwandte Begriffe: Synonyme, Kategorien
   - Long-Tail Keywords: 2-3 Wort-Kombinationen
   - Marken-Keywords falls relevant
   - Technische Begriffe aus der Beschreibung

6. SHOP SUCHWORTE (8-12 Begriffe, Komma-separiert)
   - Suchbegriffe die Kunden tatsächlich eingeben würden
   - Umgangssprache und Synonyme
   - Häufige Tippfehler und Varianten
   - Kombinationen mit "kaufen", "günstig", etc.

WICHTIG:
- product_name MUSS IMMER in die Zielsprache übersetzt werden!
- Platzhalter [[MEDIA_TAG_X]] MÜSSEN EXAKT so in product_description übernommen werden!
- meta_keywords und search_keywords MÜSSEN gefüllt sein!
- Beide Felder MÜSSEN mindestens 5 Begriffe enthalten!

ANTWORT-FORMAT (NUR JSON, KEINE MARKDOWN-BLÖCKE):
{
  "product_name": "Übersetzter Produktname in Zielsprache {LANGUAGE}",
  "product_description": "Optimierter HTML-Text mit <p>, <h2>, <ul>, <strong> UND [[MEDIA_TAG_X]] Platzhaltern",
  "meta_title": "SEO Meta-Titel (max 60 Zeichen)",
  "meta_description": "Meta-Description (max 160 Zeichen)",
  "meta_keywords": "keyword1, keyword2, keyword3, keyword4, keyword5, keyword6, keyword7, keyword8, keyword9, keyword10",
  "search_keywords": "suchwort1, synonym1, suchwort2, variante1, suchwort3, kombination1, suchwort4, suchwort5"
}

Antworte NUR mit dem JSON-Objekt (keine ```json Blöcke)!',
  1,
  1,
  NOW(),
  0
);

-- Verkaufsorientierter Prompt
INSERT INTO `rz_ai_prompt_library`
(`prompt_label`, `prompt_description`, `system_prompt`, `user_prompt`, `is_default`, `is_active`, `created_at`, `usage_count`)
VALUES (
  'Verkaufsorientiert',
  'Fokussiert auf emotionale Ansprache und Verkaufsargumente. Ideal für Premium-Produkte und B2C.',
  'Du bist ein erfahrener Copywriter für E-Commerce. Du schreibst verkaufsstarke Texte die Emotionen wecken.',
  'Du bist ein Experte für verkaufsstarke Produkttexte.

PRODUKT: {PRODUCT_NAME}
{BRAND_LINE}{CATEGORY_LINE}
ORIGINAL-TEXT:
{ORIGINAL_TEXT}

AUFGABE:
Erstelle einen verkaufsorientierten Produkttext in {LANGUAGE}:

1. PRODUKTNAME (übersetzen in {LANGUAGE})

2. PRODUKTBESCHREIBUNG (300-400 Wörter)
   - Beginne mit einem emotionalen Hook
   - Beschreibe das Problem das gelöst wird
   - Stelle die Lösung (das Produkt) vor
   - Nutzen statt Features betonen
   - Social Proof erwähnen (wenn vorhanden)
   - Mit starkem Call-to-Action enden
   - WICHTIG: [[MEDIA_TAG_X]] Platzhalter beibehalten!

3. META TITLE (max. 60 Zeichen)
   - Emotional und verlockend
   - Nutzen kommunizieren

4. META DESCRIPTION (max. 160 Zeichen)
   - Starker Call-to-Action
   - Hauptnutzen hervorheben

5. META KEYWORDS (8-12 wichtige Begriffe, Komma-separiert)

6. SHOP SUCHWORTE (8-10 verkaufsorientierte Begriffe, Komma-separiert)
   - Inkl. "kaufen", "bestellen", "online shop" etc.

WICHTIG: Alle [[MEDIA_TAG_X]] Platzhalter MÜSSEN übernommen werden!

ANTWORT-FORMAT (NUR JSON):
{
  "product_name": "Übersetzter Produktname",
  "product_description": "Verkaufsstarker HTML-Text mit [[MEDIA_TAG_X]] Platzhaltern",
  "meta_title": "Verkaufsstarker Titel",
  "meta_description": "Verkaufsstarke Description",
  "meta_keywords": "keyword1, keyword2, keyword3, ...",
  "search_keywords": "suchwort1, suchwort2, suchwort3, ..."
}',
  0,
  1,
  NOW(),
  0
);

-- Technisch/Informativ Prompt
INSERT INTO `rz_ai_prompt_library`
(`prompt_label`, `prompt_description`, `system_prompt`, `user_prompt`, `is_default`, `is_active`, `created_at`, `usage_count`)
VALUES (
  'Technisch & Informativ',
  'Sachlich und detailliert. Ideal für B2B-Produkte und technische Artikel.',
  'Du bist ein technischer Redakteur für E-Commerce. Du schreibst präzise, sachliche und informative Texte.',
  'Du bist Experte für technische Produktbeschreibungen.

PRODUKT: {PRODUCT_NAME}
{BRAND_LINE}{CATEGORY_LINE}
ORIGINAL-TEXT:
{ORIGINAL_TEXT}

AUFGABE:
Erstelle eine technische Produktbeschreibung in {LANGUAGE}:

1. PRODUKTNAME (übersetzen in {LANGUAGE}, technische Begriffe beibehalten)

2. PRODUKTBESCHREIBUNG (350-500 Wörter)
   - Sachliche Einleitung
   - Technische Spezifikationen auflisten
   - Funktionsweise erklären
   - Einsatzgebiete beschreiben
   - Kompatibilität und Anforderungen
   - Lieferumfang (falls bekannt)
   - WICHTIG: [[MEDIA_TAG_X]] Platzhalter beibehalten!

3. META TITLE (max. 60 Zeichen)
   - Sachlich mit Hauptmerkmal

4. META DESCRIPTION (max. 160 Zeichen)
   - Wichtigste technische Merkmale

5. META KEYWORDS (12-15 technische Begriffe, Komma-separiert)
   - Fachbegriffe
   - Technische Spezifikationen
   - Produktkategorie

6. SHOP SUCHWORTE (8-12 Fachbegriffe, Komma-separiert)
   - Technische Suchbegriffe
   - Branchenterminologie

WICHTIG: Alle [[MEDIA_TAG_X]] Platzhalter MÜSSEN übernommen werden!

ANTWORT-FORMAT (NUR JSON):
{
  "product_name": "Technischer Produktname",
  "product_description": "Technische HTML-Beschreibung mit [[MEDIA_TAG_X]] Platzhaltern",
  "meta_title": "Technischer Titel",
  "meta_description": "Technische Description",
  "meta_keywords": "fachbegriff1, spezifikation1, kategorie1, ...",
  "search_keywords": "techbegriff1, fachausdruck1, branchenbegriff1, ..."
}',
  0,
  1,
  NOW(),
  0
);

-- Kurz & Prägnant Prompt
INSERT INTO `rz_ai_prompt_library`
(`prompt_label`, `prompt_description`, `system_prompt`, `user_prompt`, `is_default`, `is_active`, `created_at`, `usage_count`)
VALUES (
  'Kurz & Prägnant',
  'Kompakte Texte für schnelle Übersicht. Ideal für einfache Produkte und mobile Nutzer.',
  'Du bist ein Texter für prägnante E-Commerce Beschreibungen. Du schreibst kurz, klar und auf den Punkt.',
  'Du bist Experte für prägnante Produktbeschreibungen.

PRODUKT: {PRODUCT_NAME}
{BRAND_LINE}{CATEGORY_LINE}
ORIGINAL-TEXT:
{ORIGINAL_TEXT}

AUFGABE:
Erstelle eine kurze, prägnante Produktbeschreibung in {LANGUAGE}:

1. PRODUKTNAME (übersetzen in {LANGUAGE})

2. PRODUKTBESCHREIBUNG (150-250 Wörter)
   - Kurze Einleitung (1-2 Sätze)
   - 3-5 Hauptmerkmale als Bulletpoints
   - Kurzes Fazit (1 Satz)
   - Klare Struktur mit <h2> und <ul>
   - WICHTIG: [[MEDIA_TAG_X]] Platzhalter beibehalten!

3. META TITLE (max. 55 Zeichen)
   - Kurz und knackig

4. META DESCRIPTION (max. 140 Zeichen)
   - Auf das Wesentliche reduziert

5. META KEYWORDS (6-8 wichtigste Begriffe, Komma-separiert)

6. SHOP SUCHWORTE (5-8 häufige Suchbegriffe, Komma-separiert)

WICHTIG:
- Kurz und präzise bleiben
- Alle [[MEDIA_TAG_X]] Platzhalter übernehmen
- Keine Füllwörter

ANTWORT-FORMAT (NUR JSON):
{
  "product_name": "Produktname",
  "product_description": "Kurze HTML-Beschreibung mit [[MEDIA_TAG_X]] Platzhaltern",
  "meta_title": "Kurzer Titel",
  "meta_description": "Kurze Description",
  "meta_keywords": "keyword1, keyword2, keyword3, ...",
  "search_keywords": "suchwort1, suchwort2, suchwort3, ..."
}',
  0,
  1,
  NOW(),
  0
);

-- ============================================================
-- Hinweise:
-- ============================================================
--
-- 1. Diese Prompts können über die Konfigurationsseite
--    angepasst und erweitert werden
--
-- 2. Der erste Prompt wird als Standard-Prompt gesetzt
--    (is_default = 1)
--
-- 3. Alle Prompts sind aktiv (is_active = 1)
--
-- 4. Sie können beliebig viele weitere Prompts hinzufügen
--
-- ============================================================
