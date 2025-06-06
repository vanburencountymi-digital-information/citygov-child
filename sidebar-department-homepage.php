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

    <?php if (is_active_sidebar('department-homepage')) { ?>
        
        <div class="widgetable p-border">
            <?php dynamic_sidebar('department-homepage'); ?>
            
        </div>
        
    <?php } else { ?>
        <p>No widgets found for the department homepage sidebar.</p>
    <?php } ?>
        
</div><!-- #sidebar -->
