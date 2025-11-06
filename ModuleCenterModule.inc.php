<?php
/**
 * ModuleCenterModule Loader
 *
 * This file serves as a loader/alias for the actual module class.
 * It simply includes the main module to avoid duplicate class declarations
 * when backup directories exist in the module path.
 *
 * DO NOT define classes here - only include the actual module.
 */

// Include the actual module class (only define it in one place)
if (!class_exists('AIProductOptimizerModuleCenterModule', false)) {
    require_once __DIR__ . '/Admin/Classes/AIProductOptimizerModuleCenterModule.inc.php';
}

// Create an alias for backward compatibility
if (!class_exists('AIProductOptimizer', false) && class_exists('AIProductOptimizerModuleCenterModule', false)) {
    class AIProductOptimizer extends AIProductOptimizerModuleCenterModule
    {
        // This is an alias class for backward compatibility
    }
}
