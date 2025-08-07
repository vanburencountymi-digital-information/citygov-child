/**
 * Department Menu - Now using theme's hover-based dropdown system
 * 
 * This script is no longer needed since we're using the same hover-based
 * dropdown system as the main menu (like Administration, Sheriff, etc.)
 * 
 * The department menu now uses:
 * - Standard WordPress menu walker (no custom walker)
 * - CSS hover states for dropdowns (same as main menu)
 * - Down arrow indicators (↓) like the main menu
 * - No custom toggle buttons or accordion JS
 */

document.addEventListener('DOMContentLoaded', function () {
  const MENU_SELECTOR = '.department-menu-list';

  function initializeDepartmentAccordion(root) {
    const menuLists = (root || document).querySelectorAll(MENU_SELECTOR);
    if (!menuLists || menuLists.length === 0) return;

    menuLists.forEach((menu) => {
      // Prevent double-initialization
      if (menu.dataset.accordionInitialized === 'true') return;
      menu.dataset.accordionInitialized = 'true';

      const itemsWithChildren = menu.querySelectorAll('li.menu-item-has-children');
      itemsWithChildren.forEach((li, index) => {
        const submenu = li.querySelector(':scope > ul.sub-menu');
        const link = li.querySelector(':scope > a');
        if (!submenu) return;

        // Ensure submenu has an id for aria-controls
        if (!submenu.id) {
          submenu.id = `dept-submenu-${Math.random().toString(36).slice(2)}-${index}`;
        }

        // Create a toggle button if it does not exist yet
        let toggleBtn = li.querySelector(':scope > button.department-toggle');
        if (!toggleBtn) {
          toggleBtn = document.createElement('button');
          toggleBtn.type = 'button';
          toggleBtn.className = 'department-toggle';
          toggleBtn.setAttribute('aria-controls', submenu.id);
          toggleBtn.setAttribute('aria-expanded', 'false');
          toggleBtn.setAttribute('aria-label', 'Toggle submenu');
          toggleBtn.textContent = '+';

          // Insert toggle after the link (or at start if no link)
          if (link && link.nextSibling) {
            link.parentNode.insertBefore(toggleBtn, link.nextSibling);
          } else if (link) {
            link.parentNode.appendChild(toggleBtn);
          } else {
            li.insertBefore(toggleBtn, submenu);
          }
        }

        // Initial collapsed state
        let shouldExpand = li.classList.contains('current-menu-item') ||
          li.classList.contains('current-menu-parent') ||
          li.classList.contains('current-menu-ancestor');

        setExpanded(li, submenu, toggleBtn, shouldExpand);

        // Click handler
        toggleBtn.addEventListener('click', function (e) {
          e.preventDefault();
          const isExpanded = li.classList.contains('expanded');
          setExpanded(li, submenu, toggleBtn, !isExpanded);
        });
      });

      // Ensure ancestors of current item are expanded
      const currentItems = menu.querySelectorAll('.current-menu-item, .current-menu-parent, .current-menu-ancestor');
      currentItems.forEach((currentLi) => {
        let parent = currentLi.parentElement;
        while (parent && parent !== menu) {
          if (parent.matches('ul.sub-menu')) {
            const ownerLi = parent.parentElement;
            const ownerToggle = ownerLi && ownerLi.querySelector(':scope > button.department-toggle');
            if (ownerLi && parent) {
              setExpanded(ownerLi, parent, ownerToggle, true);
            }
          }
          parent = parent.parentElement;
        }
      });
    });
  }

  function setExpanded(li, submenu, toggleBtn, expand) {
    if (!submenu) return;
    if (expand) {
      li.classList.add('expanded');
      submenu.style.display = 'block';
      if (toggleBtn) {
        toggleBtn.setAttribute('aria-expanded', 'true');
        toggleBtn.textContent = '−';
      }
    } else {
      li.classList.remove('expanded');
      submenu.style.display = 'none';
      if (toggleBtn) {
        toggleBtn.setAttribute('aria-expanded', 'false');
        toggleBtn.textContent = '+';
      }
    }
  }

  // Initialize immediately on DOM ready
  initializeDepartmentAccordion(document);

  // Re-initialize if the popup opens and content becomes visible
  // Expose a hook if other scripts need to re-init: window.initDepartmentAccordion()
  window.initDepartmentAccordion = function (root) {
    initializeDepartmentAccordion(root || document);
  };
}); 