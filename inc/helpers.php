<?php
/**
 * Helpers Module
 * 
 * Contains utility functions used across modules.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get the department root page ID by walking up the page hierarchy
 * 
 * This function walks up the page hierarchy until it finds a page that has
 * a department_id meta field, then returns that page's ID.
 * 
 * @param int $post_id Optional. The post ID to start from. Defaults to current post.
 * @return int|null The page ID of the department root page, or null if not found
 */
function get_department_root_page_id($post_id = null) {
    // If no post_id provided, use current post
    if ($post_id === null) {
        global $post;
        if (!$post) {
            // error_log("get_department_root_page_id: No current post found");
            return null;
        }
        $post_id = $post->ID;
    }
    
    // error_log("get_department_root_page_id: Starting with post_id = {$post_id}");
    
    // Start with the current page
    $current_id = $post_id;
    
    // Walk up the hierarchy until we find a department_id or reach the top
    while ($current_id > 0) {
        // Check if current page has department_id
        $department_id = get_post_meta($current_id, 'department_id', true);
        error_log("get_department_root_page_id: Checking page {$current_id}, department_id = '{$department_id}'");
        
        if (!empty($department_id)) {
            // error_log("get_department_root_page_id: Found department_id '{$department_id}' on page {$current_id}");
            return $current_id; // Return the page ID, not the department_id value
        }
        
        // Get the parent page
        $parent_id = wp_get_post_parent_id($current_id);
        error_log("get_department_root_page_id: Parent of {$current_id} is {$parent_id}");
        
        if ($parent_id === 0) {
            // We've reached the top of the hierarchy
            // error_log("get_department_root_page_id: Reached top of hierarchy, no department_id found");
            break;
        }
        
        $current_id = $parent_id;
    }
    
    // No department_id found in the entire hierarchy
    // error_log("get_department_root_page_id: No department_id found in hierarchy starting from {$post_id}");
    return null;
}

/**
 * Get the department ID value by walking up the page hierarchy
 * 
 * This function walks up the page hierarchy until it finds a page that has
 * a department_id meta field, then returns the department_id value.
 * 
 * @param int $post_id Optional. The post ID to start from. Defaults to current post.
 * @return string|null The department_id value if found, null otherwise
 */
function get_department_root_id($post_id = null) {
    // If no post_id provided, use current post
    if ($post_id === null) {
        global $post;
        if (!$post) {
            return null;
        }
        $post_id = $post->ID;
    }
    
    // Start with the current page
    $current_id = $post_id;
    
    // Walk up the hierarchy until we find a department_id or reach the top
    while ($current_id > 0) {
        // Check if current page has department_id
        $department_id = get_post_meta($current_id, 'department_id', true);
        if (!empty($department_id)) {
            return $department_id; // Return the department_id value
        }
        
        // Get the parent page
        $parent_id = wp_get_post_parent_id($current_id);
        if ($parent_id === 0) {
            // We've reached the top of the hierarchy
            break;
        }
        
        $current_id = $parent_id;
    }
    
    // No department_id found in the entire hierarchy
    return null;
}

/**
 * Get the department root name by walking up the page hierarchy
 * 
 * This function works similarly to get_department_root_id but returns
 * the department_name instead of department_id.
 * 
 * @param int $post_id Optional. The post ID to start from. Defaults to current post.
 * @return string|null The department_name if found, null otherwise
 */
function get_department_root_name($post_id = null) {
    // If no post_id provided, use current post
    if ($post_id === null) {
        global $post;
        if (!$post) {
            return null;
        }
        $post_id = $post->ID;
    }
    
    // Start with the current page
    $current_id = $post_id;
    
    // Walk up the hierarchy until we find a department_name or reach the top
    while ($current_id > 0) {
        // Check if current page has department_name
        $department_name = get_post_meta($current_id, 'department_name', true);
        if (!empty($department_name)) {
            return $department_name;
        }
        
        // Get the parent page
        $parent_id = wp_get_post_parent_id($current_id);
        if ($parent_id === 0) {
            // We've reached the top of the hierarchy
            break;
        }
        
        $current_id = $parent_id;
    }
    
    // No department_name found in the entire hierarchy
    return null;
}

/**
 * Generate directory slug from department name
 * 
 * @param string $name Department name
 * @return string URL-friendly slug
 */
function generate_directory_slug($name) {
    // Convert department name to lowercase and replace spaces with hyphens
    $slug = sanitize_title($name);
    
    // Ensure uniqueness if the slug already exists
    $original_slug = $slug;
    $counter = 1;
    while (get_page_by_path($slug, OBJECT, 'directory_page')) {
        $slug = $original_slug . '-' . $counter;
        $counter++;
    }
    
    return $slug;
}

/**
 * Fix invalid HTML blocks in content
 * 
 * @param string $content The content to fix
 * @return string The fixed content
 */
function fix_single_post_html($content) {
    // Common HTML issues and their fixes
    $fixes = array(
        // Fix unclosed tags
        '/<([a-z][a-z0-9]*)[^>]*>(?!.*<\/\1>)/i' => function($matches) {
            $tag = $matches[1];
            $self_closing_tags = array('img', 'br', 'hr', 'input', 'meta', 'link');
            if (in_array(strtolower($tag), $self_closing_tags)) {
                return $matches[0];
            }
            return $matches[0] . '</' . $tag . '>';
        },
        
        // Fix malformed attributes
        '/\s+([a-z-]+)\s*=\s*["\']\s*["\']/i' => '$1=""',
        
        // Fix double quotes in attributes
        '/\s+([a-z-]+)\s*=\s*["\']([^"\']*)"([^"\']*)["\']/i' => '$1="$2$3"',
        
        // Fix unescaped quotes in content
        '/<([^>]*)"([^>]*)>/' => '<$1&quot;$2>',
        
        // Fix malformed list items
        '/<li[^>]*>\s*<\/li>/' => '',
        
        // Fix empty paragraphs
        '/<p[^>]*>\s*<\/p>/' => '',
        
        // Fix malformed links
        '/<a[^>]*>\s*<\/a>/' => '',
    );
    
    $fixed_content = $content;
    
    foreach ($fixes as $pattern => $replacement) {
        if (is_callable($replacement)) {
            $fixed_content = preg_replace_callback($pattern, $replacement, $fixed_content);
        } else {
            $fixed_content = preg_replace($pattern, $replacement, $fixed_content);
        }
    }
    
    return $fixed_content;
}

/**
 * Count HTML issues between original and fixed content
 * 
 * @param string $original_content The original content
 * @param string $fixed_content The fixed content
 * @return array Array with issue counts
 */
function count_html_issues($original_content, $fixed_content) {
    $issues = array(
        'unclosed_tags' => 0,
        'malformed_attributes' => 0,
        'empty_elements' => 0,
        'total_fixes' => 0
    );
    
    // Count unclosed tags
    preg_match_all('/<([a-z][a-z0-9]*)[^>]*>(?!.*<\/\1>)/i', $original_content, $matches);
    $issues['unclosed_tags'] = count($matches[0]);
    
    // Count malformed attributes
    preg_match_all('/\s+([a-z-]+)\s*=\s*["\']\s*["\']/i', $original_content, $matches);
    $issues['malformed_attributes'] = count($matches[0]);
    
    // Count empty elements
    preg_match_all('/<([^>]*)\s*>\s*<\/\1>/', $original_content, $matches);
    $issues['empty_elements'] = count($matches[0]);
    
    // Calculate total fixes
    $issues['total_fixes'] = $issues['unclosed_tags'] + $issues['malformed_attributes'] + $issues['empty_elements'];
    
    return $issues;
}

/**
 * Fix HTML blocks for a single page
 * 
 * @param int $page_id The page ID
 * @param bool $dry_run Whether to perform a dry run
 * @return array Results of the fix operation
 */
function fix_single_page_html_blocks($page_id, $dry_run = true) {
    $page = get_post($page_id);
    if (!$page) {
        return array('success' => false, 'error' => 'Page not found');
    }
    
    $original_content = $page->post_content;
    $fixed_content = fix_single_post_html($original_content);
    
    $issues = count_html_issues($original_content, $fixed_content);
    
    if (!$dry_run && $original_content !== $fixed_content) {
        $update_result = wp_update_post(array(
            'ID' => $page_id,
            'post_content' => $fixed_content
        ));
        
        if (is_wp_error($update_result)) {
            return array('success' => false, 'error' => $update_result->get_error_message());
        }
    }
    
    return array(
        'success' => true,
        'page_id' => $page_id,
        'page_title' => $page->post_title,
        'issues_found' => $issues,
        'content_changed' => $original_content !== $fixed_content,
        'dry_run' => $dry_run
    );
}

/**
 * Fix invalid HTML blocks across multiple posts/pages
 * 
 * @param bool $dry_run Whether to perform a dry run
 * @param array $post_types Array of post types to process
 * @param int $limit Maximum number of posts to process
 * @return array Results of the fix operation
 */
function fix_invalid_html_blocks($dry_run = true, $post_types = array('post', 'page'), $limit = 0) {
    $args = array(
        'post_type' => $post_types,
        'post_status' => 'publish',
        'posts_per_page' => $limit > 0 ? $limit : -1,
        'orderby' => 'ID',
        'order' => 'ASC'
    );
    
    $posts = get_posts($args);
    $results = array(
        'total_posts' => count($posts),
        'posts_processed' => 0,
        'posts_fixed' => 0,
        'total_issues_found' => 0,
        'dry_run' => $dry_run,
        'details' => array()
    );
    
    foreach ($posts as $post) {
        $fix_result = fix_single_page_html_blocks($post->ID, $dry_run);
        
        if ($fix_result['success']) {
            $results['posts_processed']++;
            
            if ($fix_result['content_changed']) {
                $results['posts_fixed']++;
                $results['total_issues_found'] += $fix_result['issues_found']['total_fixes'];
            }
            
            $results['details'][] = $fix_result;
        }
    }
    
    return $results;
}

/**
 * Set department subpage templates
 * 
 * @param bool $dry_run Whether to perform a dry run
 * @param int $limit Maximum number of pages to process
 * @return array Results of the operation
 */
function set_department_subpage_templates($dry_run = true, $limit = 0) {
    // Get all pages that have a parent with department_id
    $args = array(
        'post_type' => 'page',
        'post_status' => 'publish',
        'posts_per_page' => $limit > 0 ? $limit : -1,
        'orderby' => 'ID',
        'order' => 'ASC'
    );
    
    $pages = get_posts($args);
    $results = array(
        'total_pages' => count($pages),
        'pages_processed' => 0,
        'pages_updated' => 0,
        'pages_skipped' => 0,
        'dry_run' => $dry_run,
        'details' => array()
    );
    
    foreach ($pages as $page) {
        $parent_id = wp_get_post_parent_id($page->ID);
        if ($parent_id > 0) {
            $parent = get_post($parent_id);
            if ($parent && get_post_meta($parent_id, 'department_id', true)) {
                // This is a subpage of a department
                $current_template = get_page_template_slug($page->ID);
                $target_template = 'template-department-subpage.php';
                
                // Skip if the page already uses the department homepage template
                if ($current_template === 'template-department-homepage.php') {
                    $results['pages_skipped']++;
                    $results['details'][] = array(
                        'page_id' => $page->ID,
                        'page_title' => $page->post_title,
                        'parent_id' => $parent_id,
                        'parent_title' => $parent->post_title,
                        'old_template' => $current_template,
                        'new_template' => $target_template,
                        'updated' => false,
                        'skipped' => true,
                        'reason' => 'Already uses department homepage template'
                    );
                    continue;
                }
                
                if ($current_template !== $target_template) {
                    $results['pages_processed']++;
                    
                    if (!$dry_run) {
                        update_post_meta($page->ID, '_wp_page_template', $target_template);
                    }
                    
                    $results['pages_updated']++;
                    $results['details'][] = array(
                        'page_id' => $page->ID,
                        'page_title' => $page->post_title,
                        'parent_id' => $parent_id,
                        'parent_title' => $parent->post_title,
                        'old_template' => $current_template,
                        'new_template' => $target_template,
                        'updated' => true,
                        'skipped' => false
                    );
                }
            }
        }
    }
    
    return $results;
} 