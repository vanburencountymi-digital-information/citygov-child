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
  - **Shortcode Attribute Modification**:
    - Function: `modify_shortcode_atts`
    - Replaces `{department_name}` placeholder in shortcode attributes with the current department name.

### 3. Sidebar
- **File**: `sidebar.php`
  - Passes the `department_name` to the sidebar to make it accessible to widgets and shortcodes.
  - Uses a global variable `$current_department_name` to store the department name.

### 4. Template
- **File**: `template-department-homepage.php`
  - Custom page template for department homepages.
  - Displays department details and staff directory using shortcodes.
  - Retrieves `department_id` and `department_name` from custom fields.

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
