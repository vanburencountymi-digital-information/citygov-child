<?php
/**
 * CityGov Child Theme Functions
 * 
 * This file serves as the main loader for all theme functionality.
 * Individual modules are organized in the inc/ directory for better maintainability.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Child theme versioning
define('VBC_CHILD_THEME_VERSION', wp_get_theme()->get('Version'));

// Auto-load all files in inc/
foreach (glob(__DIR__ . '/inc/*.php') as $file) {
    require_once $file;
}


