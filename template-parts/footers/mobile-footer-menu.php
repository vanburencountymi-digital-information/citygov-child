<?php
/**
 * Mobile Footer Menu
 * 
 * This template creates a mobile footer menu that appears on mobile devices
 * and includes a department menu button to trigger the department menu popup.
 */
?>

<div id="mobile-footer-menu" class="mobile-footer-menu">
    <div class="mobile-menu-container">
        <div class="mobile-menu-item">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="mobile-menu-link">
                <span class="menu-icon">🏠</span>
                <span class="menu-text">Home</span>
            </a>
        </div>
        
        <?php
        // Only show department menu button if we're in a department context
        $department_root_id = get_department_root_id();
        if (!empty($department_root_id)) :
        ?>
        <div class="mobile-menu-item">
            <button onclick="openDepartmentMenu()" class="mobile-menu-button">
                <span class="menu-icon">📋</span>
                <span class="menu-text">Department Menu</span>
            </button>
        </div>
        <?php endif; ?>
        
        <div class="mobile-menu-item">
            <a href="<?php echo esc_url(get_permalink() . '#contact'); ?>" class="mobile-menu-link">
                <span class="menu-icon">📞</span>
                <span class="menu-text">Contact</span>
            </a>
        </div>
        
        <div class="mobile-menu-item">
            <a href="<?php echo esc_url(home_url('/search')); ?>" class="mobile-menu-link">
                <span class="menu-icon">🔍</span>
                <span class="menu-text">Search</span>
            </a>
        </div>
    </div>
</div> 