<?php
/**
 * Custom Breadcrumb Functionality
 * 
 * Overrides the parent theme's breadcrumb function with more robust functionality
 * that handles department pages, form center, directory, and other custom post types.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enhanced breadcrumb function that handles various page types and hierarchies
 */
function citygov_breadcrumbs() {
    // Don't show breadcrumbs on homepage
    if (is_home() || is_front_page()) {
        return;
    }

    $breadcrumbs = array();
    
    // Always start with Home
    $breadcrumbs[] = array(
        'title' => esc_html__('Home', 'citygov'),
        'url' => home_url('/'),
        'current' => false
    );

    // Handle different page types
    if (is_page()) {
        $breadcrumbs = array_merge($breadcrumbs, get_page_breadcrumbs());
    } elseif (is_single()) {
        $breadcrumbs = array_merge($breadcrumbs, get_single_breadcrumbs());
    } elseif (is_category() || is_tag() || is_tax()) {
        $breadcrumbs = array_merge($breadcrumbs, get_taxonomy_breadcrumbs());
    } elseif (is_archive()) {
        $breadcrumbs = array_merge($breadcrumbs, get_archive_breadcrumbs());
    } elseif (is_search()) {
        $breadcrumbs[] = array(
            'title' => esc_html__('Search Results', 'citygov'),
            'url' => '',
            'current' => true
        );
    } elseif (is_404()) {
        $breadcrumbs[] = array(
            'title' => esc_html__('Page Not Found', 'citygov'),
            'url' => '',
            'current' => true
        );
    }

    // Output the breadcrumbs
    output_breadcrumbs($breadcrumbs);
}

/**
 * Get breadcrumbs for pages
 */
function get_page_breadcrumbs() {
    global $post;
    $breadcrumbs = array();
    
    // Check if this is a department page or related to a department
    $department_info = get_department_info($post);
    if ($department_info) {
        $breadcrumbs[] = $department_info;
    }
    
    // Check for special sections like forms or directory
    $section_info = get_section_info($post);
    if ($section_info) {
        $breadcrumbs[] = $section_info;
    }
    
    // Add current page
    $breadcrumbs[] = array(
        'title' => get_the_title($post),
        'url' => '',
        'current' => true
    );
    
    return $breadcrumbs;
}

/**
 * Get breadcrumbs for single posts
 */
function get_single_breadcrumbs() {
    $breadcrumbs = array();
    
    // Handle custom post types
    $post_type = get_post_type();
    
    switch ($post_type) {
        case 'wpm_project':
            $breadcrumbs[] = array(
                'title' => esc_html__('Projects', 'citygov'),
                'url' => get_post_type_archive_link('wpm_project'),
                'current' => false
            );
            break;
            
        case 'event':
            $breadcrumbs[] = array(
                'title' => esc_html__('Events', 'citygov'),
                'url' => get_post_type_archive_link('event'),
                'current' => false
            );
            break;
            
        case 'dlp_document':
            $breadcrumbs[] = array(
                'title' => esc_html__('Documents', 'citygov'),
                'url' => get_post_type_archive_link('dlp_document'),
                'current' => false
            );
            break;
            
        default:
            // For regular posts, add category
            $categories = get_the_category();
            if (!empty($categories)) {
                $breadcrumbs[] = array(
                    'title' => $categories[0]->name,
                    'url' => get_category_link($categories[0]->term_id),
                    'current' => false
                );
            }
            break;
    }
    
    // Add current post
    $breadcrumbs[] = array(
        'title' => get_the_title(),
        'url' => '',
        'current' => true
    );
    
    return $breadcrumbs;
}

/**
 * Get breadcrumbs for taxonomy pages
 */
function get_taxonomy_breadcrumbs() {
    $breadcrumbs = array();
    
    if (is_category()) {
        $category = get_queried_object();
        $breadcrumbs[] = array(
            'title' => esc_html__('Blog', 'citygov'),
            'url' => get_permalink(get_option('page_for_posts')),
            'current' => false
        );
        $breadcrumbs[] = array(
            'title' => $category->name,
            'url' => '',
            'current' => true
        );
    } elseif (is_tag()) {
        $tag = get_queried_object();
        $breadcrumbs[] = array(
            'title' => esc_html__('Blog', 'citygov'),
            'url' => get_permalink(get_option('page_for_posts')),
            'current' => false
        );
        $breadcrumbs[] = array(
            'title' => $tag->name,
            'url' => '',
            'current' => true
        );
    }
    
    return $breadcrumbs;
}

/**
 * Get breadcrumbs for archive pages
 */
function get_archive_breadcrumbs() {
    $breadcrumbs = array();
    
    if (is_post_type_archive()) {
        $post_type_obj = get_post_type_object(get_post_type());
        $breadcrumbs[] = array(
            'title' => $post_type_obj->labels->name,
            'url' => '',
            'current' => true
        );
    } elseif (is_date()) {
        $breadcrumbs[] = array(
            'title' => esc_html__('Blog', 'citygov'),
            'url' => get_permalink(get_option('page_for_posts')),
            'current' => false
        );
        $breadcrumbs[] = array(
            'title' => get_the_archive_title(),
            'url' => '',
            'current' => true
        );
    }
    
    return $breadcrumbs;
}

/**
 * Get department information for a page
 */
function get_department_info($post) {
    // Check if this page is a department homepage
    if (has_term('department-homepage', 'category') || 
        has_term('department', 'category') ||
        get_post_meta($post->ID, '_is_department_homepage', true)) {
        return array(
            'title' => get_the_title($post),
            'url' => get_permalink($post),
            'current' => false
        );
    }
    
    // Check if this page belongs to a department
    $parent_id = $post->post_parent;
    while ($parent_id) {
        $parent = get_post($parent_id);
        if (has_term('department-homepage', 'category', $parent) || 
            has_term('department', 'category', $parent) ||
            get_post_meta($parent->ID, '_is_department_homepage', true)) {
            return array(
                'title' => get_the_title($parent),
                'url' => get_permalink($parent),
                'current' => false
            );
        }
        $parent_id = $parent->post_parent;
    }
    
    return null;
}

/**
 * Get section information (forms, directory, etc.)
 */
function get_section_info($post) {
    $current_url = get_permalink($post);
    $site_url = home_url('/');
    
    // Check if we're in the forms section
    if (strpos($current_url, $site_url . 'forms/') === 0) {
        return array(
            'title' => esc_html__('Forms', 'citygov'),
            'url' => home_url('/forms/'),
            'current' => false
        );
    }
    
    // Check if we're in the directory section
    if (strpos($current_url, $site_url . 'directory/') === 0) {
        return array(
            'title' => esc_html__('Directory', 'citygov'),
            'url' => home_url('/directory/'),
            'current' => false
        );
    }
    
    // Check if we're in the documents section
    if (strpos($current_url, $site_url . 'documents/') === 0) {
        return array(
            'title' => esc_html__('Documents', 'citygov'),
            'url' => home_url('/documents/'),
            'current' => false
        );
    }
    
    return null;
}

/**
 * Output the breadcrumb HTML
 */
function output_breadcrumbs($breadcrumbs) {
    if (empty($breadcrumbs)) {
        return;
    }
    
    echo '<nav class="breadcrumbs" aria-label="' . esc_attr__('Breadcrumb navigation', 'citygov') . '">';
    
    foreach ($breadcrumbs as $index => $crumb) {
        if ($index > 0) {
            echo '<span class="breadcrumb-separator">/</span>';
        }
        
        if ($crumb['current']) {
            echo '<span class="crumb current">' . esc_html($crumb['title']) . '</span>';
        } else {
            echo '<span class="crumb"><a href="' . esc_url($crumb['url']) . '">' . esc_html($crumb['title']) . '</a></span>';
        }
    }
    
    echo '</nav>';
}

// Remove the parent theme's breadcrumb function and replace with our enhanced version
remove_action('wp_head', 'citygov_breadcrumbs');

/**
 * Helper function to display breadcrumbs in templates
 * Usage: <?php display_breadcrumbs(); ?>
 */
function display_breadcrumbs() {
    citygov_breadcrumbs();
} 