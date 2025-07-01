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
        console.log('Initializing popup accordions...'); // Debug log
        const accordionItems = popup.querySelectorAll(".page-list-ext-item.has-children .page-title-wrapper");
        console.log('Found accordion items:', accordionItems.length); // Debug log
        
        accordionItems.forEach(function(item) {
            // Remove any existing listeners to prevent duplicates
            item.removeEventListener("click", handleAccordionClick);
            item.addEventListener("click", handleAccordionClick);
        });
    }
    
    // Separate function for accordion click handling
    function handleAccordionClick(e) {
        // Prevent navigation if clicking on the dropdown indicator or title itself
        if (e.target.tagName !== "A") {
            e.preventDefault();
            e.stopPropagation();
            const parent = this.closest(".page-list-ext-item");
            parent.classList.toggle("expanded");
            const accordion = parent.querySelector(".subpages-accordion");
            if (parent.classList.contains("expanded")) {
                accordion.style.maxHeight = accordion.scrollHeight + "px";
            } else {
                accordion.style.maxHeight = "0";
            }
        }
    }
    
    // Alternative accordion initialization that works with any accordion structure
    function initializeAllAccordions() {
        // Look for any elements with has-children class and page-title-wrapper
        const allAccordionItems = popup.querySelectorAll(".has-children .page-title-wrapper");
        console.log('Found all accordion items:', allAccordionItems.length);
        
        allAccordionItems.forEach(function(item) {
            // Remove existing listeners
            item.removeEventListener("click", handleGenericAccordionClick);
            item.addEventListener("click", handleGenericAccordionClick);
        });
    }
    
    // Generic accordion click handler
    function handleGenericAccordionClick(e) {
        if (e.target.tagName !== "A") {
            e.preventDefault();
            e.stopPropagation();
            
            const parent = this.closest(".has-children");
            const accordion = parent.querySelector(".subpages-accordion, .accordion-content");
            
            if (parent && accordion) {
                parent.classList.toggle("expanded");
                if (parent.classList.contains("expanded")) {
                    accordion.style.maxHeight = accordion.scrollHeight + "px";
                } else {
                    accordion.style.maxHeight = "0";
                }
            }
        }
    }
    
    // Function to open popup
    window.openDepartmentMenu = function() {
        popup.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
        
        // Initialize accordions after popup is visible - try multiple approaches
        setTimeout(initializePopupAccordions, 50);
        setTimeout(initializeAllAccordions, 100);
        setTimeout(initializePopupAccordions, 200);
        setTimeout(initializeAllAccordions, 300);
        setTimeout(initializePopupAccordions, 500);
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
    
    // Also initialize accordions when popup content changes (MutationObserver)
    const popupObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === "childList" && mutation.addedNodes.length > 0) {
                // Check if any new nodes contain accordion elements
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1 && node.querySelector && 
                        (node.querySelector(".page-list-ext-item.has-children") || 
                         node.querySelector(".has-children"))) {
                        setTimeout(initializePopupAccordions, 100);
                        setTimeout(initializeAllAccordions, 150);
                    }
                });
            }
        });
    });
    
    // Observe the popup body for changes
    const popupBody = popup.querySelector('.popup-body');
    if (popupBody) {
        popupObserver.observe(popupBody, {
            childList: true,
            subtree: true
        });
    }
});
</script> 