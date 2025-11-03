<?php

require_once __DIR__ . '/Admin/Classes/AIProductOptimizerAjaxHandler.inc.php';

class AIProductOptimizerModuleCenterModuleController extends AbstractModuleCenterModuleController
{
    public function proceed()
    {
        parent::proceed();
        
        // CSS laden
        $this->assets->add_style('aiproductoptimizer.css', 
            'GXModules/REDOzone/AIProductOptimizer/Admin/Styles/aiproductoptimizer.css');
        
        // JavaScript laden
        $this->assets->add_script('ai_optimizer_v2.js',
            'GXModules/REDOzone/AIProductOptimizer/Admin/Javascript/ai_optimizer_v2.js');
    }
    
    public function actionDefault()
    {
        $this->contentView->set_template_dir(DIR_FS_CATALOG . 'GXModules/REDOzone/AIProductOptimizer/Admin/Html/');
        $this->contentView->set_content_template('settings.html');
        
        // API Key aus Datenbank holen
        $api_key = gm_get_conf('AIPRODUCTOPTIMIZER_API_KEY');
        $this->contentView->set_data('API_KEY', $api_key);
        
        return $this->contentView;
    }
}
