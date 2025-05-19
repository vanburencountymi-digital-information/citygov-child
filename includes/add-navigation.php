<?php
// Helper function (if not already loaded elsewhere)
function is_descendant_of_slug($slug) {
    global $post;
    if (!$post) return false;

    $ancestor = get_page_by_path($slug);
    if (!$ancestor || !isset($ancestor->ID)) return false;

    $ancestors = get_post_ancestors($post->ID);
    return in_array($ancestor->ID, $ancestors);
}

// Set label for quick links context
$quick_links_label = ''; // default

if (is_page('county-courts') || is_descendant_of_slug('departments/county-courts')) {
    $quick_links_label = 'Courts';
    $theme_location = 'courts_quick_links';
} elseif (is_page('sheriff') || is_descendant_of_slug('departments/sheriff')) {
    $quick_links_label = 'Sheriff';
    $theme_location = 'sheriff_quick_links';
} else {
    $theme_location = 'add-menu'; // fallback to default
}
?>

<h2 class="quick-links-title"><?php echo esc_html($quick_links_label); ?></h2>

<?php
if (function_exists('has_nav_menu') && has_nav_menu($theme_location)) {
    wp_nav_menu(array(
        'depth' => 2,
        'sort_column' => 'menu_order',
        'container' => 'ul',
        'menu_class' => 'nav tranz',
        'menu_id' => 'add-nav',
        'theme_location' => $theme_location,
        'walker' => new Aria_Walker_Nav_Menu(),
        'items_wrap' => '<ul id="%1$s" class="%2$s" role="menubar">%3$s</ul>',
    ));
}
?>
