# Backup-Verwaltung - AI Product Optimizer

## Übersicht

Das AI Product Optimizer Modul für Gambio GX 4.8 verfügt über ein vollständiges Backup-Management-System, das automatisch die ursprünglichen Produkttexte sichert, bevor KI-generierte Inhalte eingefügt werden.

## Funktionen

### 1. Automatische Backup-Erstellung
- **Zeitpunkt**: Wird automatisch ausgelöst, wenn der "SEO-Texte mit KI generieren" Button verwendet wird
- **Umfang**: Sichert alle relevanten Produktdaten für alle aktiven Sprachen:
  - Produktbeschreibung (`products_description`)
  - Meta-Titel (`products_meta_title`)
  - Meta-Description (`products_meta_description`)
  - Meta-Keywords (`products_meta_keywords`)
  - Shop-Suchworte (`products_keywords`)
- **Speicherort**: Datenbanktabelle `rz_ai_optimizer_backup`

### 2. Wiederherstellung von Backups
- **UI-Integration**: "Original wiederherstellen" Button erscheint automatisch auf der Produktseite, wenn ein Backup vorhanden ist
- **Funktionsweise**: Stellt alle gesicherten Texte für alle Sprachen wieder her
- **Sicherheit**: Bestätigungsdialog vor dem Wiederherstellen
- **Automatisches Reload**: Seite wird nach erfolgreicher Wiederherstellung neu geladen

### 3. Backup-Prüfung
- **Dynamische Anzeige**: System prüft automatisch, ob ein Backup existiert
- **AJAX-basiert**: Keine Seitenneuladung erforderlich
- **Statusanzeige**: Visuelles Feedback während des Prozesses

## Technische Architektur

### Backend-Komponenten

#### BackupService.inc.php
Zentrale Service-Klasse für alle Backup-Operationen:

```php
BackupService::createBackup($productId)    // Erstellt ein Backup
BackupService::restoreBackup($productId)   // Stellt ein Backup wieder her
BackupService::hasBackup($productId)       // Prüft ob Backup existiert
BackupService::cleanOldBackups()           // Löscht alte Backups (>30 Tage)
```

**Speicherort**: `/Services/BackupService.inc.php`

#### Controller-Actions
Drei AJAX-Endpunkte im `AIProductOptimizerModuleCenterModuleController`:

1. **actionGenerate()** (Zeile 174-230)
   - Erstellt automatisch Backup vor KI-Generierung
   - Aufgerufen bei: KI-Textgenerierung

2. **actionRestore()** (Zeile 265-293)
   - Stellt Backup wieder her
   - Endpoint: `admin.php?do=AIProductOptimizerModuleCenterModule/Restore`

3. **actionCheckBackup()** (Zeile 295-321)
   - Prüft ob Backup existiert
   - Endpoint: `admin.php?do=AIProductOptimizerModuleCenterModule/CheckBackup`

**Speicherort**: `/Admin/Classes/Controllers/AIProductOptimizerModuleCenterModuleController.inc.php`

### Frontend-Komponenten

#### JavaScript
Die Datei `ai_optimizer_v2.js` enthält die clientseitige Logik:

**Funktionen**:
- `addRestoreButtonIfNeeded()` (Zeile 178-213): Prüft und fügt Restore-Button hinzu
- `insertRestoreButton()` (Zeile 215-239): Fügt Button in DOM ein
- `restoreBackup()` (Zeile 241-277): Führt Wiederherstellung durch

**AJAX-Calls**:
```javascript
// Prüfe ob Backup existiert
$.ajax({
    url: 'admin.php?do=AIProductOptimizerModuleCenterModule/CheckBackup',
    data: { product_id: productId }
})

// Stelle Backup wieder her
$.ajax({
    url: "admin.php?do=AIProductOptimizerModuleCenterModule/Restore",
    data: { product_id: productId }
})
```

**Speicherort**: `/Admin/Javascript/ai_optimizer_v2.js`

#### UI-Integration
Der `AIProductOptimizerAdminEditProductExtenderComponent` integriert die Funktionalität in die Produktseite:

- Prüft beim Laden der Produktseite ob Backup existiert (Zeile 26-27)
- Zeigt "Original wiederherstellen" Button wenn Backup vorhanden (Zeile 36-41)
- Injiziert Sprach-Mapping für JavaScript (Zeile 51-54)

**Speicherort**: `/Admin/Overloads/AdminEditProductExtenderComponent/AIProductOptimizerAdminEditProductExtenderComponent.inc.php`

## Datenbankstruktur

### Tabelle: rz_ai_optimizer_backup

```sql
CREATE TABLE `rz_ai_optimizer_backup` (
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
  KEY `idx_backup_date` (`backup_date`),
  KEY `idx_product_restored` (`products_id`, `restored`)
)
```

**Felder**:
- `backup_id`: Eindeutige Backup-ID (Auto-Increment)
- `products_id`: Referenz auf Produkt
- `languages_id`: Referenz auf Sprache
- `products_description`: Gesicherte Produktbeschreibung
- `products_meta_title`: Gesicherter Meta-Titel
- `products_meta_description`: Gesicherte Meta-Description
- `products_meta_keywords`: Gesicherte Meta-Keywords
- `products_keywords`: Gesicherte Shop-Suchworte
- `backup_date`: Zeitpunkt der Backup-Erstellung
- `restored`: Flag (0 = aktiv, 1 = bereits wiederhergestellt)

**Indizes**:
- Primärschlüssel auf `backup_id`
- Index auf `products_id` für schnelle Produkt-Suche
- Index auf `backup_date` für Datums-Abfragen
- Kombinierter Index auf `products_id` und `restored` für optimale Abfrage-Performance

## Installation

### Automatische Installation
Die Backup-Tabelle wird automatisch beim Installieren des Moduls über die Gambio Admin-Oberfläche erstellt:

1. Navigate zu: **Module** → **Gambio Module Center**
2. Suche nach "AI Product Optimizer"
3. Klicke auf **Installieren**
4. Die Methode `install()` in `AIProductOptimizerModuleCenterModule.inc.php` erstellt die Tabelle automatisch

### Manuelle Installation
Falls eine manuelle Installation erforderlich ist:

```bash
mysql -u [username] -p [database] < install.sql
```

Die `install.sql` Datei befindet sich im Root-Verzeichnis des Moduls.

## Workflow

### 1. KI-Textgenerierung mit Backup
```
Benutzer klickt "SEO-Texte mit KI generieren"
    ↓
JavaScript: generateContent()
    ↓
AJAX zu: actionGenerate()
    ↓
BackupService::createBackup($productId)
    → Liest aktuelle Produktdaten aus DB
    → Speichert in rz_ai_optimizer_backup
    → Für jede aktive Sprache
    ↓
OpenAI API Call
    ↓
Felder werden befüllt
    ↓
Restore-Button wird hinzugefügt (falls noch nicht vorhanden)
```

### 2. Wiederherstellung
```
Benutzer klickt "Original wiederherstellen"
    ↓
JavaScript: restoreBackup()
    ↓
Bestätigungsdialog
    ↓
AJAX zu: actionRestore()
    ↓
BackupService::restoreBackup($productId)
    → Liest neueste nicht-wiederhergestellte Backups
    → Updated products_description Tabelle
    → Markiert Backups als restored
    ↓
Seite wird neu geladen
```

### 3. Backup-Check beim Seitenladen
```
Produktseite wird geladen
    ↓
Extender Component: proceed()
    ↓
BackupService::hasBackup($productId)
    ↓
Falls true: Restore-Button wird direkt angezeigt
Falls false: Nur Generieren-Button
```

## Wartung

### Alte Backups löschen
Um Speicherplatz zu sparen, können alte Backups (>30 Tage) gelöscht werden:

```php
require_once 'Services/BackupService.inc.php';
BackupService::cleanOldBackups();
```

**Empfehlung**: Einrichten als Cronjob:
```bash
# Täglich um 3 Uhr morgens
0 3 * * * php /pfad/zum/cronjob_cleanup.php
```

**cronjob_cleanup.php**:
```php
<?php
require_once 'Services/BackupService.inc.php';
BackupService::cleanOldBackups();
echo "Old backups cleaned: " . date('Y-m-d H:i:s') . "\n";
?>
```

### Monitoring
Nützliche SQL-Abfragen für Monitoring:

```sql
-- Anzahl Backups pro Produkt
SELECT products_id, COUNT(*) as backup_count
FROM rz_ai_optimizer_backup
WHERE restored = 0
GROUP BY products_id;

-- Größte Backups
SELECT backup_id, products_id, LENGTH(products_description) as size_bytes
FROM rz_ai_optimizer_backup
ORDER BY size_bytes DESC
LIMIT 10;

-- Alte Backups finden
SELECT COUNT(*) as old_backups
FROM rz_ai_optimizer_backup
WHERE backup_date < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

## Best Practices

### Für Entwickler
1. **Immer Backup erstellen**: Vor jeder automatischen Textänderung `BackupService::createBackup()` aufrufen
2. **Error Handling**: Try-Catch Blöcke um alle Backup-Operationen
3. **Transaktionen**: Bei kritischen Operationen DB-Transaktionen verwenden
4. **Logging**: Wichtige Backup-Operationen loggen

### Für Administratoren
1. **Regelmäßige Cleanup**: Cronjob für alte Backups einrichten
2. **Backup-Monitoring**: Regelmäßig Tabellengröße überprüfen
3. **Datenbank-Backup**: Zusätzliches DB-Backup vor Updates
4. **Testing**: Restore-Funktion regelmäßig testen

## Sicherheit

### Implementierte Schutzmaßnahmen
- **SQL-Injection Schutz**: `xtc_db_input()` für alle User-Inputs
- **Bestätigung**: Confirmation-Dialog vor Wiederherstellung
- **Session-basiert**: Nur Admin-Benutzer können Backups verwalten
- **Validierung**: Produkt-ID und Sprach-ID werden validiert

### Datenschutz
- Backups enthalten nur Produkttexte, keine personenbezogenen Daten
- Alte Backups werden nach 30 Tagen automatisch gelöscht (wenn Cronjob eingerichtet)
- Nur für Admin-Bereich zugänglich

## Fehlerbehandlung

### Häufige Fehler und Lösungen

#### 1. "Kein Backup vorhanden"
**Ursache**: Produkt wurde noch nie mit KI bearbeitet
**Lösung**: Erst KI-Generierung durchführen

#### 2. Restore-Button erscheint nicht
**Ursache**: JavaScript nicht geladen oder AJAX-Fehler
**Lösung**: Browser-Console prüfen, JavaScript-Datei überprüfen

#### 3. "Produkt-ID nicht gefunden"
**Ursache**: Produktseite wurde nicht korrekt geladen
**Lösung**: Seite neu laden, URL Parameter prüfen

#### 4. Tabelle existiert nicht
**Ursache**: Modul wurde nicht korrekt installiert
**Lösung**: `install.sql` manuell ausführen oder Modul neu installieren

## Performance

### Optimierungen
- **Indizes**: Mehrere DB-Indizes für schnelle Abfragen
- **Lazy Loading**: Restore-Button wird nur bei Bedarf hinzugefügt
- **AJAX**: Keine Seitenneuladung beim Backup-Check
- **Batch Operations**: Alle Sprachen werden in einem Durchgang verarbeitet

### Empfohlene Limits
- Max. Backup-Größe: 5 MB pro Eintrag
- Max. Backup-Alter: 30 Tage
- Empfohlene Cleanup-Frequenz: Täglich

## Erweiterungsmöglichkeiten

### Geplante Features
- [ ] Mehrere Backup-Versionen pro Produkt
- [ ] Backup-Historie mit Diff-Ansicht
- [ ] Export/Import von Backups
- [ ] Bulk-Restore für mehrere Produkte
- [ ] Backup-Benachrichtigungen per E-Mail

### API für Entwickler
Die Backup-Funktionalität kann einfach erweitert werden:

```php
// Custom Backup mit zusätzlichen Daten
class ExtendedBackupService extends BackupService {
    public static function createBackupWithImages($productId) {
        // Implementierung
    }
}
```

## Support

### Dokumentation
- GitHub Repository: https://github.com/cmtopchem-glitch/ai-product-optimizer
- Installation Guide: README.txt
- API Dokumentation: docs/API.md

### Kontakt
- Website: http://www.redozone.com
- E-Mail: support@redozone.com
- Lizenz: GNU General Public License (Version 2)

## Changelog

### Version 1.0 (2025-11-03)
- ✅ Initiale Backup-Verwaltung implementiert
- ✅ Automatische Backup-Erstellung
- ✅ Restore-Funktionalität
- ✅ UI-Integration in Produktseite
- ✅ Cleanup-Funktion für alte Backups
- ✅ Vollständige Dokumentation

---

**Copyright (c) 2024-2025 REDOzone**
Released under the GNU General Public License (Version 2)
