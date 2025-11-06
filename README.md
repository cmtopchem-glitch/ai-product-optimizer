# AI Product Optimizer - Gambio GX 4.8

## Überblick

**AI Product Optimizer** ist ein leistungsstarkes Modul für Gambio GX 4.8, das KI-gestützte SEO-Optimierung für Produkttexte bietet. Das Modul verwendet die OpenAI API (GPT-4), um automatisch hochwertige, suchmaschinenoptimierte Produktbeschreibungen, Meta-Titel, Meta-Descriptions und Keywords in mehreren Sprachen zu generieren.

### Hauptfunktionen

✅ **KI-gestützte Textgenerierung**
- Automatische Erstellung SEO-optimierter Produktbeschreibungen
- Meta-Titel und Meta-Descriptions
- Meta-Keywords und Shop-Suchworte
- Mehrsprachige Unterstützung (alle aktiven Shop-Sprachen)

✅ **Intelligente Backup-Verwaltung**
- Automatische Sicherung vor jeder KI-Generierung
- Ein-Klick Wiederherstellung der Originaltexte
- Versionsverwaltung mit Zeitstempel
- Automatische Cleanup-Funktion für alte Backups

✅ **Nahtlose Integration**
- Direkt in die Gambio Produktseite integriert
- Keine manuelle Konfiguration erforderlich
- Intuitive Benutzeroberfläche
- AJAX-basiert ohne Seitenneuladung

## Installation

### Voraussetzungen
- Gambio GX 4.8.x oder höher
- PHP 7.4 oder höher
- MySQL/MariaDB Datenbank
- OpenAI API Key (erhältlich auf https://platform.openai.com)

### Schritt 1: Modul hochladen
1. Lade das komplette Verzeichnis `ai-product-optimizer` auf deinen Server
2. Platziere es unter: `GXModules/REDOzone/AIProductOptimizer/`

### Schritt 2: Modul installieren
1. Melde dich im Gambio Admin-Bereich an
2. Navigiere zu **Module** → **Gambio Module Center**
3. Suche nach "AI Product Optimizer"
4. Klicke auf **Installieren**
5. Das Modul erstellt automatisch die Backup-Tabelle und Konfigurationseinträge

### Schritt 3: OpenAI API konfigurieren
1. Navigiere zu **AI Product Optimizer** → **Konfiguration**
2. Gib deinen OpenAI API Key ein
3. Wähle das gewünschte GPT-Modell (empfohlen: gpt-4o-mini)
4. Optional: Passe die System- und User-Prompts an
5. Klicke auf **Speichern**

### Alternative: Manuelle Datenbank-Installation
Falls die automatische Installation fehlschlägt:

```bash
mysql -u [username] -p [database] < install.sql
```

## Verwendung

### Produkttexte generieren

1. **Produkt bearbeiten**
   - Öffne ein beliebiges Produkt im Admin-Bereich
   - Gib mindestens Produktname und Beschreibung (Deutsch) ein

2. **KI-Generierung starten**
   - Klicke auf den Button **"SEO-Texte mit KI generieren"**
   - Die KI analysiert deine Eingaben
   - Generiert optimierte Texte für alle aktiven Sprachen
   - Befüllt automatisch alle Felder

3. **Ergebnis prüfen**
   - Kontrolliere die generierten Texte
   - Passe bei Bedarf manuell an
   - Speichere das Produkt

### Original-Texte wiederherstellen

1. **Restore-Button finden**
   - Der Button **"Original wiederherstellen"** erscheint automatisch
   - Nur sichtbar wenn ein Backup existiert

2. **Wiederherstellung durchführen**
   - Klicke auf **"Original wiederherstellen"**
   - Bestätige die Sicherheitsabfrage
   - Die Seite lädt automatisch neu mit den Original-Texten

### Best Practices

**Für optimale Ergebnisse:**
- Verwende aussagekräftige Produktnamen
- Gib eine detaillierte Ausgangsbeschreibung ein
- Füge Kategorie und Markeninformationen hinzu
- Überprüfe die generierten Texte vor dem Speichern
- Nutze die Backup-Funktion bei Unsicherheit

## Backup-Verwaltung

Das Modul verfügt über ein vollautomatisches Backup-System:

### Automatische Backups
- **Zeitpunkt**: Vor jeder KI-Generierung
- **Umfang**: Alle Produkttexte und Meta-Daten
- **Sprachen**: Alle aktiven Shop-Sprachen
- **Speicherung**: In Datenbanktabelle `rz_ai_optimizer_backup`

### Backup-Eigenschaften
- **Versionierung**: Jedes Backup erhält einen Zeitstempel
- **Status-Tracking**: Unterscheidung zwischen aktiven und wiederhergestellten Backups
- **Automatische Cleanup**: Alte Backups (>30 Tage) können automatisch gelöscht werden

### Detaillierte Dokumentation
Siehe [BACKUP_MANAGEMENT.md](BACKUP_MANAGEMENT.md) für:
- Technische Architektur
- API-Dokumentation
- Datenbankstruktur
- Fehlerbehandlung
- Performance-Optimierung

## Dateistruktur

```
ai-product-optimizer/
├── Admin/
│   ├── Classes/
│   │   ├── AIProductOptimizerAjaxHandler.inc.php
│   │   ├── AIProductOptimizerModuleCenterModule.inc.php
│   │   └── Controllers/
│   │       └── AIProductOptimizerModuleCenterModuleController.inc.php
│   ├── Javascript/
│   │   └── ai_optimizer_v2.js
│   ├── Styles/
│   │   └── aiproductoptimizer.css
│   ├── Templates/
│   │   └── config_page.html
│   ├── TextPhrases/
│   │   ├── german/
│   │   ├── english/
│   │   ├── french/
│   │   └── spanish/
│   └── Overloads/
│       └── AdminEditProductExtenderComponent/
│           └── AIProductOptimizerAdminEditProductExtenderComponent.inc.php
├── Services/
│   ├── BackupService.inc.php          # Backup-Verwaltung
│   └── OpenAIService.inc.php          # OpenAI API Integration
├── install.sql                         # Datenbank-Installation
├── BACKUP_MANAGEMENT.md                # Backup-Dokumentation
└── README.md                           # Dieses Dokument
```

## Konfiguration

### OpenAI API Einstellungen

#### API Key
Dein persönlicher OpenAI API Key:
```
Erhältlich auf: https://platform.openai.com/api-keys
Format: sk-...
```

#### Modell-Auswahl
Verfügbare Modelle:
- **gpt-4o** - Höchste Qualität, höhere Kosten
- **gpt-4o-mini** - Empfohlen! Gutes Preis-/Leistungsverhältnis
- **gpt-4-turbo** - Schnell und leistungsstark
- **gpt-3.5-turbo** - Günstigste Option

#### System-Prompt
Definiert die Rolle der KI:
```
Du bist ein professioneller E-Commerce SEO-Texter.
Du antwortest immer im angeforderten JSON-Format.
```

#### User-Prompt
Template für die Textgenerierung (anpassbar):
- Produktname und Beschreibung werden automatisch eingefügt
- Kategorien und Marken optional
- Platzhalter: {PRODUCT_NAME}, {ORIGINAL_TEXT}, {LANGUAGE}

### Datenbank-Konfiguration

Die Konfigurationswerte werden in der `gm_configuration` Tabelle gespeichert:

| Key | Beschreibung | Beispielwert |
|-----|--------------|--------------|
| OPENAI_API_KEY | OpenAI API Schlüssel | sk-... |
| OPENAI_MODEL | Verwendetes GPT-Modell | gpt-4o-mini |
| OPENAI_SYSTEM_PROMPT | System-Anweisung | Du bist... |
| OPENAI_USER_PROMPT | Prompt-Template | Erstelle SEO... |

## API-Integration

### OpenAI API
Das Modul nutzt die OpenAI Chat Completions API:

**Endpoint**: `https://api.openai.com/v1/chat/completions`

**Request-Format**:
```json
{
  "model": "gpt-4o-mini",
  "messages": [
    {"role": "system", "content": "Du bist ein SEO-Texter..."},
    {"role": "user", "content": "Produkt: ..."}
  ],
  "temperature": 0.7,
  "max_tokens": 2000
}
```

**Response-Format**:
```json
{
  "product_description": "Optimierter HTML-Text...",
  "meta_title": "SEO Meta-Titel",
  "meta_description": "Meta-Description",
  "meta_keywords": "keyword1, keyword2, ...",
  "search_keywords": "suchwort1, suchwort2, ..."
}
```

### AJAX-Endpunkte

#### Textgenerierung
```
POST admin.php?do=AIProductOptimizerModuleCenterModule/Generate
Parameter:
  - product_id: Produkt-ID
  - product_name: Produktname
  - original_text: Ausgangsbeschreibung
  - category: Kategorie (optional)
  - brand: Marke (optional)
```

#### Backup prüfen
```
GET admin.php?do=AIProductOptimizerModuleCenterModule/CheckBackup
Parameter:
  - product_id: Produkt-ID
```

#### Backup wiederherstellen
```
POST admin.php?do=AIProductOptimizerModuleCenterModule/Restore
Parameter:
  - product_id: Produkt-ID
```

## Fehlerbehandlung

### Häufige Probleme

#### 1. "Fatal error: Cannot declare class AIProductOptimizerModuleCenterModuleController"
**Ursache**: Backup-Verzeichnisse im Modul-Pfad verursachen Konflikte
**Symptome**:
- Fehler: "because the name is already in use"
- Tritt bei Verzeichnissen wie `AIProductOptimizer_BACKUP_20251102_194355` auf
- ClassFinder lädt Klassen aus mehreren Verzeichnissen

**Lösung**:
1. **Sofortige Behebung**: Backup-Verzeichnisse aus dem Modul-Pfad entfernen
   ```bash
   # Auf dem Server:
   cd GXModules/REDOzone/
   # Backup-Verzeichnisse außerhalb des Modul-Pfads verschieben
   mv AIProductOptimizer_BACKUP_* /backups/module_backups/
   ```

2. **Dauerhafteösung**: Backups niemals im aktiven Modul-Pfad erstellen
   - ❌ FALSCH: `GXModules/REDOzone/AIProductOptimizer_BACKUP_20251102/`
   - ✅ RICHTIG: `/backups/modules/AIProductOptimizer_20251102/`

3. **Prüfung durchführen**: Warnung im Admin-Bereich beachten (falls vorhanden)

**Wichtig**:
- Gambio's ClassFinder scannt alle Verzeichnisse unter `GXModules/`
- Backup-Verzeichnisse mit altem Code (ohne `class_exists()` Checks) verursachen Konflikte
- Verwende für Backups einen separaten Ordner außerhalb von `GXModules/`

#### 2. "OpenAI API Key nicht konfiguriert"
**Lösung**: API Key in der Konfiguration eingeben

#### 3. "Bitte füllen Sie zunächst Produktname und Beschreibung aus"
**Lösung**: Mindestens Produktname und deutsche Beschreibung eingeben

#### 4. "Verbindungsfehler"
**Ursachen**:
- Keine Internetverbindung
- API Key ungültig
- OpenAI Service nicht erreichbar
**Lösung**: Verbindung und API Key überprüfen

#### 5. Restore-Button wird nicht angezeigt
**Ursache**: Kein Backup vorhanden
**Lösung**: Erst KI-Generierung durchführen

### Debug-Modus
Aktiviere die Browser-Console für detaillierte Log-Ausgaben:
```javascript
console.log('Debug - Produktname:', productName);
console.log('API Response:', response);
```

## Performance

### Optimierungen
- **Caching**: CKEditor-Instanzen werden gecacht
- **Lazy Loading**: Restore-Button nur bei Bedarf
- **AJAX**: Keine Seitenneuladung
- **Indizes**: Optimierte Datenbank-Abfragen

### Geschwindigkeit
- Backup-Erstellung: < 1 Sekunde
- KI-Generierung: 5-15 Sekunden (abhängig von API)
- Wiederherstellung: < 1 Sekunde

### API-Kosten
Geschätzte Kosten pro Generierung (bei gpt-4o-mini):
- Input: ~500 Tokens → $0.0008
- Output: ~1000 Tokens → $0.0024
- **Gesamt**: ~$0.003 pro Produkt

## Sicherheit

### Implementierte Maßnahmen
- ✅ SQL-Injection Schutz via `xtc_db_input()`
- ✅ Session-basierte Authentifizierung
- ✅ Admin-Bereich erforderlich
- ✅ AJAX CSRF-Protection
- ✅ Input-Validierung

### Datenschutz
- Keine personenbezogenen Daten werden an OpenAI gesendet
- Nur Produktinformationen werden verarbeitet
- Backups werden nach 30 Tagen gelöscht (optional)
- Keine Tracking-Cookies

### API-Sicherheit
- API Key wird verschlüsselt in der Datenbank gespeichert
- HTTPS-Verbindung zu OpenAI
- Keine Logs von sensiblen Daten

## Wartung

### Backup-Cleanup
Empfohlener Cronjob für automatisches Löschen alter Backups:

**cronjob_cleanup.php**:
```php
<?php
require_once 'Services/BackupService.inc.php';
BackupService::cleanOldBackups();
echo "Cleanup completed: " . date('Y-m-d H:i:s');
?>
```

**Crontab**:
```bash
# Täglich um 3 Uhr
0 3 * * * php /pfad/zu/cronjob_cleanup.php
```

### Updates
- Regelmäßig auf neue Versionen prüfen
- Vor Updates Datenbank-Backup erstellen
- Nach Updates Cache leeren

## Mehrsprachigkeit

### Unterstützte Sprachen
Das Modul generiert automatisch Texte für alle aktiven Sprachen in deinem Shop:
- 🇩🇪 Deutsch
- 🇬🇧 Englisch
- 🇫🇷 Französisch
- 🇪🇸 Spanisch
- Und weitere...

### Sprachzuordnung
Die Zuordnung erfolgt automatisch über:
```javascript
window.AI_OPTIMIZER_LANGUAGE_MAPPING = {
  "de": 2,
  "en": 1,
  "fr": 3,
  "es": 4
}
```

## Erweiterungen

### Geplante Features
- [ ] Bulk-Generierung für mehrere Produkte
- [ ] Backup-Historie mit Diff-Ansicht
- [ ] A/B-Testing von Produkttexten
- [ ] Export/Import von Prompts
- [ ] Integration weiterer AI-Modelle (Claude, Gemini)
- [ ] Automatische SEO-Score-Bewertung

### API für Entwickler
Die Services können einfach erweitert werden:

```php
// Custom OpenAI Service
class CustomOpenAIService extends OpenAIService {
    public function generateWithImages($productName, $imageUrls) {
        // Custom implementation
    }
}
```

## Support & Community

### Dokumentation
- 📖 **Technische Dokumentation**: [BACKUP_MANAGEMENT.md](BACKUP_MANAGEMENT.md)
- 🔧 **Installation Script**: [install.sql](install.sql)
- 🌐 **GitHub Repository**: https://github.com/cmtopchem-glitch/ai-product-optimizer

### Support-Kanäle
- 📧 E-Mail: support@redozone.com
- 🌐 Website: http://www.redozone.com
- 💬 GitHub Issues: Für Bugs und Feature-Requests

### Lizenz
```
AI Product Optimizer für Gambio GX 4.8
Copyright (c) 2024-2025 REDOzone
http://www.redozone.com

Released under the GNU General Public License (Version 2)
http://www.gnu.org/licenses/gpl-2.0.html
```

## Changelog

### Version 1.0.0 (2025-11-03)
#### Neue Features
- ✅ KI-gestützte SEO-Textgenerierung
- ✅ Mehrsprachige Unterstützung
- ✅ Automatische Backup-Verwaltung
- ✅ Ein-Klick Wiederherstellung
- ✅ Integration in Gambio Produktseite
- ✅ Konfigurierbares Prompt-System
- ✅ AJAX-basierte UI ohne Seitenneuladung

#### Technische Details
- OpenAI GPT-4o/4o-mini Integration
- BackupService mit Versionsverwaltung
- Extender Component für nahtlose Integration
- Optimierte Datenbank-Struktur mit Indizes
- Vollständige Dokumentation

#### Bugfixes
- Keine (Initial Release)

---

## Schnellstart-Anleitung

### 1. Installation (5 Minuten)
```bash
# Dateien hochladen nach:
GXModules/REDOzone/AIProductOptimizer/

# Im Admin installieren:
Module → Gambio Module Center → AI Product Optimizer → Installieren
```

### 2. Konfiguration (2 Minuten)
```
1. OpenAI API Key eingeben
2. Modell wählen (gpt-4o-mini empfohlen)
3. Speichern
```

### 3. Erste Verwendung (1 Minute)
```
1. Produkt öffnen
2. Button "SEO-Texte mit KI generieren" klicken
3. Warten (5-15 Sek)
4. Ergebnis prüfen und speichern
```

**Fertig!** 🎉

---

**Viel Erfolg mit AI Product Optimizer!**

Bei Fragen oder Problemen steht dir unser Support gerne zur Verfügung.

*REDOzone - E-Commerce Solutions*
