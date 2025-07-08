<?php 
global $department_data;

if (!empty($department_data)) {
    $department_name = $department_data['department_name'];
    $department_id = $department_data['department_id'];
}

// Store department name in a global variable so it's accessible to shortcodes
global $current_department_name;
$current_department_name = $department_name;

// Store department ID in a global variable so it's accessible to shortcodes
global $current_department_id;
$current_department_id = $department_id;
?>

<div id="sidebar" class="fourcol woocommerce p-border">
    <?php
        // Get department context using the new function
        $department_root_page_id = get_department_root_page_id();
        $department_root_name = get_department_root_name();

        // Display department menu if we have a department context (desktop only)
        if (!empty($department_root_page_id)) {
            echo '<div class="department-menu-section desktop-only">';
            echo '<h2>Department Pages</h2>';
            
            // Get the department ID from the root page
            $dept_id = get_post_meta($department_root_page_id, 'department_id', true);
            
            // Debug information
            echo '<!-- Debug: department_root_page_id = ' . $department_root_page_id . ' -->';
            echo '<!-- Debug: dept_id = ' . $dept_id . ' -->';
            
            if (!empty($dept_id)) {
                // Ensure the department menu exists
                $menu_id = ensure_department_menu_exists($department_root_page_id);
                
                echo '<!-- Debug: menu_id = ' . $menu_id . ' -->';
                
                if ($menu_id) {
                    // Display the WordPress department menu
                    $menu_args = array(
                        'menu' => $menu_id,
                        'container' => 'nav',
                        'container_class' => 'department-menu-sidebar',
                        'menu_class' => 'department-menu-list',
                        'echo' => true,
                        'fallback_cb' => false
                        // Removed custom walker to use standard WordPress menu system
                    );
                    
                    wp_nav_menu($menu_args);
                } else {
                    echo '<p>Department menu could not be loaded.</p>';
                    echo '<!-- Debug: ensure_department_menu_exists returned false -->';
                }
            } else {
                echo '<p>No department ID found for this page.</p>';
                echo '<!-- Debug: dept_id is empty -->';
            }
            
            echo '</div>';
        } else {
            echo '<!-- Debug: department_root_page_id is empty -->';
        }
    ?>
    <?php if (is_active_sidebar('department-homepage')) { ?>
        
        <div class="widgetable p-border">
            <?php dynamic_sidebar('department-homepage'); ?>
            
        </div>
        
    <?php } else { ?>
        <p>No widgets found for the department homepage sidebar.</p>
    <?php } ?>
    
    
        
</div><!-- #sidebar -->
