<?php
/**
 * ModuleCenterModuleController Loader
 *
 * This file serves as a loader/alias for the actual controller class.
 * It simply includes the main controller to avoid duplicate class declarations
 * when backup directories exist in the module path.
 *
 * DO NOT define classes here - only include the actual controller.
 */

// Include the actual controller class (only define it in one place)
if (!class_exists('AIProductOptimizerModuleCenterModuleController', false)) {
    require_once __DIR__ . '/Admin/Classes/Controllers/AIProductOptimizerModuleCenterModuleController.inc.php';
}
