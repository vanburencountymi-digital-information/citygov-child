<?php
/**
 * Department Menu Mobile Popup
 * 
 * This template creates a mobile popup that displays the department menu
 * when triggered from the mobile footer menu.
 */
?>

<div id="department-menu-popup" class="mobile-popup hidden">
    <div class="popup-overlay"></div>
    <div class="popup-content">
        <div class="popup-header">
            <h2>Department Pages</h2>
            <button class="popup-close" aria-label="Close department menu">
                <span class="close-icon">×</span>
            </button>
        </div>
        
        <div class="popup-body">
            <?php
            // Get department context using the new function
            $department_root_page_id = get_department_root_page_id();
            $department_root_name = get_department_root_name();

            // Display department menu if we have a department context
            if (!empty($department_root_page_id)) {
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
                            'container_class' => 'department-menu-popup',
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
            } else {
                echo '<p>No department pages found.</p>';
            }
            ?>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const popup = document.getElementById('department-menu-popup');
    const overlay = popup.querySelector('.popup-overlay');
    const closeBtn = popup.querySelector('.popup-close');
    
    // Function to open popup
    window.openDepartmentMenu = function() {
        popup.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    };
    
    // Function to close popup
    function closePopup() {
        popup.classList.add('hidden');
        document.body.style.overflow = ''; // Restore scrolling
    }
    
    // Close on overlay click
    overlay.addEventListener("click", closePopup);
    
    // Close on close button click
    closeBtn.addEventListener("click", closePopup);
    
    // Close on escape key
    document.addEventListener("keydown", function(e) {
        if (e.key === 'Escape' && !popup.classList.contains("hidden")) {
            closePopup();
        }
    });
});
</script> 