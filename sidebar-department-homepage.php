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
        $department_root_id = get_department_root_id();
        $department_root_name = get_department_root_name();

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
    <?php if (is_active_sidebar('department-homepage')) { ?>
        
        <div class="widgetable p-border">
            <?php dynamic_sidebar('department-homepage'); ?>
            
        </div>
        
    <?php } else { ?>
        <p>No widgets found for the department homepage sidebar.</p>
    <?php } ?>
    
    
        
</div><!-- #sidebar -->
