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
            return $current_id; // Return the page ID, not the department_id value
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
    // Fast path for non-strings or empty content
    if (!is_string($content) || $content === '') {
        return $content;
    }

    libxml_use_internal_errors(true);
    $dom = new DOMDocument('1.0', 'UTF-8');

    $wrapper_id = '__wp_fix_wrapper__';
    $wrapped = '<div id="' . $wrapper_id . '">' . $content . '</div>';

    // Load as HTML fragment while preserving UTF-8
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    $xpath = new DOMXPath($dom);
    $wrapper = $xpath->query('//*[@id="' . $wrapper_id . '"]')->item(0);

    if (!$wrapper) {
        libxml_clear_errors();
        return $content;
    }

    // Pass 0: remove Gutenberg paragraph block comments only (<!-- wp:paragraph -->, <!-- /wp:paragraph -->)
    $comments = $xpath->query('//comment()');
    if ($comments && $comments->length > 0) {
        $toRemove = array();
        foreach ($comments as $comment) {
            $text = trim($comment->nodeValue);
            if (preg_match('/^\/?wp:paragraph\b/i', $text)) {
                $toRemove[] = $comment;
            }
        }
        foreach ($toRemove as $node) {
            if ($node->parentNode) {
                $node->parentNode->removeChild($node);
            }
        }
    }

    $block_tags = array(
        'address','article','aside','blockquote','canvas','dd','div','dl','dt','fieldset',
        'figcaption','figure','footer','form','h1','h2','h3','h4','h5','h6','header','hgroup',
        'hr','li','main','nav','noscript','ol','output','p','pre','section','table','tfoot','ul','video'
    );

    $is_block = function($node) use ($block_tags) {
        return $node instanceof DOMElement && in_array(strtolower($node->tagName), $block_tags, true);
    };

    // Pass 1: unwrap block-level elements that are nested inside <p>
    $paragraphs = array();
    foreach ($wrapper->getElementsByTagName('p') as $pNode) {
        $paragraphs[] = $pNode; // Snapshot, as we'll be modifying the DOM
    }

    foreach ($paragraphs as $p) {
        if (!$p->parentNode) {
            continue;
        }

        $buffer = $dom->createDocumentFragment();
        $hasInlineContent = false;

        for ($child = $p->firstChild; $child; $child = $next) {
            $next = $child->nextSibling;

            if ($is_block($child)) {
                // Flush buffered inline content before moving the block out
                if ($buffer->hasChildNodes() && $hasInlineContent) {
                    $newP = $dom->createElement('p');
                    $newP->appendChild($buffer);
                    $p->parentNode->insertBefore($newP, $p);
                }
                $buffer = $dom->createDocumentFragment();
                $hasInlineContent = false;

                $p->parentNode->insertBefore($child, $p);
                continue;
            }

            $buffer->appendChild($child);
            if ($child instanceof DOMText) {
                if (trim($child->wholeText) !== '') {
                    $hasInlineContent = true;
                }
            } else {
                $hasInlineContent = true;
            }
        }

        // If we accumulated inline content, keep it in a new <p>
        if ($buffer->hasChildNodes() && $hasInlineContent) {
            $newP = $dom->createElement('p');
            $newP->appendChild($buffer);
            $p->parentNode->insertBefore($newP, $p);
        }

        // Remove the original (now-empty) <p>
        if ($p->parentNode) {
            $p->parentNode->removeChild($p);
        }
    }

    // Pass 2: remove empty paragraphs (only whitespace and/or <br>)
    $paragraphs = array();
    foreach ($wrapper->getElementsByTagName('p') as $pNode) {
        $paragraphs[] = $pNode;
    }

    foreach ($paragraphs as $p) {
        if (!$p->parentNode) {
            continue;
        }
        $onlyWhitespaceOrBreaks = true;
        for ($child = $p->firstChild; $child; $child = $child->nextSibling) {
            if ($child instanceof DOMText) {
                if (trim($child->wholeText, "\xC2\xA0 \t\r\n") !== '') {
                    $onlyWhitespaceOrBreaks = false;
                    break;
                }
            } elseif ($child instanceof DOMElement && strtolower($child->tagName) === 'br') {
                continue;
            } else {
                $onlyWhitespaceOrBreaks = false;
                break;
            }
        }
        if ($onlyWhitespaceOrBreaks && $p->parentNode) {
            $p->parentNode->removeChild($p);
        }
    }

    // Extract inner HTML of wrapper
    $output = '';
    foreach ($wrapper->childNodes as $child) {
        $output .= $dom->saveHTML($child);
    }

    libxml_clear_errors();
    return $output;
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
 * @param array $post_statuses Array of post statuses to include (default: array('publish'))
 * @return array Results of the fix operation
 */
function fix_invalid_html_blocks($dry_run = true, $post_types = array('post', 'page'), $limit = 0, $post_statuses = array('publish')) {
    $args = array(
        'post_type' => $post_types,
        'post_status' => $post_statuses,
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

/**
 * Convert classic paragraph blocks back to regular blocks
 * 
 * This function converts content that was previously converted to classic paragraph blocks
 * back to regular blocks to prevent content loss when users paste block content.
 * 
 * @param string $content The content to convert
 * @return string The converted content
 */
function convert_classic_paragraphs_to_blocks($content) {
    // Fast path for non-strings or empty content
    if (!is_string($content) || $content === '') {
        return $content;
    }
    
    // If content already has block comments, it's already in block format
    if (strpos($content, '<!-- wp:') !== false) {
        return $content;
    }
    
    // Split content into paragraphs
    $paragraphs = preg_split('/\n\s*\n/', trim($content));
    $converted_content = '';
    
    foreach ($paragraphs as $paragraph) {
        $paragraph = trim($paragraph);
        if (empty($paragraph)) {
            continue;
        }
        
        // Check if this paragraph contains block-level HTML elements
        $block_elements = array(
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'div', 'section', 'article', 'aside', 'header', 'footer',
            'blockquote', 'pre', 'table', 'ul', 'ol', 'li',
            'figure', 'figcaption', 'video', 'audio', 'embed'
        );
        
        $has_block_elements = false;
        foreach ($block_elements as $element) {
            if (preg_match('/<' . $element . '\b/i', $paragraph)) {
                $has_block_elements = true;
                break;
            }
        }
        
        // If paragraph contains block elements, wrap it in appropriate block
        if ($has_block_elements) {
            // Determine the appropriate block type based on content
            if (preg_match('/<h[1-6]\b/i', $paragraph)) {
                // Extract heading level
                preg_match('/<h([1-6])\b/i', $paragraph, $matches);
                $level = $matches[1];
                $converted_content .= "<!-- wp:heading {\"level\":{$level}} -->\n";
                $converted_content .= $paragraph . "\n";
                $converted_content .= "<!-- /wp:heading -->\n\n";
            } elseif (preg_match('/<blockquote\b/i', $paragraph)) {
                $converted_content .= "<!-- wp:quote -->\n";
                $converted_content .= "<blockquote class=\"wp-block-quote\">\n";
                $converted_content .= $paragraph . "\n";
                $converted_content .= "</blockquote>\n";
                $converted_content .= "<!-- /wp:quote -->\n\n";
            } elseif (preg_match('/<pre\b/i', $paragraph)) {
                $converted_content .= "<!-- wp:code -->\n";
                $converted_content .= "<pre class=\"wp-block-code\">\n";
                $converted_content .= $paragraph . "\n";
                $converted_content .= "</pre>\n";
                $converted_content .= "<!-- /wp:code -->\n\n";
            } elseif (preg_match('/<ul\b|ol\b/i', $paragraph)) {
                $converted_content .= "<!-- wp:list -->\n";
                $converted_content .= $paragraph . "\n";
                $converted_content .= "<!-- /wp:list -->\n\n";
            } elseif (preg_match('/<table\b/i', $paragraph)) {
                $converted_content .= "<!-- wp:table -->\n";
                $converted_content .= $paragraph . "\n";
                $converted_content .= "<!-- /wp:table -->\n\n";
            } elseif (preg_match('/<figure\b/i', $paragraph)) {
                $converted_content .= "<!-- wp:image -->\n";
                $converted_content .= $paragraph . "\n";
                $converted_content .= "<!-- /wp:image -->\n\n";
            } else {
                // Generic block wrapper for other block elements
                $converted_content .= "<!-- wp:html -->\n";
                $converted_content .= $paragraph . "\n";
                $converted_content .= "<!-- /wp:html -->\n\n";
            }
        } else {
            // Regular paragraph content - wrap in paragraph block
            $converted_content .= "<!-- wp:paragraph -->\n";
            $converted_content .= "<p>" . $paragraph . "</p>\n";
            $converted_content .= "<!-- /wp:paragraph -->\n\n";
        }
    }
    
    return trim($converted_content);
}

/**
 * Convert classic paragraph blocks for a single page
 * 
 * @param int $page_id The page ID
 * @param bool $dry_run Whether to perform a dry run
 * @return array Results of the conversion operation
 */
function convert_single_page_classic_paragraphs($page_id, $dry_run = true) {
    $page = get_post($page_id);
    if (!$page) {
        return array('success' => false, 'error' => 'Page not found');
    }
    
    $original_content = $page->post_content;
    $converted_content = convert_classic_paragraphs_to_blocks($original_content);
    
    $content_changed = $original_content !== $converted_content;
    
    if (!$dry_run && $content_changed) {
        $update_result = wp_update_post(array(
            'ID' => $page_id,
            'post_content' => $converted_content
        ));
        
        if (is_wp_error($update_result)) {
            return array('success' => false, 'error' => $update_result->get_error_message());
        }
    }
    
    return array(
        'success' => true,
        'page_id' => $page_id,
        'page_title' => $page->post_title,
        'content_changed' => $content_changed,
        'dry_run' => $dry_run
    );
}

/**
 * Convert classic paragraph blocks across multiple posts/pages
 * 
 * @param bool $dry_run Whether to perform a dry run
 * @param array $post_types Array of post types to process
 * @param int $limit Maximum number of posts to process
 * @param array $post_statuses Array of post statuses to include (default: array('publish'))
 * @return array Results of the conversion operation
 */
function convert_classic_paragraphs_to_blocks_bulk($dry_run = true, $post_types = array('post', 'page'), $limit = 0, $post_statuses = array('publish')) {
    $args = array(
        'post_type' => $post_types,
        'post_status' => $post_statuses,
        'posts_per_page' => $limit > 0 ? $limit : -1,
        'orderby' => 'ID',
        'order' => 'ASC'
    );
    
    $posts = get_posts($args);
    $results = array(
        'total_posts' => count($posts),
        'posts_processed' => 0,
        'posts_converted' => 0,
        'dry_run' => $dry_run,
        'details' => array()
    );
    
    foreach ($posts as $post) {
        $convert_result = convert_single_page_classic_paragraphs($post->ID, $dry_run);
        
        if ($convert_result['success']) {
            $results['posts_processed']++;
            
            if ($convert_result['content_changed']) {
                $results['posts_converted']++;
            }
            
            $results['details'][] = $convert_result;
        }
    }
    
    return $results;
} 