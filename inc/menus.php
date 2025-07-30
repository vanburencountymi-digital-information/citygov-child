<?php
/**
 * Menus Module
 * 
 * Handles all navigation menu registrations for the child theme.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register navigation menus
 */
function my_child_theme_menus() {
    register_nav_menus(array(
        'courts_quick_links' => 'Courts Quick Links',
        'sheriff_quick_links' => 'Sheriff Quick Links',
    ));
}
add_action('after_setup_theme', 'my_child_theme_menus'); 