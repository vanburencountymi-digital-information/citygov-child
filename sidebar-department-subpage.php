<?php 
// Get department context using the new function
$department_root_page_id = get_department_root_page_id();
$department_root_name = get_department_root_name();
?>

<div id="sidebar" class="fourcol woocommerce p-border">

    <?php if (is_active_sidebar('sidebar-1')) { ?>
        
        <div class="widgetable p-border">
            <?php dynamic_sidebar('sidebar-1'); ?>
            
        </div>
        
    <?php } ?>
    
    <?php
    // Display department menu if we have a department context (desktop only)
    if (!empty($department_root_page_id)) {
        echo '<div class="department-menu-section desktop-only">';
        echo '<h2>Department Pages</h2>';
        
        // Get the department ID from the root page
        $dept_id = get_post_meta($department_root_page_id, 'department_id', true);
        
        if (!empty($dept_id)) {
            // Ensure the department menu exists
            $menu_id = ensure_department_menu_exists($department_root_page_id);
            
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
            }
        } else {
            echo '<p>No department ID found for this page.</p>';
        }
        
        echo '</div>';
    }
    ?>
        
</div><!-- #sidebar --> 