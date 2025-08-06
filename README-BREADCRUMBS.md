# Enhanced Breadcrumb System

This child theme includes an enhanced breadcrumb system that provides more intelligent navigation than the parent theme's basic breadcrumbs.

## Features

- **Homepage → Department → Current Page** hierarchy for department pages
- **Homepage → Forms → Current Page** for form center pages
- **Homepage → Directory → Current Page** for directory pages
- **Homepage → Documents → Current Page** for document pages
- Support for custom post types (Projects, Events, Documents)
- Proper handling of categories and archives
- Accessible markup with proper ARIA labels
- Responsive design

## Usage

### In Templates

The breadcrumbs are automatically available in your templates. You can call them using:

```php
<?php citygov_breadcrumbs(); ?>
```

Or use the helper function:

```php
<?php display_breadcrumbs(); ?>
```

### Department Pages

For department pages, the system will automatically detect:
- Pages with the category "department-homepage" or "department"
- Pages with the custom field `_is_department_homepage` set to true
- Child pages of department pages

### Special Sections

The system automatically detects these URL patterns:
- `/forms/` - Shows "Forms" as the section
- `/directory/` - Shows "Directory" as the section  
- `/documents/` - Shows "Documents" as the section

## Customization

### Adding New Sections

To add new sections, edit the `get_section_info()` function in `inc/breadcrumbs.php`:

```php
// Add this inside the get_section_info() function
if (strpos($current_url, $site_url . 'your-section/') === 0) {
    return array(
        'title' => esc_html__('Your Section', 'citygov'),
        'url' => home_url('/your-section/'),
        'current' => false
    );
}
```

### Styling

The breadcrumbs are styled in `css/breadcrumbs.css`. You can customize the appearance by modifying this file.

### Department Detection

The system detects department pages through:
1. Category terms: "department-homepage" or "department"
2. Custom field: `_is_department_homepage` (set to true)
3. Parent page hierarchy

## Examples

### Department Page
```
Home / Police Department / Contact Us
```

### Form Page
```
Home / Forms / Building Permit Application
```

### Document Page
```
Home / Documents / City Charter
```

### Blog Post
```
Home / News / City Council Meeting Summary
```

## Troubleshooting

### Breadcrumbs Not Showing
- Make sure the breadcrumb function is called in your template
- Check that the page is not the homepage (breadcrumbs are hidden on homepage)
- Verify that the CSS file is loading properly

### Department Detection Not Working
- Ensure department pages have the correct category or custom field
- Check that the parent-child page relationship is set up correctly
- Verify the department page URL structure

### Custom Post Types
- Add new post types to the `get_single_breadcrumbs()` function
- Ensure the post type has an archive page
- Add appropriate labels in the switch statement 