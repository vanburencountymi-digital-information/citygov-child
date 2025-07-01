# CityGov Child Theme

## Overview
The CityGov Child Theme is a custom WordPress theme built on top of the CityGov parent theme. It includes customizations and enhancements to better suit the needs of the website, including integration with specific plugins and custom shortcodes.

## Theme Details
- **Theme Name**: CityGov Child
- **Template**: citygov
- **Version**: 1.0

## Customizations

### 1. Styles
- **File**: `style.css`
  - Inherits styles from the CityGov parent theme.
  - Custom styles can be added here to override or extend the parent theme's styles.

### 2. Functions
- **File**: `functions.php`
  - **Custom Shortcode**: `pagelist_ext`
    - Replaces the original `pagelist_ext` shortcode from the Page List plugin.
    - Function: `my_custom_pagelist_ext_shortcode`
    - Ensures only child pages of the current page are displayed.
    - Added accordion functionality to nest subpages under parent pages.
  - **Custom Shortcode**: `subpages`
    - Replaces the original `subpages` shortcode from the Page List plugin.
    - Function: `my_custom_subpages_shortcode`
    - Shows a hierarchical list of subpages with interactive accordion dropdowns.
  - **Shortcode Attribute Modification**:
    - Function: `modify_shortcode_atts`
    - Replaces `{department_name}` placeholder in shortcode attributes with the current department name.

### 3. Sidebar
- **File**: `sidebar.php`
  - Passes the `department_name` to the sidebar to make it accessible to widgets and shortcodes.
  - Uses a global variable `$current_department_name` to store the department name.

### 4. Template Parts
- **Directory**: `template-parts/`
  - **Headers** (`template-parts/headers/`): Header components for different page types
    - `department-blog-post-header.php`: Header for department blog posts
    - `sheriff-header.php`: Header for sheriff department pages
  - **Footers** (`template-parts/footers/`): Footer components
    - `sheriff-footer.php`: Footer for sheriff department pages
  - **Modules** (`template-parts/modules/`): Reusable UI components
    - `podcast-player.php`: Audio player component for podcast episodes
    - `exit-modal.php`: Modal dialog component
    - Future modules: `mobile-footer-menu.php`, `department-menu-popup.php`

### 5. Template
- **File**: `template-department-homepage.php`
  - Custom page template for department homepages.
  - Displays department details and staff directory using shortcodes.
  - Retrieves `department_id` and `department_name` from custom fields.

## Custom Shortcodes

### 1. `[pagelist_ext]` Shortcode
- **Purpose**: Lists pages with extended display options including featured images, excerpts, and now accordion-style subpage listings.
- **Function**: `my_custom_pagelist_ext_shortcode()`
- **Key Features**:
  - Shows subpages of the current page by default
  - Displays images, titles, and excerpts
  - Provides accordion-style dropdown for nested subpages
- **Parameters**:
  - `accordion_subpages` (1 = enabled, 0 = disabled) - Controls the accordion behavior
  - `show_image` (1 = show, 0 = hide) - Show featured images
  - `show_content` (1 = show, 0 = hide) - Show page excerpt/content
  - `child_of` - ID of the parent page (defaults to current page)
  - Plus all standard parameters from the original shortcode

### 2. `[subpages]` Shortcode
- **Purpose**: Displays a clean, hierarchical list of subpages with accordion-style dropdowns.
- **Function**: `my_custom_subpages_shortcode()`
- **Key Features**:
  - Shows direct children of the current page
  - Displays nested subpages in an accordion-style dropdown
  - Clean, minimal display focused on page hierarchy
- **Parameters**:
  - `accordion_subpages` (1 = enabled, 0 = disabled) - Toggle accordion functionality
  - `sort_order` (ASC, DESC) - Order of pages
  - `sort_column` (menu_order, post_title, etc.) - Column to sort by
  - `exclude` - IDs of pages to exclude
  - Plus all standard parameters from the original shortcode

### Usage Examples:

## Plugin Interactions
- **Page List Plugin**
  - Overwrites the `pagelist_ext` shortcode to customize page listings.
- **Shortcodes**
  - Utilizes shortcodes for displaying department details and staff directories.

## Installation
1. Upload the `citygov-child` folder to the `/wp-content/themes/` directory.
2. Activate the theme through the 'Appearance > Themes' menu in WordPress.

## Notes
- Ensure the CityGov parent theme is installed and activated.
- Customizations are made to ensure compatibility with specific plugins and to enhance the functionality of the CityGov theme.

## Changelog
- **Version 1.0**
  - Initial release with custom shortcodes and template modifications.

## Future Enhancements
- Consider adding more custom templates for different page types.
- Explore additional integrations with other plugins.

## Support
For support, please contact Drake Olejniczak at drake.olejniczak@gmail.com.
