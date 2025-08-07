<?php
/**
 * Department Menus Module
 * 
 * Handles all department menu generation and management functionality.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generate a navigation menu for a department including all subpages and sub-subpages
 * 
 * This function creates a hierarchical menu structure similar to the subpages shortcode
 * but as a WordPress navigation menu that can be used in mobile popups or other contexts.
 * 
 * @param int $department_root_id The ID of the department root page
 * @param bool $force_regenerate Whether to regenerate the menu even if it exists
 * @return int|false The menu ID on success, false on failure
 */
function ensure_department_menu_exists($department_root_id, $force_regenerate = false) {
    // Get the department ID from the root page
    $dept_id = get_post_meta($department_root_id, 'department_id', true);
    if (!$dept_id) {
        return false;
    }
    
    // Create user-friendly menu name
    $department_name = get_the_title($department_root_id);
    $menu_name = "Department Menu - " . $department_name;
    
    // Create a unique slug that's safe for WordPress but includes department info
    $menu_slug = "department_menu_" . sanitize_title($department_name) . "_{$dept_id}";
    
    // Check if menu already exists by slug
    $existing_menu = wp_get_nav_menu_object($menu_slug);
    
    // Also check if a menu with the same name exists (in case it was created manually)
    $all_menus = wp_get_nav_menus();
    $existing_menu_by_name = null;
    foreach ($all_menus as $menu) {
        if ($menu->name === $menu_name) {
            $existing_menu_by_name = $menu;
            break;
        }
    }
    
    // Determine which existing menu to use (if any)
    $menu_to_update = null;
    if ($existing_menu) {
        $menu_to_update = $existing_menu;
    } elseif ($existing_menu_by_name) {
        $menu_to_update = $existing_menu_by_name;
    }
    
    if ($menu_to_update && !$force_regenerate) {
        // Menu exists and we're not forcing regeneration, but we should still update it
        $menu_id = $menu_to_update->term_id;
    } else {
        // Need to create a new menu or force regenerate
        if ($menu_to_update && $force_regenerate) {
            wp_delete_nav_menu($menu_to_update->term_id);
        }
        
        // Create new menu with the user-friendly name as the slug initially
        // This ensures the display name starts correctly
        $menu_id = wp_create_nav_menu($menu_name);
        if (is_wp_error($menu_id)) {
            return false;
        }
        
        // Now update the slug to our desired format while keeping the name
        $menu_object = wp_get_nav_menu_object($menu_id);
        if ($menu_object) {
            // Update the term to have our custom slug while keeping the name
            wp_update_term($menu_id, 'nav_menu', array(
                'name' => $menu_name,
                'slug' => $menu_slug
            ));
        }
    }
    
    // Now we have a menu_id (either existing or new) - let's update/add the menu items
    
    // Get existing menu items to check for duplicates
    $existing_items = wp_get_nav_menu_items($menu_id);
    $existing_item_ids = array();
    if (!empty($existing_items)) {
        foreach ($existing_items as $item) {
            if ($item->object === 'page') {
                $existing_item_ids[] = $item->object_id;
            }
        }
    }
    
    // Add some common department-specific menu items FIRST
    $additional_items = array(
        array(
            'title' => 'Department Home',
            'url' => get_permalink($department_root_id),
            'type' => 'custom'
        ),
        array(
            'title' => 'Contact Us',
            'url' => get_permalink($department_root_id) . '#contact',
            'type' => 'custom',
            'classes' => array('contact-us-menu-item')
        ),
        array(
            'title' => 'Staff Directory',
            'url' => home_url('/directory/' . generate_directory_slug(get_the_title($department_root_id)) . '/'),
            'type' => 'custom'
        )
    );
    
    $additional_items_created = 0;
    foreach ($additional_items as $item) {
        // Check if this additional item already exists (by URL)
        $item_exists = false;
        foreach ($existing_items as $existing_item) {
            if ($existing_item->url === $item['url']) {
                $item_exists = true;
                break;
            }
        }
        
        if (!$item_exists) {
            $additional_item_data = array(
                'menu-item-title' => $item['title'],
                'menu-item-url' => $item['url'],
                'menu-item-type' => $item['type'],
                'menu-item-status' => 'publish'
            );
            
            if (isset($item['classes'])) {
                $additional_item_data['menu-item-classes'] = implode(' ', $item['classes']);
            }
            
            $additional_item_id = wp_update_nav_menu_item($menu_id, 0, $additional_item_data);
            if ($additional_item_id && !is_wp_error($additional_item_id)) {
                $additional_items_created++;
            }
        }
    }
    
    // Get all direct children of the department root
    $subpages_args = array(
        'parent' => $department_root_id,
        'sort_order' => 'ASC',
        'sort_column' => 'menu_order, post_title',
        'hierarchical' => 0,
        'post_type' => 'page',
        'post_status' => 'publish'
    );
    
    $subpages = get_pages($subpages_args);
    
    $menu_items_created = 0;
    
    if (!empty($subpages)) {
        foreach ($subpages as $page) {
            // Check if this page already exists in the menu
            if (in_array($page->ID, $existing_item_ids)) {
                continue;
            }
            
            // Check if this subpage has children
            $has_children = count(get_pages(array('parent' => $page->ID))) > 0;
            
            // Create the main menu item
            $menu_item_data = array(
                'menu-item-title' => $page->post_title,
                'menu-item-object' => 'page',
                'menu-item-object-id' => $page->ID,
                'menu-item-type' => 'post_type',
                'menu-item-status' => 'publish',
                'menu-item-url' => get_permalink($page->ID)
            );
            
            $parent_menu_item_id = wp_update_nav_menu_item($menu_id, 0, $menu_item_data);
            
            if ($parent_menu_item_id && !is_wp_error($parent_menu_item_id)) {
                $menu_items_created++;
                
                // If this page has children, add them as sub-menu items
                if ($has_children) {
                    $nested_args = array(
                        'parent' => $page->ID,
                        'sort_order' => 'ASC',
                        'sort_column' => 'menu_order, post_title',
                        'post_type' => 'page',
                        'post_status' => 'publish'
                    );
                    
                    $nested_pages = get_pages($nested_args);
                    
                    if (!empty($nested_pages)) {
                        foreach ($nested_pages as $subpage) {
                            // Check if this nested page already exists in the menu
                            if (in_array($subpage->ID, $existing_item_ids)) {
                                continue;
                            }
                            
                            $submenu_item_data = array(
                                'menu-item-title' => $subpage->post_title,
                                'menu-item-object' => 'page',
                                'menu-item-object-id' => $subpage->ID,
                                'menu-item-type' => 'post_type',
                                'menu-item-status' => 'publish',
                                'menu-item-url' => get_permalink($subpage->ID),
                                'menu-item-parent-id' => $parent_menu_item_id
                            );
                            
                            $submenu_item_id = wp_update_nav_menu_item($menu_id, 0, $submenu_item_data);
                            if ($submenu_item_id && !is_wp_error($submenu_item_id)) {
                                $menu_items_created++;
                            }
                        }
                    }
                }
            }
        }
    }
    
    return $menu_id;
}

/**
 * Generate menus for all departments that have department_id set
 * 
 * This is a bulk function to create menus for all departments at once.
 * Useful for initial setup or when adding new departments.
 * 
 * @param bool $force_regenerate Whether to regenerate existing menus
 * @return array Array of results for each department processed
 */
function generate_all_department_menus($force_regenerate = false) {
    $results = array();
    
    // Get all pages that have department_id set
    $args = array(
        'post_type' => 'page',
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'department_id',
                'compare' => 'EXISTS'
            )
        ),
        'posts_per_page' => -1
    );
    
    $department_pages = get_posts($args);
    
    foreach ($department_pages as $page) {
        $dept_id = get_post_meta($page->ID, 'department_id', true);
        $menu_id = ensure_department_menu_exists($page->ID, $force_regenerate);
        
        $results[] = array(
            'page_id' => $page->ID,
            'page_title' => $page->post_title,
            'department_id' => $dept_id,
            'menu_id' => $menu_id,
            'success' => $menu_id !== false
        );
    }
    
    return $results;
}

/**
 * Get a department menu by department ID
 * 
 * @param string $department_id The department ID
 * @return WP_Term|false The menu object or false if not found
 */
function get_department_menu($department_id) {
    // First try to find by the new slug format
    $menus = wp_get_nav_menus();
    foreach ($menus as $menu) {
        if (strpos($menu->slug, "department_menu_") === 0 && strpos($menu->slug, "_{$department_id}") !== false) {
            return $menu;
        }
    }
    
    // Fallback to old format for backward compatibility
    $menu_slug = "department_menu_{$department_id}";
    return wp_get_nav_menu_object($menu_slug);
}

/**
 * Display a department menu
 * 
 * @param string $department_id The department ID
 * @param array $args Additional arguments for wp_nav_menu()
 * @return string|false The menu HTML or false if menu not found
 */
function display_department_menu($department_id, $args = array()) {
    $menu = get_department_menu($department_id);
    if (!$menu) {
        return false;
    }
    
    $default_args = array(
        'menu' => $menu->term_id,
        'container' => 'nav',
        'container_class' => 'department-menu',
        'menu_class' => 'department-menu-list',
        'echo' => false
    );
    
    $args = wp_parse_args($args, $default_args);
    
    return wp_nav_menu($args);
}



/**
 * List all existing department menus for debugging
 * 
 * @return array Array of department menus with their details
 */
function list_all_department_menus() {
    $all_menus = wp_get_nav_menus();
    $department_menus = array();
    
    foreach ($all_menus as $menu) {
        // Check if this is a department menu (either old or new format)
        if (strpos($menu->slug, 'department_menu_') === 0) {
            $department_menus[] = array(
                'id' => $menu->term_id,
                'name' => $menu->name,
                'slug' => $menu->slug,
                'count' => $menu->count,
                'format' => strpos($menu->slug, 'department_menu_') === 0 && preg_match('/_\d+$/', $menu->slug) ? 'new' : 'old'
            );
        }
    }
    
    return $department_menus;
}

/**
 * Delete all existing department menus
 * 
 * This function finds and deletes all menus that match the department menu pattern
 * without affecting other menus like the main navigation.
 * 
 * @return array Array of deleted menu information
 */
function delete_all_department_menus() {
    $all_menus = wp_get_nav_menus();
    $deleted_menus = array();
    
    foreach ($all_menus as $menu) {
        // Check if this is a department menu by either:
        // 1. Slug starts with 'department_menu_'
        // 2. Display name starts with 'Department Menu -'
        $is_department_menu = false;
        $reason = '';
        
        if (strpos($menu->slug, 'department_menu_') === 0) {
            $is_department_menu = true;
            $reason = 'slug pattern';
        } elseif (strpos($menu->name, 'Department Menu -') === 0) {
            $is_department_menu = true;
            $reason = 'name pattern';
        }
        
        if ($is_department_menu) {
            $deleted = wp_delete_nav_menu($menu->term_id);
            if ($deleted) {
                $deleted_menus[] = array(
                    'id' => $menu->term_id,
                    'name' => $menu->name,
                    'slug' => $menu->slug,
                    'reason' => $reason,
                    'success' => true
                );
            } else {
                $deleted_menus[] = array(
                    'id' => $menu->term_id,
                    'name' => $menu->name,
                    'slug' => $menu->slug,
                    'reason' => $reason,
                    'success' => false,
                    'error' => 'Failed to delete menu'
                );
            }
        }
    }
    
    return $deleted_menus;
}

/**
 * Reset and regenerate all department menus
 * 
 * This function deletes all existing department menus and then regenerates them
 * from scratch. This is useful when there are conflicts or when you want to
 * ensure all menus are using the latest format.
 * 
 * @return array Array of results for each department processed
 */
function reset_and_regenerate_all_department_menus() {
    // First, delete all existing department menus
    $deleted_menus = delete_all_department_menus();
    
    // Then regenerate all department menus
    $results = generate_all_department_menus(false); // false = don't force regenerate since we just deleted them
    
    // Combine the results
    $combined_results = array(
        'deleted_menus' => $deleted_menus,
        'regenerated_menus' => $results
    );
    
    return $combined_results;
}

/**
 * Migrate existing department menus to the new naming format
 * 
 * This function finds existing menus with the old format and updates them
 * to use the new user-friendly naming while preserving their content.
 * 
 * @return array Array of migration results
 */
function migrate_department_menus_to_new_format() {
    $results = array();
    
    // Get all pages that have department_id set
    $args = array(
        'post_type' => 'page',
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'department_id',
                'compare' => 'EXISTS'
            )
        ),
        'posts_per_page' => -1
    );
    
    $department_pages = get_posts($args);
    
    foreach ($department_pages as $page) {
        $dept_id = get_post_meta($page->ID, 'department_id', true);
        $department_name = $page->post_title;
        
        // Check for old format menu
        $old_menu_slug = "department_menu_{$dept_id}";
        $old_menu = wp_get_nav_menu_object($old_menu_slug);
        
        // Check for new format menu
        $new_menu_slug = "department_menu_" . sanitize_title($department_name) . "_{$dept_id}";
        $new_menu = wp_get_nav_menu_object($new_menu_slug);
        
        if ($old_menu && !$new_menu) {
            // Migrate old menu to new format
            $menu_items = wp_get_nav_menu_items($old_menu->term_id);
            
            // Create new menu with user-friendly name
            $new_menu_id = wp_create_nav_menu($new_menu_slug);
            if (!is_wp_error($new_menu_id)) {
                // Set the user-friendly name
                wp_update_nav_menu_object($new_menu_id, array('menu-name' => "Department Menu - " . $department_name));
                
                // Copy all menu items
                $items_copied = 0;
                foreach ($menu_items as $item) {
                    $new_item_data = array(
                        'menu-item-title' => $item->title,
                        'menu-item-object' => $item->object,
                        'menu-item-object-id' => $item->object_id,
                        'menu-item-type' => $item->type,
                        'menu-item-status' => 'publish',
                        'menu-item-url' => $item->url,
                        'menu-item-parent-id' => $item->menu_item_parent
                    );
                    
                    $new_item_id = wp_update_nav_menu_item($new_menu_id, 0, $new_item_data);
                    if ($new_item_id && !is_wp_error($new_item_id)) {
                        $items_copied++;
                    }
                }
                
                // Delete old menu
                wp_delete_nav_menu($old_menu->term_id);
                
                $results[] = array(
                    'page_id' => $page->ID,
                    'page_title' => $page->post_title,
                    'department_id' => $dept_id,
                    'old_menu_id' => $old_menu->term_id,
                    'new_menu_id' => $new_menu_id,
                    'items_copied' => $items_copied,
                    'success' => true,
                    'action' => 'migrated'
                );
            } else {
                $results[] = array(
                    'page_id' => $page->ID,
                    'page_title' => $page->post_title,
                    'department_id' => $dept_id,
                    'success' => false,
                    'error' => 'Failed to create new menu',
                    'action' => 'migration_failed'
                );
            }
        } elseif ($new_menu) {
            $results[] = array(
                'page_id' => $page->ID,
                'page_title' => $page->post_title,
                'department_id' => $dept_id,
                'menu_id' => $new_menu->term_id,
                'success' => true,
                'action' => 'already_new_format'
            );
        } else {
            $results[] = array(
                'page_id' => $page->ID,
                'page_title' => $page->post_title,
                'department_id' => $dept_id,
                'success' => false,
                'error' => 'No existing menu found',
                'action' => 'no_menu_found'
            );
        }
    }
    
    return $results;
} 