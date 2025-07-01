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
            $department_root_id = get_department_root_id();
            $department_root_name = get_department_root_name();

            // Display department menu if we have a department context
            if (!empty($department_root_id)) {
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
    
    // Function to initialize accordions in the popup
    function initializePopupAccordions() {
        const accordionItems = popup.querySelectorAll(".page-list-ext-item.has-children .page-title-wrapper");
        accordionItems.forEach(function(item) {
            item.addEventListener("click", function(e) {
                // Prevent navigation if clicking on the dropdown indicator or title itself
                if (e.target.tagName !== "A") {
                    e.preventDefault();
                    const parent = this.closest(".page-list-ext-item");
                    parent.classList.toggle("expanded");
                    const accordion = parent.querySelector(".subpages-accordion");
                    if (parent.classList.contains("expanded")) {
                        accordion.style.maxHeight = accordion.scrollHeight + "px";
                    } else {
                        accordion.style.maxHeight = "0";
                    }
                }
            });
        });
    }
    
    // Function to open popup
    window.openDepartmentMenu = function() {
        popup.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
        
        // Initialize accordions after popup is visible
        setTimeout(initializePopupAccordions, 100);
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