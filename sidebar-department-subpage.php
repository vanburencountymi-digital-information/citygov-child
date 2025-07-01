<?php 
// Get department context using the new function
$department_root_id = get_department_root_id();
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
    if (!empty($department_root_id)) {
        echo '<div class="department-menu-section desktop-only">';
        echo '<h2>Department Pages</h2>';
        
        // Get the department root page ID (the page that has the department_id)
        $current_id = get_the_ID();
        while ($current_id > 0) {
            $dept_id = get_post_meta($current_id, 'department_id', true);
            if (!empty($dept_id)) {
                // This is the department root page
                break;
            }
            $current_id = wp_get_post_parent_id($current_id);
        }
        
        if ($current_id > 0) {
            // Display the department menu using the department root page
            echo do_shortcode('[pagelist_ext child_of="' . $current_id . '" show_content="1" limit_content="150" accordion_subpages="1"]');
        }
        
        echo '</div>';
    }
    ?>
        
</div><!-- #sidebar --> 