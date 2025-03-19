<?php 
$args = wp_parse_args($args, array('department_name' => '')); 
$department_name = $args['department_name'];

// Store department name in a global variable so it's accessible to shortcodes
global $current_department_name;
$current_department_name = $department_name;
?>

<div id="sidebar" class="fourcol woocommerce p-border">

    <?php if (is_active_sidebar('tmnf-sidebar')) { ?>
        
        <div class="widgetable p-border">

            <?php dynamic_sidebar('tmnf-sidebar'); ?>
            
        </div>
        
    <?php } ?>
        
</div><!-- #sidebar -->
