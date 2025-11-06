<?php
if (!class_exists('AIProductOptimizerAdminEditProductExtenderComponent')) {
class AIProductOptimizerAdminEditProductExtenderComponent extends AIProductOptimizerAdminEditProductExtenderComponent_parent
{
    public function proceed()
    {
        parent::proceed();
        
        if (empty($_GET['pID'])) {
            return false;
        }
        
        $productId = (int)$_GET['pID'];
        
        // Lade aktive Sprachen aus DB
        $languageMapping = [];
        $query = "SELECT languages_id, code FROM languages WHERE status = 1 ORDER BY languages_id";
        $result = xtc_db_query($query);
        
        while ($row = xtc_db_fetch_array($result)) {
            $languageMapping[$row['code']] = (int)$row['languages_id'];
        }
        
        $languageMappingJson = json_encode($languageMapping);
        
        // Prüfe ob Backup existiert
        require_once DIR_FS_CATALOG . 'GXModules/REDOzone/AIProductOptimizer/Services/BackupService.inc.php';
        $hasBackup = BackupService::hasBackup($productId);
        
        $content = '<div class="ai-optimizer-container" style="margin: 15px 0;">';

        // Generieren-Button
        $content .= '<button id="ai-optimize-button" class="btn btn-primary" type="button">';
        $content .= '<i class="fa fa-magic"></i> SEO-Texte mit KI generieren';
        $content .= '</button>';

        // Restore-Button (nur wenn Backup existiert)
        if ($hasBackup) {
            $content .= ' <button id="ai-restore-button" class="btn btn-warning" type="button" onclick="AIProductOptimizer.restoreBackup()" style="margin-left: 10px;">';
            $content .= '<i class="fa fa-undo"></i> Original wiederherstellen';
            $content .= '</button>';
        }

        // Konfigurations-Button
        $content .= ' <a href="admin.php?do=AIProductOptimizerModuleCenterModule" class="btn btn-default" style="margin-left: 10px;">';
        $content .= '<i class="fa fa-cog"></i> Konfiguration';
        $content .= '</a>';

        // Backup-Verwaltung Toggle Button
        if ($hasBackup) {
            $content .= ' <button id="ai-toggle-backups" class="btn btn-info" type="button" style="margin-left: 10px;">';
            $content .= '<i class="fa fa-database"></i> Backups verwalten';
            $content .= '</button>';
        }

        $content .= '<span id="ai-optimizer-status" style="margin-left: 10px;"></span>';
        $content .= '</div>';

        // Backup-Verwaltungsbereich (standardmäßig versteckt)
        if ($hasBackup) {
            $content .= '<div id="ai-backup-management" style="display: none; margin: 15px 0; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 5px;">';
            $content .= '<h4 style="margin-top: 0;"><i class="fa fa-database"></i> Gespeicherte Backups</h4>';
            $content .= '<div id="ai-backup-list">';
            $content .= '<p><i class="fa fa-spinner fa-spin"></i> Lade Backups...</p>';
            $content .= '</div>';
            $content .= '</div>';
        }
        
        // Injiziere Sprach-Mapping als globale JS-Variable
        $content .= '<script>';
        $content .= 'window.AI_OPTIMIZER_LANGUAGE_MAPPING = ' . $languageMappingJson . ';';
        $content .= '</script>';
        
        $content .= '<script src="' . DIR_WS_CATALOG . 'GXModules/REDOzone/AIProductOptimizer/Admin/Javascript/ai_optimizer_v2.js"></script>';
        
        $this->v_output_buffer['top']['aiProductOptimizer'] = array(
            'title' => 'AI Product Optimizer',
            'content' => $content
        );
    }
}
}
