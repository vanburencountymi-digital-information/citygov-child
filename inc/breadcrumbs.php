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
    
    // Check for special sections like forms or directory first
    $section_info = get_section_info($post);
    if ($section_info) {
        $breadcrumbs[] = $section_info;
    }
    
    // Check for the next level after the main section (super department or equivalent)
    $next_level_info = get_next_level_after_section($post);
    if ($next_level_info) {
        $breadcrumbs[] = $next_level_info;
    }
    
    // Determine if we're in the government section to avoid duplicate crumbs there
    $site_url = home_url('/');
    $current_page_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $in_government = (strpos($current_page_url, $site_url . 'government/') === 0);
    
    // Check if this is a department page or related to a department
    // For department pages, include the department breadcrumb even if a super department crumb exists
    // But suppress department breadcrumb when in the government section to prevent duplicates
    $department_info = get_department_info($post);
    if ($department_info && !$in_government) {
        // Only add department info if we're not already on the department homepage
        if ($department_info['url'] !== get_permalink($post)) {
            $breadcrumbs[] = $department_info;
        }
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
    
    // Check for section info first (for forms, directory, etc.)
    $section_info = get_section_info();
    if ($section_info) {
        $breadcrumbs[] = $section_info;
    }
    
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
    // Use the existing helper function to find the department root page
    $department_root_id = get_department_root_page_id($post->ID);
    
    if ($department_root_id) {
        $department_page = get_post($department_root_id);
        if ($department_page) {
            return array(
                'title' => get_the_title($department_page),
                'url' => get_permalink($department_page),
                'current' => false
            );
        }
    }
    
    return null;
}

/**
 * Get the next level after the main section (super department or equivalent)
 */
function get_next_level_after_section($post = null) {
    if (!$post) {
        global $post;
    }
    
    $current_url = get_permalink($post);
    $site_url = home_url('/');
    
    // Get the current page URL for more accurate detection
    $current_page_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    
    // Check if we're in the departments section
    if (strpos($current_page_url, $site_url . 'departments/') === 0) {
        $path = str_replace($site_url . 'departments/', '', $current_page_url);
        $path_parts = explode('/', $path);
        
        if (!empty($path_parts[0])) {
            $super_dept_slug = $path_parts[0];
            
            // Map super department slugs to display names
            $super_dept_names = array(
                'departments-offices' => 'Administration',
                'sheriff' => 'Sheriff\'s Office',
                'county-courts' => 'County Courts'
            );
            
            $super_dept_name = isset($super_dept_names[$super_dept_slug]) ? $super_dept_names[$super_dept_slug] : ucwords(str_replace('-', ' ', $super_dept_slug));
            
            return array(
                'title' => $super_dept_name,
                'url' => home_url('/departments/' . $super_dept_slug . '/'),
                'current' => false
            );
        }
    }
    
    // Check if we're in the government section
    if (strpos($current_page_url, $site_url . 'government/') === 0) {
        $path = str_replace($site_url . 'government/', '', $current_page_url);
        $path_parts = explode('/', $path);
        
        if (!empty($path_parts[0])) {
            $next_level_slug = $path_parts[0];
            $next_level_name = ucwords(str_replace('-', ' ', $next_level_slug));
            
            return array(
                'title' => $next_level_name,
                'url' => home_url('/government/' . $next_level_slug . '/'),
                'current' => false
            );
        }
    }

    // Check if we're in the visitors section
    if (strpos($current_page_url, $site_url . 'visitors/') === 0) {
        return array(
            'title' => esc_html__('Visitors', 'citygov'),
            'url' => home_url('/visitors/'),
            'current' => false
        );
    }
    
    return null;
}

/**
 * Get section information (forms, directory, etc.)
 */
function get_section_info($post = null) {
    // Get current URL - use global post if none provided
    if (!$post) {
        global $post;
    }
    
    $current_url = get_permalink($post);
    $site_url = home_url('/');
    
    // Get the current page URL for more accurate detection
    $current_page_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    
    
    // Check if we're in the forms section
    if (strpos($current_url, $site_url . 'forms/') === 0 || strpos($current_page_url, $site_url . 'forms/') === 0) {
        return array(
            'title' => esc_html__('Forms', 'citygov'),
            'url' => home_url('/forms/'),
            'current' => false
        );
    }
    
    // Check if we're in the directory section
    if (strpos($current_page_url, $site_url . 'directory/') === 0) {
        return array(
            'title' => esc_html__('Directory', 'citygov'),
            'url' => home_url('/directory/'),
            'current' => false
        );
    }
    
    // Check if we're in the documents section
    if (strpos($current_page_url, $site_url . 'documents/') === 0) {
        return array(
            'title' => esc_html__('Documents', 'citygov'),
            'url' => home_url('/documents/'),
            'current' => false
        );
    }
    
    // Check if we're in the departments section
    // Note: We don't add a "Departments" breadcrumb here because we'll show the super department instead
    if (strpos($current_page_url, $site_url . 'departments/') === 0) {
        return null; // Don't add a "Departments" breadcrumb
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
            echo '<span class="breadcrumb-separator">→</span>';
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

/**
 * Temporary debug function to test breadcrumb logic
 * Usage: <?php debug_breadcrumbs(); ?>
 */
function debug_breadcrumbs() {
    global $post;
    
    echo '<div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border: 1px solid #ccc;">';
    echo '<strong>Breadcrumb Debug:</strong><br>';
    echo 'Current URL: ' . get_permalink($post) . '<br>';
    echo 'Site URL: ' . home_url('/') . '<br>';
    echo 'Request URI: ' . $_SERVER['REQUEST_URI'] . '<br>';
    echo 'Post Type: ' . get_post_type() . '<br>';
    echo 'Is Page: ' . (is_page() ? 'Yes' : 'No') . '<br>';
    echo 'Is Single: ' . (is_single() ? 'Yes' : 'No') . '<br>';
    
    $section_info = get_section_info();
    if ($section_info) {
        echo 'Section Info: ' . $section_info['title'] . '<br>';
    } else {
        echo 'Section Info: None<br>';
    }
    
    $department_info = get_department_info($post);
    if ($department_info) {
        echo 'Department Info: ' . $department_info['title'] . '<br>';
    } else {
        echo 'Department Info: None<br>';
    }
    
    // Debug department helper functions
    $department_root_id = get_department_root_page_id($post->ID);
    if ($department_root_id) {
        echo 'Department Root ID: ' . $department_root_id . '<br>';
        $department_page = get_post($department_root_id);
        if ($department_page) {
            echo 'Department Root Title: ' . get_the_title($department_page) . '<br>';
        }
    } else {
        echo 'Department Root ID: None<br>';
    }
    
    $department_name = get_department_root_name($post->ID);
    if ($department_name) {
        echo 'Department Root Name: ' . $department_name . '<br>';
    } else {
        echo 'Department Root Name: None<br>';
    }
    
    // Debug next level detection
    $next_level_info = get_next_level_after_section($post);
    if ($next_level_info) {
        echo 'Next Level: ' . $next_level_info['title'] . ' (' . $next_level_info['url'] . ')<br>';
    } else {
        echo 'Next Level: None<br>';
    }
    
    echo '</div>';
} 