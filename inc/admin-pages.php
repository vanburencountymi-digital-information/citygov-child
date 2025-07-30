<?php
/**
 * Admin Pages Module
 * 
 * Handles all admin page registrations and their callbacks for the child theme.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add CSV export functionality to Broken Link Checker plugin
 */
function citygov_add_blc_csv_export() {
    // Only run on the broken link checker admin page
    if (!isset($_GET['page']) || $_GET['page'] !== 'blc_local') {
        return;
    }
    
    // Add export button to the admin page
    add_action('admin_footer', 'citygov_blc_export_button');
    
    // Handle CSV export
    if (isset($_GET['blc_export_csv']) && wp_verify_nonce($_GET['_wpnonce'], 'blc_export_csv')) {
        citygov_export_blc_csv();
    }
}
add_action('admin_init', 'citygov_add_blc_csv_export');

/**
 * Add export button to the broken links page
 */
function citygov_blc_export_button() {
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Add export button to the actions area
        var exportButton = '<a href="<?php echo wp_nonce_url(admin_url('admin.php?page=blc_local&blc_export_csv=1'), 'blc_export_csv'); ?>" class="button button-secondary" style="margin-left: 10px;">Export to CSV</a>';
        
        // Find the actions area and add our button
        $('.sui-actions-right').append(exportButton);
    });
    </script>
    <?php
}

/**
 * Export broken links to CSV
 */
function citygov_export_blc_csv() {
    global $wpdb;
    
    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    
    // Get all broken links with page information
    $query = "
        SELECT 
            l.link_id,
            l.url,
            l.final_url,
            l.http_code,
            l.status_code,
            l.status_text,
            l.broken,
            l.warning,
            l.redirect_count,
            l.last_check,
            l.last_success,
            l.check_count,
            l.request_duration,
            l.dismissed,
            l.first_failure,
            l.log,
            i.container_id,
            i.container_type,
            i.link_text,
            i.raw_url,
            p.post_title,
            p.post_type,
            p.guid
        FROM {$wpdb->prefix}blc_links l
        LEFT JOIN {$wpdb->prefix}blc_instances i ON l.link_id = i.link_id
        LEFT JOIN {$wpdb->posts} p ON i.container_id = p.ID AND i.container_type = 'post'
        WHERE l.broken = 1 OR l.warning = 1
        ORDER BY l.last_check DESC
    ";
    
    $links = $wpdb->get_results($query, ARRAY_A);
    
    if (empty($links)) {
        wp_die('No broken links found to export');
    }
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="broken-links-' . date('Y-m-d-H-i-s') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    $headers = array(
        'Link ID',
        'URL',
        'Final URL',
        'HTTP Code',
        'Status Code',
        'Status Text',
        'Broken',
        'Warning',
        'Redirect Count',
        'Last Check',
        'Last Success',
        'Check Count',
        'Request Duration',
        'Dismissed',
        'First Failure',
        'Log',
        'Page ID',
        'Page Type',
        'Page Title',
        'Page URL',
        'Link Text',
        'Raw URL'
    );
    
    fputcsv($output, $headers);
    
    // Add data rows
    foreach ($links as $link) {
        $row = array(
            $link['link_id'],
            $link['url'],
            $link['final_url'],
            $link['http_code'],
            $link['status_code'],
            $link['status_text'],
            $link['broken'] ? 'Yes' : 'No',
            $link['warning'] ? 'Yes' : 'No',
            $link['redirect_count'],
            $link['last_check'],
            $link['last_success'],
            $link['check_count'],
            $link['request_duration'],
            $link['dismissed'] ? 'Yes' : 'No',
            $link['first_failure'],
            $link['log'],
            $link['container_id'] ?: '',
            $link['container_type'] ?: '',
            $link['post_title'] ?: '',
            $link['guid'] ?: '',
            $link['link_text'] ?: '',
            $link['raw_url'] ?: ''
        );
        
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

/**
 * Add functionality to detect missing subpages on admin menu page
 */
function add_department_menu_missing_pages_notice() {
    global $pagenow;
    
    // Only run on the nav-menus.php page
    if ($pagenow !== 'nav-menus.php') {
        return;
    }
    
    // Get the current menu being edited
    $nav_menu_selected_id = isset($_REQUEST['menu']) ? (int) $_REQUEST['menu'] : 0;
    if (!$nav_menu_selected_id) {
        return;
    }
    
    // Check if this is a department menu
    $menu = wp_get_nav_menu_object($nav_menu_selected_id);
    if (!$menu) {
        return;
    }
    
    // Check if this is a department menu by slug pattern
    if (strpos($menu->slug, 'department_menu_') !== 0) {
        return;
    }
    
    // Extract department ID from menu slug
    $dept_id = null;
    if (preg_match('/department_menu_.*_(\d+)$/', $menu->slug, $matches)) {
        $dept_id = $matches[1];
    }
    
    if (!$dept_id) {
        return;
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
        return;
    }
    
    $department_root = $department_pages[0];
    
    // Get current menu items
    $menu_items = wp_get_nav_menu_items($nav_menu_selected_id);
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
    
    // If there are missing subpages, show notice
    if (!empty($missing_subpages)) {
        add_action('admin_notices', function() use ($missing_subpages, $nav_menu_selected_id, $department_root) {
            $missing_count = count($missing_subpages);
            $missing_titles = array();
            foreach ($missing_subpages as $page) {
                $missing_titles[] = $page->post_title;
            }
            
            $nonce = wp_create_nonce('add_missing_subpages');
            $add_url = admin_url('admin-ajax.php?action=add_missing_subpages&menu_id=' . $nav_menu_selected_id . '&_wpnonce=' . $nonce);
            
            echo '<div class="notice notice-warning department-menu-notice">';
            echo '<div class="notice-title">Missing Subpages in Department Menu</div>';
            echo '<p>The department menu for <strong>' . esc_html($department_root->post_title) . '</strong> is missing ' . $missing_count . ' subpage(s):</p>';
            echo '<ul class="missing-pages-list">';
            foreach ($missing_titles as $title) {
                echo '<li>' . esc_html($title) . '</li>';
            }
            echo '</ul>';
            echo '<p><a href="' . esc_url($add_url) . '" class="button button-primary add-missing-button">Add Missing Subpages to Menu</a></p>';
            echo '</div>';
        });
    }
}
add_action('admin_init', 'add_department_menu_missing_pages_notice');

/**
 * Show notice when subpages are added
 */
function show_subpages_added_notice() {
    if (isset($_GET['subpages_added']) && isset($_GET['total_missing'])) {
        $added = intval($_GET['subpages_added']);
        $total = intval($_GET['total_missing']);
        
        if ($added > 0) {
            $message = sprintf(
                'Successfully added %d out of %d missing subpages to the department menu.',
                $added,
                $total
            );
            
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . esc_html($message) . '</p>';
            echo '</div>';
        }
    }
}
add_action('admin_notices', 'show_subpages_added_notice');

/**
 * Add department menu generator page
 */
function add_department_menu_generator_page() {
    add_management_page(
        'Generate Department Menus',
        'Generate Dept Menus',
        'manage_options',
        'generate-department-menus',
        'department_menu_generator_page'
    );
}
add_action('admin_menu', 'add_department_menu_generator_page');

/**
 * Add HTML block fixer page
 */
function add_html_block_fixer_page() {
    add_management_page(
        'Fix HTML Blocks',
        'Fix HTML Blocks',
        'manage_options',
        'html-block-fixer',
        'html_block_fixer_page'
    );
}
add_action('admin_menu', 'add_html_block_fixer_page');

/**
 * Add department subpage template tool page
 */
function add_department_subpage_template_tool_page() {
    add_management_page(
        'Set Department Subpage Templates',
        'Dept Subpage Templates',
        'manage_options',
        'department-subpage-templates',
        'department_subpage_template_tool_page'
    );
}
add_action('admin_menu', 'add_department_subpage_template_tool_page');

/**
 * Add FileBird migration tool page
 */
function add_filebird_migration_tool_page() {
    add_management_page(
        'FileBird Migration Tool',
        'FileBird Migration',
        'manage_options',
        'filebird-migration-tool',
        'filebird_migration_tool_page'
    );
}
add_action('admin_menu', 'add_filebird_migration_tool_page');

/**
 * Add FileBird rename tool page
 */
function add_filebird_rename_tool_page() {
    add_management_page(
        'FileBird Rename Tool',
        'FileBird Rename',
        'manage_options',
        'filebird-rename-tool',
        'filebird_rename_tool_page'
    );
}
add_action('admin_menu', 'add_filebird_rename_tool_page'); 