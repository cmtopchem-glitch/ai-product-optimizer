/**
 * JavaScript für AI Product Optimizer - Gambio GX 4.8.0.2
 * Mit Keywords-Support
 */
var AIProductOptimizer = {
    init: function() {
        this.bindEvents();
    },
    
    bindEvents: function() {
        var self = this;
        $('#ai-optimize-button').off('click');
        $('#ai-optimize-button').on('click', function(e) {
            e.preventDefault();
            console.log('Button geklickt!');
            self.generateContent();
        });

        // Backup-Verwaltung Toggle
        $('#ai-toggle-backups').off('click').on('click', function(e) {
            e.preventDefault();
            self.toggleBackupManagement();
        });
    },
    
    generateContent: function() {
        var self = this;
        setTimeout(function() {
            var productName = self.getProductName();
            var originalText = self.getOriginalDescription();
            var productId = self.getProductId();
            
            console.log('Debug - Produktname:', productName);
            console.log('Debug - Beschreibung Länge:', originalText ? originalText.length : 0);
            console.log('Debug - Beschreibung Anfang:', originalText ? originalText.substring(0, 100) : 'leer');
            
            if (!productName || !originalText || originalText.length < 10) {
                self.showError('Bitte füllen Sie zunächst Produktname und Beschreibung (Deutsch) aus.');
                return;
            }
            
            self.showStatus('Generiere optimierte Texte mit Keywords...', 'info');
            $('#ai-optimize-button').prop('disabled', true);
            
            $.ajax({
                url: 'admin.php?do=AIProductOptimizerModuleCenterModule/Generate',
                method: 'POST',
                dataType: 'json',
                data: {
                    product_id: productId,
                    product_name: productName,
                    original_text: originalText,
                    category: self.getCategoryName(),
                    brand: self.getBrandName()
                },
                success: function(response) {
                    console.log('API Response:', response);
                    if (response.success) {
                        self.fillFields(response.data);
                        self.showStatus('Texte und Keywords erfolgreich generiert!', 'success');
                        
                        // Restore-Button hinzufügen falls nicht vorhanden
                        self.addRestoreButtonIfNeeded();
                    } else {
                        self.showError(response.error || 'Ein Fehler ist aufgetreten');
                    }
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', xhr.responseText);
                    self.showError('Verbindungsfehler: ' + error);
                },
                complete: function() {
                    $('#ai-optimize-button').prop('disabled', false);
                }
            });
        }, 100);
    },
    
    fillFields: function(data) {
        // Lade Sprach-Mapping (sollte vom Extender injiziert worden sein)
        var languages = window.AI_OPTIMIZER_LANGUAGE_MAPPING;
        
        if (!languages) {
            console.error('AI_OPTIMIZER_LANGUAGE_MAPPING nicht gefunden! Extender-Problem?');
            alert('Fehler: Sprach-Konfiguration nicht geladen. Bitte Seite neu laden.');
            return;
        }
        
        console.log('Language Mapping:', languages);
        console.log('Generated Data:', data);
        
        for (var lang in data) {
            if (data.hasOwnProperty(lang)) {
                var langId = languages[lang];
                
                if (!langId) {
                    console.warn('Sprache nicht gefunden im Mapping:', lang);
                    continue;
                }
                
                var content = data[lang];
                
                // Produktbeschreibung
                var descFieldName = 'products_description_' + langId;
                if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances[descFieldName]) {
                    CKEDITOR.instances[descFieldName].setData(content.description);
                    console.log('✓ Beschreibung befüllt für', lang, '(ID:', langId + ')');
                }
                
                // Meta-Felder
                $('input[name="products_meta_title[' + langId + ']"]').val(content.meta_title);
                $('textarea[name="products_meta_description[' + langId + ']"]').val(content.meta_description);
                
                // ✅ KEYWORDS (beide sind INPUT-Felder in GX4!)
                var metaKeywordsField = $('input[name="products_meta_keywords[' + langId + ']"]');
                var searchKeywordsField = $('input[name="products_keywords[' + langId + ']"]');
                
                if (metaKeywordsField.length > 0) {
                    metaKeywordsField.val(content.meta_keywords);
                    console.log('✓ Meta Keywords befüllt für', lang, ':', content.meta_keywords);
                } else {
                    console.warn('✗ Meta Keywords Feld nicht gefunden für', lang);
                }
                
                if (searchKeywordsField.length > 0) {
                    searchKeywordsField.val(content.search_keywords);
                    console.log('✓ Shop-Suchworte befüllt für', lang, ':', content.search_keywords);
                } else {
                    console.warn('✗ Shop-Suchworte Feld nicht gefunden für', lang);
                }
            }
        }
    },
    
    getProductId: function() {
        return $('input[name="products_id"]').val() || '';
    },
    
    getProductName: function() {
        return $('input[name="products_name[2]"]').val() || '';
    },
    
    getOriginalDescription: function() {
        var descFieldName = 'products_description_2';
        if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances[descFieldName]) {
            var data = CKEDITOR.instances[descFieldName].getData();
            var temp = $('<div>').html(data).text();
            return temp.trim();
        }
        return $('textarea[name="' + descFieldName + '"]').val() || '';
    },
    
    getCategoryName: function() {
        return $('select[name="categories_id"] option:selected').text() || '';
    },
    
    getBrandName: function() {
        return $('input[name="brand_name"]').val() || '';
    },
    
    showStatus: function(message, type) {
        var iconMap = {'info': 'fa-spinner fa-spin', 'success': 'fa-check', 'error': 'fa-exclamation-triangle'};
        var colorMap = {'info': '#0066cc', 'success': '#28a745', 'error': '#dc3545'};
        
        $('#ai-optimizer-status').html(
            '<i class="fa ' + iconMap[type] + '"></i> ' +
            '<span style="color: ' + colorMap[type] + '">' + message + '</span>'
        );
        
        if (type === 'success' || type === 'error') {
            setTimeout(function() {
                $('#ai-optimizer-status').fadeOut(function() {
                    $(this).html('').show();
                });
            }, 5000);
        }
    },
    
    showError: function(message) {
        this.showStatus(message, 'error');
        alert('❌ Fehler: ' + message);
    },
    
    addRestoreButtonIfNeeded: function() {
        var self = this;
        
        // Button existiert bereits?
        if ($('#ai-restore-button').length > 0) {
            console.log('Restore-Button existiert bereits');
            return;
        }
        
        var productId = self.getProductId();
        if (!productId) {
            console.log('Keine Produkt-ID gefunden');
            return;
        }
        
        // Prüfe ob Backup existiert via AJAX
        $.ajax({
            url: 'admin.php?do=AIProductOptimizerModuleCenterModule/CheckBackup',
            method: 'GET',
            dataType: 'json',
            data: {
                product_id: productId
            },
            success: function(response) {
                if (response.hasBackup) {
                    console.log('✅ Backup vorhanden, füge Restore-Button hinzu');
                    self.insertRestoreButton();
                } else {
                    console.log('ℹ️ Kein Backup vorhanden');
                }
            },
            error: function() {
                console.log('⚠️ Konnte Backup-Status nicht prüfen');
            }
        });
    },
    
    insertRestoreButton: function() {
        // Prüfe nochmal ob Button bereits existiert
        if ($('#ai-restore-button').length > 0) {
            return;
        }
        
        var restoreHtml = '<div id="ai-restore-container" style="margin: 15px 0; padding: 15px; background: #fff3cd; border: 2px solid #ffc107; border-radius: 5px;">' +
            '<button type="button" id="ai-restore-button" class="btn btn-warning" style="font-weight: bold;">' +
            '<i class="fa fa-undo"></i> Original wiederherstellen' +
            '</button>' +
            '<span id="ai-restore-status" style="margin-left: 15px;"></span>' +
            '</div>';
        
        // Füge Button nach dem Optimize-Button ein
        $('#ai-optimize-button').closest('div').after(restoreHtml);
        
        // Bind Event
        var self = this;
        $('#ai-restore-button').on('click', function(e) {
            e.preventDefault();
            self.restoreBackup();
        });
        
        console.log('✅ Restore-Button dynamisch hinzugefügt');
    },
    
    restoreBackup: function() {
        var self = this;

        if (!confirm("Möchten Sie wirklich die Original-Texte wiederherstellen? Die aktuellen KI-generierten Texte werden überschrieben.")) {
            return;
        }

        var productId = self.getProductId();
        if (!productId) {
            self.showError("Produkt-ID nicht gefunden");
            return;
        }

        self.showStatus("Stelle Original-Texte wieder her...", "info");

        $.ajax({
            url: "admin.php?do=AIProductOptimizerModuleCenterModule/Restore",
            method: "POST",
            dataType: "json",
            data: {
                product_id: productId
            },
            success: function(response) {
                if (response.success) {
                    self.showStatus(response.message + " - Seite wird neu geladen...", "success");
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    self.showError(response.error || "Fehler beim Wiederherstellen");
                }
            },
            error: function(xhr, status, error) {
                self.showError("Verbindungsfehler: " + error);
            }
        });
    },

    toggleBackupManagement: function() {
        var $backupManagement = $('#ai-backup-management');
        if ($backupManagement.is(':visible')) {
            $backupManagement.slideUp();
        } else {
            $backupManagement.slideDown();
            this.loadBackups();
        }
    },

    loadBackups: function() {
        var self = this;
        var productId = self.getProductId();

        if (!productId) {
            $('#ai-backup-list').html('<p class="text-danger">Produkt-ID nicht gefunden</p>');
            return;
        }

        $.ajax({
            url: 'admin.php?do=AIProductOptimizerModuleCenterModule/GetBackups',
            method: 'GET',
            dataType: 'json',
            data: {
                product_id: productId
            },
            success: function(response) {
                if (response.success && response.backups) {
                    self.renderBackupList(response.backups);
                } else {
                    $('#ai-backup-list').html('<p class="text-danger">Fehler beim Laden der Backups</p>');
                }
            },
            error: function(xhr, status, error) {
                $('#ai-backup-list').html('<p class="text-danger">Verbindungsfehler: ' + error + '</p>');
            }
        });
    },

    renderBackupList: function(backups) {
        var self = this;
        var html = '';

        if (backups.length === 0) {
            html = '<p class="text-muted">Keine Backups vorhanden</p>';
        } else {
            html = '<table class="table table-striped table-hover" style="margin-bottom: 0;">';
            html += '<thead>';
            html += '<tr>';
            html += '<th>Speicherdatum</th>';
            html += '<th>Status</th>';
            html += '<th>Sprachen</th>';
            html += '<th style="text-align: right;">Aktionen</th>';
            html += '</tr>';
            html += '</thead>';
            html += '<tbody>';

            backups.forEach(function(backup) {
                var date = new Date(backup.backup_date.replace(' ', 'T'));
                var formattedDate = self.formatDate(date);
                var statusBadge = backup.restored == 1
                    ? '<span class="label label-default">Wiederhergestellt</span>'
                    : '<span class="label label-success">Verfügbar</span>';

                html += '<tr>';
                html += '<td>' + formattedDate + '</td>';
                html += '<td>' + statusBadge + '</td>';
                html += '<td>' + backup.language_count + '</td>';
                html += '<td style="text-align: right;">';

                // Wiederherstellen-Button (nur wenn nicht bereits wiederhergestellt)
                if (backup.restored == 0) {
                    html += '<button class="btn btn-sm btn-warning" onclick="AIProductOptimizer.restoreSpecificBackup(' + backup.backup_id + ')" style="margin-right: 5px;">';
                    html += '<i class="fa fa-undo"></i> Wiederherstellen';
                    html += '</button>';
                }

                // Löschen-Button
                html += '<button class="btn btn-sm btn-danger" onclick="AIProductOptimizer.deleteBackup(' + backup.backup_id + ')">';
                html += '<i class="fa fa-trash"></i> Löschen';
                html += '</button>';

                html += '</td>';
                html += '</tr>';
            });

            html += '</tbody>';
            html += '</table>';
        }

        $('#ai-backup-list').html(html);
    },

    formatDate: function(date) {
        var day = ('0' + date.getDate()).slice(-2);
        var month = ('0' + (date.getMonth() + 1)).slice(-2);
        var year = date.getFullYear();
        var hours = ('0' + date.getHours()).slice(-2);
        var minutes = ('0' + date.getMinutes()).slice(-2);

        return day + '.' + month + '.' + year + ' ' + hours + ':' + minutes + ' Uhr';
    },

    deleteBackup: function(backupId) {
        var self = this;

        if (!confirm('Möchten Sie dieses Backup wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.')) {
            return;
        }

        var productId = self.getProductId();
        if (!productId) {
            self.showError('Produkt-ID nicht gefunden');
            return;
        }

        $.ajax({
            url: 'admin.php?do=AIProductOptimizerModuleCenterModule/DeleteBackup',
            method: 'POST',
            dataType: 'json',
            data: {
                backup_id: backupId,
                product_id: productId
            },
            success: function(response) {
                if (response.success) {
                    self.showStatus(response.message, 'success');
                    self.loadBackups(); // Liste neu laden
                } else {
                    self.showError(response.error || 'Fehler beim Löschen');
                }
            },
            error: function(xhr, status, error) {
                self.showError('Verbindungsfehler: ' + error);
            }
        });
    },

    restoreSpecificBackup: function(backupId) {
        var self = this;

        if (!confirm('Möchten Sie dieses Backup wirklich wiederherstellen? Die aktuellen Produkttexte werden überschrieben.')) {
            return;
        }

        var productId = self.getProductId();
        if (!productId) {
            self.showError('Produkt-ID nicht gefunden');
            return;
        }

        self.showStatus('Stelle Backup wieder her...', 'info');

        $.ajax({
            url: 'admin.php?do=AIProductOptimizerModuleCenterModule/RestoreSpecificBackup',
            method: 'POST',
            dataType: 'json',
            data: {
                backup_id: backupId,
                product_id: productId
            },
            success: function(response) {
                if (response.success) {
                    self.showStatus(response.message + ' - Seite wird neu geladen...', 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    self.showError(response.error || 'Fehler beim Wiederherstellen');
                }
            },
            error: function(xhr, status, error) {
                self.showError('Verbindungsfehler: ' + error);
            }
        });
    }
};

// Auto-Init wenn jQuery bereit ist
(function() {
    function tryInit() {
        if (typeof $ !== 'undefined' && typeof AIProductOptimizer !== 'undefined' && $('#ai-optimize-button').length > 0) {
            AIProductOptimizer.init();
            console.log('AIProductOptimizer auto-initialized (GX4 4.8.0.2 mit Keywords)');
        } else {
            setTimeout(tryInit, 100);
        }
    }
    
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(tryInit, 100);
    } else {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(tryInit, 100);
        });
    }
})();
