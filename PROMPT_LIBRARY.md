# Prompt-Bibliothek für AI Product Optimizer

## Übersicht

Die Prompt-Bibliothek ermöglicht es Ihnen, mehrere Prompt-Templates zu speichern und zu verwalten. Sie können unterschiedliche Prompts für verschiedene Produkttypen, Zielgruppen oder Schreibstile erstellen und jederzeit darauf zugreifen.

## Features

- ✅ **Unbegrenzte Prompts**: Speichern Sie beliebig viele Prompt-Templates
- ✅ **Benutzerdefinierte Labels**: Geben Sie jedem Prompt einen aussagekräftigen Namen
- ✅ **Standard-Prompt**: Markieren Sie einen Prompt als Standard
- ✅ **Verwendungsstatistik**: Sehen Sie, wie oft ein Prompt verwendet wurde
- ✅ **Einfache Verwaltung**: Erstellen, Bearbeiten, Löschen über die Konfigurationsseite
- ✅ **Vordefinierte Templates**: 4 Standard-Prompts für verschiedene Anwendungsfälle

## Installation

### 1. Datenbank aktualisieren

Führen Sie das SQL-Script aus, um die Datenbanktabelle zu erstellen:

```sql
-- Die Tabelle wird automatisch erstellt, wenn Sie install.sql ausführen
-- Oder führen Sie nur den Prompt-Library-Teil aus:
CREATE TABLE IF NOT EXISTS `rz_ai_prompt_library` (
  -- Siehe install.sql für vollständiges Schema
);
```

### 2. Standard-Prompts importieren (optional)

Um die vordefinierten Standard-Prompts zu importieren, führen Sie aus:

```sql
-- Führen Sie die Datei default_prompts.sql aus
-- Diese erstellt 4 Standard-Prompts:
-- 1. SEO-optimiert (Standard)
-- 2. Verkaufsorientiert
-- 3. Technisch & Informativ
-- 4. Kurz & Prägnant
```

**Wichtig**: Führen Sie `default_prompts.sql` nur EINMAL aus, um Duplikate zu vermeiden!

## Verwendung

### Prompts verwalten

1. Öffnen Sie die Konfigurationsseite des AI Product Optimizer
2. Scrollen Sie zum Abschnitt "📚 Prompt-Bibliothek"
3. Hier können Sie:
   - Neue Prompts erstellen
   - Bestehende Prompts bearbeiten
   - Prompts löschen
   - Einen Prompt als Standard setzen
   - Prompts in das Konfigurationsformular laden

### Neuen Prompt erstellen

1. Klicken Sie auf "➕ Neuen Prompt speichern"
2. Geben Sie ein Label ein (z.B. "Premium-Produkte")
3. Optional: Beschreibung hinzufügen
4. System-Prompt und User-Prompt eingeben
5. Optional: Als Standard-Prompt markieren
6. Speichern

**Tipp**: Klicken Sie auf "Neuen Prompt speichern" während Sie bereits Prompts im Formular haben - diese werden automatisch in den Dialog kopiert!

### Prompt bearbeiten

1. Klicken Sie bei einem Prompt auf "✏️ Bearbeiten"
2. Nehmen Sie Ihre Änderungen vor
3. Speichern Sie die Änderungen

### Prompt laden

1. Klicken Sie bei einem Prompt auf "📥 Laden"
2. Der Prompt wird in die Konfigurationsformular-Felder geladen
3. Vergessen Sie nicht, auf "💾 Speichern" zu klicken!

### Standard-Prompt festlegen

Der Standard-Prompt wird automatisch verwendet, wenn:
- Kein spezifischer Prompt ausgewählt wurde
- Die Generierung über die Produktseite gestartet wird

Um einen Prompt als Standard zu setzen:
1. Klicken Sie auf "⭐ Als Standard" beim gewünschten Prompt
2. Der bisherige Standard-Prompt verliert automatisch den Standard-Status

## Standard-Prompts Übersicht

### 1. SEO-optimiert (Standard) ⭐
- **Fokus**: Suchmaschinenoptimierung
- **Zielgruppe**: Alle Produkttypen
- **Besonderheiten**:
  - Umfangreiche Keywords
  - Meta-Tags optimiert
  - 300-500 Wörter Beschreibung

### 2. Verkaufsorientiert 🎯
- **Fokus**: Conversion und Emotionen
- **Zielgruppe**: B2C, Premium-Produkte
- **Besonderheiten**:
  - Emotionale Ansprache
  - Nutzen statt Features
  - Starker Call-to-Action

### 3. Technisch & Informativ 🔧
- **Fokus**: Sachlichkeit und Details
- **Zielgruppe**: B2B, technische Produkte
- **Besonderheiten**:
  - Spezifikationen
  - Fachterminologie
  - 350-500 Wörter

### 4. Kurz & Prägnant ⚡
- **Fokus**: Schnelle Übersicht
- **Zielgruppe**: Einfache Produkte, mobile Nutzer
- **Besonderheiten**:
  - 150-250 Wörter
  - Bulletpoints
  - Klare Struktur

## Platzhalter

Alle Prompts unterstützen folgende Platzhalter:

- `{PRODUCT_NAME}` - Produktname
- `{ORIGINAL_TEXT}` - Original Produktbeschreibung
- `{LANGUAGE}` - Zielsprache
- `{BRAND_LINE}` - Marke (optional)
- `{CATEGORY_LINE}` - Kategorie (optional)
- `[[MEDIA_TAG_X]]` - Media-Tags (werden automatisch verwaltet)

**Wichtig**: Die `[[MEDIA_TAG_X]]` Platzhalter werden automatisch vom System erstellt und müssen in der generierten Beschreibung beibehalten werden!

## Best Practices

### Prompt-Struktur

Ein guter Prompt sollte:
1. Klar die Rolle definieren (System-Prompt)
2. Spezifische Anforderungen listen
3. Das gewünschte Format beschreiben
4. Wichtige Constraints betonen

### System-Prompt

```
Du bist ein [Rolle]. Du [Hauptaufgabe].
```

Beispiel:
```
Du bist ein professioneller E-Commerce SEO-Texter.
Du antwortest immer im angeforderten JSON-Format.
```

### User-Prompt

Strukturieren Sie den User-Prompt:

1. **Kontext**: Produktinformationen
2. **Aufgabe**: Was soll gemacht werden
3. **Anforderungen**: Spezifische Vorgaben
4. **Format**: JSON-Struktur
5. **Wichtige Hinweise**: Constraints

### JSON-Format

Alle Prompts MÜSSEN folgendes JSON-Format zurückgeben:

```json
{
  "product_name": "Übersetzter Produktname",
  "product_description": "Optimierte Beschreibung",
  "meta_title": "Meta-Titel (max 60 Zeichen)",
  "meta_description": "Meta-Description (max 160 Zeichen)",
  "meta_keywords": "keyword1, keyword2, keyword3, ...",
  "search_keywords": "suchwort1, suchwort2, suchwort3, ..."
}
```

## Verwendungsstatistik

Die Bibliothek trackt automatisch:
- **usage_count**: Wie oft ein Prompt verwendet wurde
- **last_used_at**: Wann der Prompt zuletzt verwendet wurde

Diese Informationen helfen Ihnen zu verstehen, welche Prompts am meisten genutzt werden.

## Technische Details

### Datenbank-Tabelle

```sql
rz_ai_prompt_library
├── prompt_id (INT, PRIMARY KEY)
├── prompt_label (VARCHAR)
├── prompt_description (TEXT)
├── system_prompt (TEXT)
├── user_prompt (LONGTEXT)
├── is_default (TINYINT)
├── is_active (TINYINT)
├── created_at (DATETIME)
├── updated_at (DATETIME)
├── usage_count (INT)
└── last_used_at (DATETIME)
```

### Service-Klasse

`PromptLibraryService.inc.php` bietet folgende Methoden:
- `getAllPrompts($activeOnly)`
- `getPromptById($promptId)`
- `getDefaultPrompt()`
- `createPrompt(...)`
- `updatePrompt(...)`
- `deletePrompt($promptId)`
- `setAsDefault($promptId)`
- `incrementUsageCount($promptId)`

### Integration

Der `OpenAIService` wurde erweitert um:
- Automatisches Laden des Default-Prompts aus der Bibliothek
- `usePromptFromLibrary($promptId)` Methode
- Fallback auf Konfigurations-Prompts wenn Bibliothek leer

## Troubleshooting

### Prompts werden nicht angezeigt
- Prüfen Sie ob die Tabelle `rz_ai_prompt_library` existiert
- Führen Sie `install.sql` aus wenn nötig

### Standard-Prompt wird nicht verwendet
- Stellen Sie sicher, dass ein Prompt `is_default = 1` hat
- Nur ein Prompt kann Standard sein
- System wechselt automatisch zu Konfigurations-Prompts als Fallback

### Änderungen werden nicht gespeichert
- Prüfen Sie Dateiberechtigungen
- Schauen Sie in Browser-Konsole nach Fehlern
- Prüfen Sie Server-Logs

## Erweiterte Anwendungsfälle

### Produkt-spezifische Prompts

Erstellen Sie Prompts für spezifische Produktkategorien:
- "Fashion & Kleidung"
- "Elektronik & Technik"
- "Lebensmittel & Genuss"
- "Handwerk & Werkzeug"

### Zielgruppen-spezifische Prompts

Verschiedene Prompts für verschiedene Zielgruppen:
- "B2B Geschäftskunden"
- "B2C Endverbraucher"
- "Premium Segment"
- "Preisbewusste Käufer"

### Sprach-spezifische Prompts

Optimieren Sie Prompts für verschiedene Märkte:
- "Deutsch - Förmlich"
- "Deutsch - Locker"
- "Englisch - US Market"
- "Englisch - UK Market"

## Support

Bei Fragen oder Problemen:
1. Prüfen Sie diese Dokumentation
2. Schauen Sie in `install.sql` und `default_prompts.sql`
3. Kontaktieren Sie den Support

## Changelog

### Version 1.0 (2025-11-08)
- ✅ Initiales Release
- ✅ Prompt-Bibliothek Verwaltung
- ✅ 4 Standard-Prompts
- ✅ Verwendungsstatistik
- ✅ Standard-Prompt Funktion
- ✅ Integration in Generierungsflow
