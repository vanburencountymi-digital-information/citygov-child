<?php
/**
 * Widgets Module
 * 
 * Handles all widget area registrations for the child theme.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register widget areas
 */
function my_theme_widgets_init() {
    // Department Homepage Sidebar
    register_sidebar(array(
        'name'          => 'Department Homepage Sidebar',
        'id'            => 'department-homepage',
        'description'   => 'Widgets in this area will be shown on the department homepage template.',
        'before_widget' => '<div id="%1$s" class="sidebar_item">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="widgettitle">',
        'after_title'   => '</h2>',
    ));
    
    // Department Blog Single Post Sticky Sidebar
    register_sidebar(array(
        'name'          => 'Department Blog Single Post Sticky Sidebar',
        'id'            => 'department-blog-single-post-sticky-sidebar',
        'description'   => 'Widgets in this area will appear in the sticky sidebar for single posts.',
        'before_widget' => '<div id="%1$s" class="sidebar_item">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="widgettitle">',
        'after_title'   => '</h2>',
    ));
}
add_action('widgets_init', 'my_theme_widgets_init'); 