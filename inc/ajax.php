<?php
/**
 * AJAX Module
 * 
 * Handles all AJAX handler functions for the child theme.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX handler for replacing PDF documents
 */
function handle_replace_pdf_document() {
    // Check nonce for security
    if (!wp_verify_nonce($_POST['pdf_replace_nonce'], 'replace_pdf_nonce')) {
        wp_die(json_encode(array('success' => false, 'data' => 'Security check failed')));
    }
    
    // Check user permissions
    if (!current_user_can('edit_posts')) {
        wp_die(json_encode(array('success' => false, 'data' => 'Insufficient permissions')));
    }
    
    // Check if file was uploaded
    if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
        wp_die(json_encode(array('success' => false, 'data' => 'No file uploaded or upload error')));
    }
    
    $file = $_FILES['pdf_file'];
    $post_id = intval($_POST['post_id']);
    $new_title = sanitize_text_field($_POST['pdf_title']);
    
    // Validate post exists and is a document
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'dlp_document') {
        wp_die(json_encode(array('success' => false, 'data' => 'Invalid document')));
    }
    
    // Validate file type
    $file_type = wp_check_filetype($file['name']);
    if ($file_type['type'] !== 'application/pdf') {
        wp_die(json_encode(array('success' => false, 'data' => 'Only PDF files are allowed')));
    }
    
    // Validate file size (10MB limit)
    if ($file['size'] > 10 * 1024 * 1024) {
        wp_die(json_encode(array('success' => false, 'data' => 'File size must be less than 10MB')));
    }
    
    // Get the current document object
    $document = dlp_get_document($post_id);
    if (!$document) {
        wp_die(json_encode(array('success' => false, 'data' => 'Could not load document')));
    }
    
    // Verify this is a valid document
    if (!is_object($document) || !method_exists($document, 'get_download_url')) {
        wp_die(json_encode(array('success' => false, 'data' => 'Invalid document object')));
    }
    
    // Debug: Log all post meta to understand Document Library Pro structure
    $all_post_meta = get_post_meta($post_id);
    error_log('Document Library Pro Debug - All post meta for post ' . $post_id . ': ' . print_r($all_post_meta, true));
    
    // Get current file ID from post meta (this is what Document Library Pro actually uses)
    $current_file_id = get_post_meta($post_id, '_dlp_attached_file_id', true);
    error_log('Document Library Pro Debug - Current file ID: ' . $current_file_id);
    
    // Debug: Check what methods are available on the document object
    if (is_object($document)) {
        $methods = get_class_methods($document);
        error_log('Document Library Pro Debug - Available methods: ' . print_r($methods, true));
        
        // Try to get download URL to see how it works
        if (method_exists($document, 'get_download_url')) {
            $download_url = $document->get_download_url();
            error_log('Document Library Pro Debug - Download URL: ' . $download_url);
        }
    }
    
    // Upload the new file
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    
    $upload = wp_handle_upload($file, array('test_form' => false));
    
    if (isset($upload['error'])) {
        wp_die(json_encode(array('success' => false, 'data' => 'Upload failed: ' . $upload['error'])));
    }
    
    // Create attachment post
    $attachment = array(
        'post_title' => $new_title ?: basename($upload['file']),
        'post_content' => '',
        'post_status' => 'inherit',
        'post_mime_type' => $upload['type']
    );
    
    $attachment_id = wp_insert_attachment($attachment, $upload['file'], $post_id);
    
    if (is_wp_error($attachment_id)) {
        wp_die(json_encode(array('success' => false, 'data' => 'Failed to create attachment')));
    }
    
    // Generate attachment metadata
    $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
    wp_update_attachment_metadata($attachment_id, $attachment_data);
    
    // Debug: Log the new attachment ID
    error_log('Document Library Pro Debug - New attachment ID: ' . $attachment_id);
    
    // Use the document object's set_file_id method to properly update the file
    if (method_exists($document, 'set_file_id')) {
        $document->set_file_id($attachment_id);
        error_log('Document Library Pro Debug - Set file ID via document object');
    }
    
    // Update the correct post meta that Document Library Pro uses
    $update_result = update_post_meta($post_id, '_dlp_attached_file_id', $attachment_id);
    error_log('Document Library Pro Debug - Update _dlp_attached_file_id result: ' . ($update_result ? 'true' : 'false'));
    
    // Also update the attachment_id meta for consistency
    update_post_meta($post_id, '_dlp_attachment_id', $attachment_id);
    
    // Update post title if provided
    if ($new_title) {
        wp_update_post(array(
            'ID' => $post_id,
            'post_title' => $new_title
        ));
    }
    
    // Delete the old attachment if it exists and is different
    if ($current_file_id && $current_file_id !== $attachment_id) {
        $delete_result = wp_delete_attachment($current_file_id, true);
        error_log('Document Library Pro Debug - Delete old attachment result: ' . ($delete_result ? 'true' : 'false'));
    }
    
    // Debug: Log final post meta after update
    $final_post_meta = get_post_meta($post_id);
    error_log('Document Library Pro Debug - Final post meta: ' . print_r($final_post_meta, true));
    
    // Refresh the document object to ensure it has the new file
    $updated_document = dlp_get_document($post_id);
    if ($updated_document && method_exists($updated_document, 'get_download_url')) {
        $new_download_url = $updated_document->get_download_url();
        error_log('Document Library Pro Debug - New download URL: ' . $new_download_url);
    }
    
    wp_die(json_encode(array('success' => true, 'data' => 'PDF replaced successfully')));
}
add_action('wp_ajax_replace_pdf_document', 'handle_replace_pdf_document');

/**
 * AJAX handler for regenerating individual department menus
 */
function handle_regenerate_department_menu() {
    // Check nonce for security
    if (!wp_verify_nonce($_POST['regenerate_menu_nonce'], 'regenerate_single_menu')) {
        wp_die(json_encode(array('success' => false, 'message' => 'Security check failed')));
    }
    
    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_die(json_encode(array('success' => false, 'message' => 'Insufficient permissions')));
    }
    
    $page_id = intval($_POST['page_id']);
    $force_regenerate = isset($_POST['force_regenerate']) && $_POST['force_regenerate'] === 'true';
    
    if ($page_id <= 0) {
        wp_die(json_encode(array('success' => false, 'message' => 'Invalid page ID')));
    }
    
    $page = get_post($page_id);
    if (!$page || $page->post_type !== 'page') {
        wp_die(json_encode(array('success' => false, 'message' => 'Invalid page')));
    }
    
    $dept_id = get_post_meta($page_id, 'department_id', true);
    if (empty($dept_id)) {
        wp_die(json_encode(array('success' => false, 'message' => 'Page does not have a department_id set')));
    }
    
    $menu_id = ensure_department_menu_exists($page_id, $force_regenerate);
    
    if ($menu_id !== false) {
        $action_text = $force_regenerate ? 'regenerated' : 'updated';
        $message = "Successfully {$action_text} menu for department: {$page->post_title} (Menu ID: {$menu_id})";
        wp_die(json_encode(array('success' => true, 'message' => $message, 'menu_id' => $menu_id)));
    } else {
        wp_die(json_encode(array('success' => false, 'message' => "Failed to regenerate menu for department: {$page->post_title}")));
    }
}
add_action('wp_ajax_regenerate_department_menu', 'handle_regenerate_department_menu');

/**
 * AJAX handler for adding missing subpages to department menus
 */
function handle_add_missing_subpages() {
    // Check nonce for security
    if (!wp_verify_nonce($_GET['_wpnonce'], 'add_missing_subpages')) {
        wp_die('Security check failed');
    }
    
    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    
    $menu_id = intval($_GET['menu_id']);
    if ($menu_id <= 0) {
        wp_die('Invalid menu ID');
    }
    
    $menu = wp_get_nav_menu_object($menu_id);
    if (!$menu) {
        wp_die('Menu not found');
    }
    
    // Extract department ID from menu slug
    $dept_id = null;
    if (preg_match('/department_menu_.*_(\d+)$/', $menu->slug, $matches)) {
        $dept_id = $matches[1];
    }
    
    if (!$dept_id) {
        wp_die('Could not determine department ID from menu');
    }
    
    // Find the department root page
    $department_pages = get_posts(array(
        'post_type' => 'page',
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'department_id',
                'value' => $dept_id,
                'compare' => '='
            )
        ),
        'posts_per_page' => 1
    ));
    
    if (empty($department_pages)) {
        wp_die('Department page not found');
    }
    
    $department_root = $department_pages[0];
    
    // Get current menu items
    $menu_items = wp_get_nav_menu_items($menu_id);
    $existing_page_ids = array();
    foreach ($menu_items as $item) {
        if ($item->object === 'page') {
            $existing_page_ids[] = $item->object_id;
        }
    }
    
    // Get all subpages of the department
    $subpages = get_pages(array(
        'parent' => $department_root->ID,
        'sort_order' => 'ASC',
        'sort_column' => 'menu_order, post_title',
        'hierarchical' => 0,
        'post_type' => 'page',
        'post_status' => 'publish'
    ));
    
    if ($subpages === false) {
        $subpages = array();
    }
    
    // Find missing subpages
    $missing_subpages = array();
    foreach ($subpages as $subpage) {
        if (!in_array($subpage->ID, $existing_page_ids)) {
            $missing_subpages[] = $subpage;
        }
    }
    
    // Also check for missing nested subpages
    foreach ($subpages as $subpage) {
        $nested_pages = get_pages(array(
            'parent' => $subpage->ID,
            'sort_order' => 'ASC',
            'sort_column' => 'menu_order, post_title',
            'post_type' => 'page',
            'post_status' => 'publish'
        ));
        
        if ($nested_pages !== false) {
            foreach ($nested_pages as $nested_page) {
                if (!in_array($nested_page->ID, $existing_page_ids)) {
                    $missing_subpages[] = $nested_page;
                }
            }
        }
    }
    
    // Add missing subpages to the menu
    $added_count = 0;
    foreach ($missing_subpages as $page) {
        $menu_item_data = array(
            'menu-item-title' => $page->post_title,
            'menu-item-object' => 'page',
            'menu-item-object-id' => $page->ID,
            'menu-item-type' => 'post_type',
            'menu-item-status' => 'publish',
            'menu-item-url' => get_permalink($page->ID)
        );
        
        $menu_item_id = wp_update_nav_menu_item($menu_id, 0, $menu_item_data);
        if ($menu_item_id && !is_wp_error($menu_item_id)) {
            $added_count++;
        }
    }
    
    // Redirect back to the menu page with success message
    $redirect_url = add_query_arg(
        array(
            'page' => 'nav-menus.php',
            'menu' => $menu_id,
            'subpages_added' => $added_count,
            'total_missing' => count($missing_subpages)
        ),
        admin_url('nav-menus.php')
    );
    
    wp_redirect($redirect_url);
    exit;
}
add_action('wp_ajax_add_missing_subpages', 'handle_add_missing_subpages');

/**
 * AJAX handler for fixing HTML blocks
 */
function handle_fix_html_blocks_ajax() {
    // Check nonce for security
    if (!wp_verify_nonce($_POST['html_fix_nonce'], 'fix_html_blocks')) {
        wp_die(json_encode(array('success' => false, 'message' => 'Security check failed')));
    }
    
    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_die(json_encode(array('success' => false, 'message' => 'Insufficient permissions')));
    }
    
    $dry_run = isset($_POST['dry_run']) && $_POST['dry_run'] === 'true';
    $post_types = isset($_POST['post_types']) ? explode(',', $_POST['post_types']) : array('post', 'page');
    $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 0;
    
    $results = fix_invalid_html_blocks($dry_run, $post_types, $limit);
    
    wp_die(json_encode($results));
}
add_action('wp_ajax_fix_html_blocks', 'handle_fix_html_blocks_ajax'); 