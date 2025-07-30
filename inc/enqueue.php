<?php
/**
 * Enqueue Module
 * 
 * Handles all script and style registrations for the child theme.
 * Uses a configuration array approach for better organization.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register all theme assets
 */
function vbc_register_assets() {
    // Define all assets in a configuration array
    $assets = [
        // Scripts
        'vbc-fix-swiper-conflict' => [
            'type' => 'script',
            'src' => '/js/fix-swiper-conflict.js',
            'deps' => [],
            'ver' => filemtime(get_stylesheet_directory() . '/js/fix-swiper-conflict.js'),
            'footer' => true,
            'condition' => 'is_front_page'
        ],
        'pdf-replace-modal' => [
            'type' => 'script',
            'src' => '/js/pdf-replace-modal.js',
            'deps' => [],
            'ver' => filemtime(get_stylesheet_directory() . '/js/pdf-replace-modal.js'),
            'footer' => true,
            'condition' => function() {
                return is_singular('dlp_document') && current_user_can('edit_posts');
            }
        ],
        'podcast-player-js' => [
            'type' => 'script',
            'src' => '/js/podcast-player.js',
            'deps' => [],
            'ver' => filemtime(get_stylesheet_directory() . '/js/podcast-player.js'),
            'footer' => true
        ],
        'sheriff-autolink' => [
            'type' => 'script',
            'src' => '/js/auto-detect-phone-and-email.js',
            'deps' => [],
            'ver' => '1.0.0',
            'footer' => true
        ],
        'external-link-warning' => [
            'type' => 'script',
            'src' => '/js/external-link-warning.js',
            'deps' => [],
            'ver' => null,
            'footer' => true
        ],
        'auto-anchor-offset' => [
            'type' => 'script',
            'src' => '/js/auto-anchor-offset.js',
            'deps' => [],
            'ver' => null,
            'footer' => true
        ],
        'department-menu' => [
            'type' => 'script',
            'src' => '/js/department-menu.js',
            'deps' => [],
            'ver' => filemtime(get_stylesheet_directory() . '/js/department-menu.js'),
            'footer' => true
        ],
        'pdf-loader-script' => [
            'type' => 'script',
            'src' => '/js/pdf-loader.js',
            'deps' => [],
            'ver' => null,
            'footer' => true,
            'condition' => 'is_singular_dlp_document'
        ],
        
        // Styles
        'citygov-style' => [
            'type' => 'style',
            'src' => get_template_directory_uri() . '/style.css',
            'deps' => [],
            'ver' => wp_get_theme()->get('Version')
        ],
        'citygov-child-style' => [
            'type' => 'style',
            'src' => get_stylesheet_uri(),
            'deps' => ['citygov-style'],
            'ver' => wp_get_theme()->get('Version')
        ],
        'pagelist-accordion-styles' => [
            'type' => 'style',
            'src' => '/css/pagelist-accordion.css',
            'deps' => [],
            'ver' => wp_get_theme()->get('Version')
        ]
    ];
    
    // Process each asset
    foreach ($assets as $handle => $args) {
        // Check conditional loading
        if (isset($args['condition'])) {
            $condition = $args['condition'];
            if (is_string($condition)) {
                // Handle string-based conditions
                switch ($condition) {
                    case 'is_front_page':
                        if (!is_front_page()) continue 2;
                        break;
                    case 'is_singular_dlp_document':
                        if (!is_singular('dlp_document')) continue 2;
                        break;
                }
            } elseif (is_callable($condition)) {
                // Handle callable conditions
                if (!call_user_func($condition)) {
                    continue;
                }
            }
        }
        
        // Enqueue based on type
        if ($args['type'] === 'script') {
            wp_enqueue_script(
                $handle,
                get_stylesheet_directory_uri() . $args['src'],
                $args['deps'],
                $args['ver'],
                $args['footer']
            );
            
            // Localize script if needed
            if ($handle === 'pdf-replace-modal') {
                wp_localize_script('pdf-replace-modal', 'ajaxurl', admin_url('admin-ajax.php'));
            }
        } else {
            wp_enqueue_style(
                $handle,
                $args['src'],
                $args['deps'],
                $args['ver']
            );
        }
    }
}
add_action('wp_enqueue_scripts', 'vbc_register_assets');

/**
 * Add inline styles for subpages accordion
 */
function add_subpages_accordion_styles() {
    $css = '
    /* Additional styles for [subpages] shortcode with accordion */
    .subpages-page-list .subpage-item.has-children {
        list-style: none;
        margin-bottom: 8px;
    }
    
    .subpages-page-list .subpage-item {
        position: relative;
    }
    
    .subpages-page-list .page-title-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
    }
    
    .subpages-page-list .dropdown-indicator {
        width: 20px;
        height: 20px;
        position: relative;
    }
    
    .subpages-page-list .dropdown-indicator:before,
    .subpages-page-list .dropdown-indicator:after {
        content: \'\';
        position: absolute;
        background-color: #333;
        transition: all 0.3s ease;
    }
    
    .subpages-page-list .dropdown-indicator:before {
        /* Vertical line of the + */
        width: 2px;
        height: 12px;
        top: 4px;
        left: 9px;
    }
    
    .subpages-page-list .dropdown-indicator:after {
        /* Horizontal line of the + */
        width: 12px;
        height: 2px;
        top: 9px;
        left: 4px;
    }
    
    /* When expanded, hide the vertical line to show only - */
    .subpages-page-list .subpage-item.expanded .dropdown-indicator:before {
        opacity: 0;
    }
    
    .subpages-page-list .subpages-accordion {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
        padding-left: 20px;
    }
    
    .subpages-page-list .subpage-item.expanded .subpages-accordion {
        max-height: 1000px;
    }
    
    .subpages-page-list .nested-subpage-item {
        margin: 5px 0;
    }
    ';
    
    wp_add_inline_style('pagelist-accordion-styles', $css);
}
add_action('wp_enqueue_scripts', 'add_subpages_accordion_styles', 12);

/**
 * Add department menu notice styles
 */
function add_department_menu_notice_styles() {
    $css = '
    .department-menu-notice {
        background: #fff;
        border-left: 4px solid #0073aa;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
        margin: 5px 0 15px;
        padding: 1px 12px;
    }
    
    .department-menu-notice .notice-title {
        font-weight: 600;
        margin: 8px 0 4px;
    }
    
    .department-menu-notice .missing-pages-list {
        margin: 8px 0;
        padding-left: 20px;
    }
    
    .department-menu-notice .missing-pages-list li {
        margin: 2px 0;
    }
    
    .department-menu-notice .add-missing-button {
        margin: 8px 0;
    }
    ';
    
    wp_add_inline_style('wp-admin', $css);
}
add_action('admin_enqueue_scripts', 'add_department_menu_notice_styles');

/**
 * Enqueue FileBird migration styles
 */
function enqueue_filebird_migration_styles($hook) {
    if ($hook !== 'tools_page_filebird-migration-tool' && $hook !== 'tools_page_filebird-rename-tool') {
        return;
    }
    
    wp_enqueue_style(
        'filebird-migration-styles',
        get_stylesheet_directory_uri() . '/css/filebird-migration.css',
        array(),
        wp_get_theme()->get('Version')
    );
}
add_action('admin_enqueue_scripts', 'enqueue_filebird_migration_styles'); 