<?php
function citygov_child_enqueue_styles() {
    // Load the parent stylesheet directly from the parent directory
    wp_enqueue_style('citygov-style', get_template_directory_uri() . '/style.css');

    // Then load the child stylesheet
    wp_enqueue_style('citygov-child-style', get_stylesheet_uri(), array('citygov-style'), wp_get_theme()->get('Version'));
}
add_action('wp_enqueue_scripts', 'citygov_child_enqueue_styles');
//Register a separate sidebar for the department homepage
function my_theme_widgets_init() {
    // Department Homepage Sidebar
    register_sidebar( array(
        'name'          => 'Department Homepage Sidebar',
        'id'            => 'department-homepage',
        'description'   => 'Widgets in this area will be shown on the department homepage template.',
        'before_widget' => '<div id="%1$s" class="sidebar_item">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="widgettitle">',
        'after_title'   => '</h2>',
    ) );
    // Department Blog Single Post Sticky Sidebar
    register_sidebar( array(
        'name'          => 'Department Blog Single Post Sticky Sidebar',
        'id'            => 'department-blog-single-post-sticky-sidebar',
        'description'   => 'Widgets in this area will appear in the sticky sidebar for single posts.',
        'before_widget' => '<div id="%1$s" class="sidebar_item">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2 class="widgettitle">',
        'after_title'   => '</h2>',
    ) );
}
add_action( 'widgets_init', 'my_theme_widgets_init' );

function my_child_theme_menus() {
    register_nav_menus(array(
        'courts_quick_links' => 'Courts Quick Links',
        'sheriff_quick_links' => 'Sheriff Quick Links',
    ));
}
add_action('after_setup_theme', 'my_child_theme_menus');

function podcast_enqueue_scripts() {
        // Podcast player script
        wp_enqueue_script(
            'podcast-player-js',
        get_stylesheet_directory_uri() . '/js/podcast-player.js',
        array(), // No dependencies
        filemtime(get_stylesheet_directory() . '/js/podcast-player.js'), // cache bust
        true // Load in footer
    );
}
add_action('wp_enqueue_scripts', 'podcast_enqueue_scripts');

function sheriff_enqueue_scripts() {
    wp_enqueue_script(
        'sheriff-autolink',
        get_stylesheet_directory_uri() . '/js/auto-detect-phone-and-email.js',
        array(), // dependencies (if any)
        '1.0.0', // version
        true // load in footer
    );
}
add_action('wp_enqueue_scripts', 'sheriff_enqueue_scripts');

function enqueue_external_link_warning_script() {
    wp_enqueue_script(
        'external-link-warning',
        get_stylesheet_directory_uri() . '/js/external-link-warning.js',
        array(), // dependencies (e.g., jQuery)
        null,     // version
        true      // in footer
    );
}
add_action('wp_enqueue_scripts', 'enqueue_external_link_warning_script');

function enqueue_anchor_offset_script() {
    wp_enqueue_script(
        'auto-anchor-offset',
        get_stylesheet_directory_uri() . '/js/auto-anchor-offset.js',
        array(),
        null,
        true
    );
}
add_action('wp_enqueue_scripts', 'enqueue_anchor_offset_script');


function dynamic_doc_library_shortcode($atts) {
    // Get the URL parameter if it exists
    $doc_tag = isset($_GET['doc_tag']) ? sanitize_text_field($_GET['doc_tag']) : '';

    // Merge it with any shortcode attributes passed directly
    $atts = shortcode_atts(array(
        'tag' => $doc_tag,
    ), $atts);

    // Return the original shortcode with dynamic attribute
    return do_shortcode('[doc_library doc_tag="' . $doc_tag . '"]');
}

// Register the dynamic shortcode
add_shortcode('dynamic_doc_library', 'dynamic_doc_library_shortcode');


// Add a filter to modify shortcode attributes
function modify_shortcode_atts($atts) {
    global $current_department_name;
    global $current_department_id;
    
    // Look for placeholder in all attributes
    if (!empty($current_department_name)) {
        foreach ($atts as $key => $value) {
            if (is_string($value)) {
                $atts[$key] = str_replace('{department_name}', $current_department_name, $value);
            }
        }
    }
    if (!empty($current_department_id)) {
        foreach ($atts as $key => $value) {
            if (is_string($value)) {
                $atts[$key] = str_replace('{department_id}', $current_department_id, $value);
            }
        }
    }

    return $atts;
}
add_filter('shortcode_atts_tribe_events_list', 'modify_shortcode_atts', 10, 1);
add_filter('shortcode_atts_tribe_events', 'modify_shortcode_atts', 10, 1);
add_filter('shortcode_atts_department_details', 'modify_shortcode_atts', 10, 1);
add_filter('shortcode_atts_staff_directory', 'modify_shortcode_atts', 10, 1);

// Tell document library pro to stop overriding the content of the single document page
add_filter( 'document_library_pro_enable_single_content_customization', '__return_false' );

// Fix the pagelist_ext shortcode so it only shows children (and not grandchildren) of the current page

// Remove the original shortcode
remove_shortcode('pagelist_ext');
remove_shortcode('pagelistext');

function enqueue_pdf_loader_script() {
    if ( is_singular( 'dlp_document' ) ) {
        wp_enqueue_script(
            'pdf-loader-script',
            get_stylesheet_directory_uri() . '/js/pdf-loader.js',
            array(),
            null,
            true // Load in footer
        );
    }
}
add_action( 'wp_enqueue_scripts', 'enqueue_pdf_loader_script' );

// Define your custom shortcode function with a unique name
function my_custom_pagelist_ext_shortcode($atts) {
    global $post, $pagelist_unq_settings;
    $return = '';
    extract( shortcode_atts( array(
        'show_image' => 1,
        'show_first_image' => 0,
        'show_title' => 1,
        'show_content' => 1,
        'more_tag' => 1,
        'limit_content' => 250,
        'image_width' => '150',
        'image_height' => '150',
        'child_of' => '',
        'sort_order' => 'ASC',
        'sort_column' => 'menu_order, post_title',
        'hierarchical' => 1,
        'exclude' => '0',
        'include' => '0',
        'meta_key' => '',
        'meta_value' => '',
        'authors' => '',
        'parent' => -1,
        'exclude_tree' => '',
        'number' => '',
        'offset' => 0,
        'post_type' => 'page',
        'post_status' => 'publish',
        'class' => '',
        'strip_tags' => 1,
        'strip_shortcodes' => 1,
        'show_child_count' => 0,
        'child_count_template' => 'Subpages: %child_count%',
        'show_meta_key' => '',
        'meta_template' => '%meta%',
        'accordion_subpages' => 1, // New parameter to control accordion behavior
    ), $atts ) );

    // Sanitize and validate image_width
    $image_width = absint($image_width);
    if ($image_width === 0) {
        $image_width = 150; // Set a default value if invalid input is provided
    }

    // Sanitize and validate image_height
    $image_height = absint($image_height);
    if ($image_height === 0) {
        $image_height = 150; // Set a default value if invalid input is provided
    }

    if ( $child_of == '' ) { // show subpages if child_of is empty
        $child_of = isset($post->ID) ? $post->ID : 0;
    }

    $page_list_ext_args = array(
        'show_image' => $show_image,
        'show_first_image' => $show_first_image,
        'show_title' => $show_title,
        'show_content' => $show_content,
        'more_tag' => $more_tag,
        'limit_content' => $limit_content,
        'image_width' => $image_width,
        'image_height' => $image_height,
        'sort_order' => $sort_order,
        'sort_column' => $sort_column,
        'hierarchical' => $hierarchical,
        'exclude' => pagelist_unqprfx_norm_params($exclude),
        'include' => pagelist_unqprfx_norm_params($include),
        'meta_key' => $meta_key,
        'meta_value' => $meta_value,
        'authors' => $authors,
// 			'child_of' => pagelist_unqprfx_norm_params($child_of),
        'parent' => pagelist_unqprfx_norm_params($child_of),
        'exclude_tree' => pagelist_unqprfx_norm_params($exclude_tree),
        'number' => '', // $number - own counter
        'offset' => 0, // $offset - own offset
        'post_type' => $post_type,
        'post_status' => $post_status,
        'class' => $class,
        'strip_tags' => $strip_tags,
        'strip_shortcodes' => $strip_shortcodes,
        'show_child_count' => $show_child_count,
        'child_count_template' => $child_count_template,
        'show_meta_key' => $show_meta_key,
        'meta_template' => $meta_template
    );
    $page_list_ext_args_all = array(
        'show_image' => $show_image,
        'show_first_image' => $show_first_image,
        'show_title' => $show_title,
        'show_content' => $show_content,
        'more_tag' => $more_tag,
        'limit_content' => $limit_content,
        'image_width' => $image_width,
        'image_height' => $image_height,
        'sort_order' => $sort_order,
        'sort_column' => $sort_column,
        'hierarchical' => $hierarchical,
        'exclude' => pagelist_unqprfx_norm_params($exclude),
        'include' => pagelist_unqprfx_norm_params($include),
        'meta_key' => $meta_key,
        'meta_value' => $meta_value,
        'authors' => $authors,
        'child_of' => 0, // for showing all pages
        'parent' => pagelist_unqprfx_norm_params($parent),
        'exclude_tree' => pagelist_unqprfx_norm_params($exclude_tree),
        'number' => '', // $number - own counter
        'offset' => 0, // $offset - own offset
        'post_type' => $post_type,
        'post_status' => $post_status,
        'class' => $class,
        'strip_tags' => $strip_tags,
        'strip_shortcodes' => $strip_shortcodes,
        'show_child_count' => $show_child_count,
        'child_count_template' => $child_count_template,
        'show_meta_key' => $show_meta_key,
        'meta_template' => $meta_template
    );
    $list_pages = get_pages( $page_list_ext_args );
    // if ( count( $list_pages ) == 0 ) { // if there is no subpages
    //     $list_pages = get_pages( $page_list_ext_args_all ); // we are showing all pages
    // }
    $list_pages_html = '';
    $count = 0;
    $offset_count = 0;
    if ( $list_pages !== false && count( $list_pages ) > 0 ) {
        foreach($list_pages as $page){
            $count++;
            $offset_count++;
            if ( !empty( $offset ) && is_numeric( $offset ) && $offset_count <= $offset ) {
                $count = 0; // number counter to zero if offset is not finished
            }
            if ( ( !empty( $offset ) && is_numeric( $offset ) && $offset_count > $offset ) || ( empty( $offset ) ) || ( !empty( $offset ) && !is_numeric( $offset ) ) ) {
                if ( ( !empty( $number ) && is_numeric( $number ) && $count <= $number ) || ( empty( $number ) ) || ( !empty( $number ) && !is_numeric( $number ) ) ) {
                    $link = get_permalink( $page->ID );
                    $has_children = count(get_pages(array('parent' => $page->ID))) > 0;
                    $page_class = $has_children && $accordion_subpages ? 'page-list-ext-item has-children' : 'page-list-ext-item';
                    
                    $list_pages_html .= '<div class="' . $page_class . '">';
                    if ( $show_image == 1 ) {
                        if ( get_the_post_thumbnail( $page->ID ) ) { // if there is a featured image
                            $list_pages_html .= '<div class="page-list-ext-image"><a href="'.$link.'" title="'.esc_attr($page->post_title).'">';
                            //$list_pages_html .= get_the_post_thumbnail($page->ID, array($image_width,$image_height)); // doesn't work good with image size

                            $image = wp_get_attachment_image_src( get_post_thumbnail_id( $page->ID ), array($image_width,$image_height) ); // get featured img; 'large'
                            $img_url = $image[0]; // get the src of the featured image
                            $list_pages_html .= '<img src="'.$img_url.'" width="'.esc_attr($image_width).'" alt="'.esc_attr($page->post_title).'" />'; // not using height="'.$image_height.'" because images could be not square shaped and they will be stretched

                            $list_pages_html .= '</a></div> ';
                        } else {
                            if ( $show_first_image == 1 ) {
                                $img_scr = pagelist_unqprfx_get_first_image( $page->post_content );
                                if ( !empty( $img_scr ) ) {
                                    $list_pages_html .= '<div class="page-list-ext-image"><a href="'.$link.'" title="'.esc_attr($page->post_title).'">';
                                    $list_pages_html .= '<img src="'.$img_scr.'" width="'.esc_attr($image_width).'" alt="'.esc_attr($page->post_title).'" />'; // not using height="'.$image_height.'" because images could be not square shaped and they will be stretched
                                    $list_pages_html .= '</a></div> ';
                                }
                            }
                        }
                    }


                    if ( $show_title == 1 ) {
                        if ($has_children && $accordion_subpages) {
                            $list_pages_html .= '<div class="page-title-wrapper">';
                            $list_pages_html .= '<h3 class="page-list-ext-title"><a href="'.$link.'" title="'.esc_attr($page->post_title).'">'.$page->post_title.'</a></h3>';
                            $list_pages_html .= '<span class="dropdown-indicator" aria-hidden="true"></span>';
                            $list_pages_html .= '</div>';
                        } else {
                            $list_pages_html .= '<h3 class="page-list-ext-title"><a href="'.$link.'" title="'.esc_attr($page->post_title).'">'.$page->post_title.'</a></h3>';
                        }
                    }
                    if ( $show_content == 1 ) {
                        //$content = apply_filters('the_content', $page->post_content);
                        //$content = str_replace(']]>', ']]&gt;', $content); // both used in default the_content() function

                        if ( !empty( $page->post_excerpt ) ) {
                            $text_content = $page->post_excerpt;
                        } else {
                            $text_content = $page->post_content;
                        }

                        if ( post_password_required($page) ) {
                            $content = '<!-- password protected -->';
                        } else {
                            $content = pagelist_unqprfx_parse_content( $text_content, $limit_content, $strip_tags, $strip_shortcodes, $more_tag );
                            $content = do_shortcode( $content );

                            if ( $show_title == 0 ) { // make content as a link if there is no title
                                $content = '<a href="'.$link.'">'.$content.'</a>';
                            }
                        }

                        $list_pages_html .= '<div class="page-list-ext-item-content">'.$content.'</div>';

                    }
                    if ( $show_child_count == 1 ) {
                        $count_subpages = count(get_pages("child_of=".$page->ID));
                        if ( $count_subpages > 0 ) { // hide empty
                            $child_count_pos = strpos($child_count_template, '%child_count%'); // check if we have %child_count% marker in template
                            if ($child_count_pos === false) { // %child_count% not found in template
                                $child_count_template_html = $child_count_template.' '.$count_subpages;
                                $list_pages_html .= '<div class="page-list-ext-child-count">'.$child_count_template_html.'</div>';
                            } else { // %child_count% found in template
                                $child_count_template_html = str_replace('%child_count%', $count_subpages, $child_count_template);
                                $list_pages_html .= '<div class="page-list-ext-child-count">'.$child_count_template_html.'</div>';
                            }
                        }
                    }
                    if ( $show_meta_key != '' ) {
                        $post_meta = do_shortcode(get_post_meta($page->ID, $show_meta_key, true));
                        if ( !empty($post_meta) ) { // hide empty
                            $meta_pos = strpos($meta_template, '%meta%'); // check if we have %meta% marker in template
                            if ($meta_pos === false) { // %meta% not found in template
                                $meta_template_html = $meta_template.' '.$post_meta;
                                $list_pages_html .= '<div class="page-list-ext-meta">'.$meta_template_html.'</div>';
                            } else { // %meta% found in template
                                $meta_template_html = str_replace('%meta%', $post_meta, $meta_template);
                                $list_pages_html .= '<div class="page-list-ext-meta">'.$meta_template_html.'</div>';
                            }
                        }
                    }
                    
                    // Add accordion for subpages if this page has children
                    if ($has_children && $accordion_subpages) {
                        // Get subpages of this page
                        $subpages_args = array(
                            'parent' => $page->ID,
                            'sort_order' => $sort_order,
                            'sort_column' => $sort_column,
                            'hierarchical' => 0,
                            'exclude' => pagelist_unqprfx_norm_params($exclude),
                            'include' => pagelist_unqprfx_norm_params($include),
                            'meta_key' => $meta_key,
                            'meta_value' => $meta_value,
                            'authors' => $authors,
                            'post_type' => $post_type,
                            'post_status' => $post_status
                        );
                        
                        $subpages = get_pages($subpages_args);
                        
                        if (!empty($subpages)) {
                            $list_pages_html .= '<div class="subpages-accordion">';
                            
                            foreach ($subpages as $subpage) {
                                $subpage_link = get_permalink($subpage->ID);
                                $list_pages_html .= '<div class="subpage-item">';
                                $list_pages_html .= '<h4 class="subpage-title"><a href="'.$subpage_link.'" title="'.esc_attr($subpage->post_title).'">'.$subpage->post_title.'</a></h4>';
                                
                                // Optionally show excerpt for subpages
                                if ($show_content == 1) {
                                    if (!empty($subpage->post_excerpt)) {
                                        $text_content = $subpage->post_excerpt;
                                    } else {
                                        $text_content = $subpage->post_content;
                                    }
                                    
                                    if (!post_password_required($subpage)) {
                                        $content = pagelist_unqprfx_parse_content($text_content, $limit_content, $strip_tags, $strip_shortcodes, $more_tag);
                                        $content = do_shortcode($content);
                                        $list_pages_html .= '<div class="subpage-content">'.$content.'</div>';
                                    }
                                }
                                
                                $list_pages_html .= '</div>';
                            }
                            
                            $list_pages_html .= '</div>'; // End .subpages-accordion
                        }
                    }
                    
                    $list_pages_html .= '</div>'."\n";
                }
            }
        }
    }
    
    // Add JavaScript for accordion functionality at the end of the return
    if ($accordion_subpages) {
        $accordion_js = '
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            var accordionItems = document.querySelectorAll(".page-list-ext-item.has-children .page-title-wrapper");
            accordionItems.forEach(function(item) {
                item.addEventListener("click", function(e) {
                    // Prevent navigation if clicking on the dropdown indicator or title itself
                    if (e.target.tagName !== "A") {
                        e.preventDefault();
                        var parent = this.closest(".page-list-ext-item");
                        parent.classList.toggle("expanded");
                        var accordion = parent.querySelector(".subpages-accordion");
                        if (parent.classList.contains("expanded")) {
                            accordion.style.maxHeight = accordion.scrollHeight + "px";
                        } else {
                            accordion.style.maxHeight = "0";
                        }
                    }
                });
            });
        });
        </script>';
        
        $return .= $accordion_js;
    }
    
    $return .= $pagelist_unq_settings['powered_by'];
    if ($list_pages_html) {
        $return .= '<div class="page-list page-list-ext '.esc_attr($class).'">'."\n".$list_pages_html."\n".'</div>';
    } else {
        $return .= '<!-- no pages to show -->';
    }
    return $return;
}

// Register your custom version
add_shortcode('pagelist_ext', 'my_custom_pagelist_ext_shortcode');
add_shortcode('pagelistext', 'my_custom_pagelist_ext_shortcode');

function enqueue_pagelist_accordion_styles() {
    wp_enqueue_style(
        'pagelist-accordion-styles',
        get_stylesheet_directory_uri() . '/css/pagelist-accordion.css',
        array(),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'enqueue_pagelist_accordion_styles');

// Remove the original subpages shortcode from the Page-list plugin
remove_shortcode('subpages');
remove_shortcode('sub_pages');

// Create our own custom subpages shortcode with accordion functionality
function my_custom_subpages_shortcode($atts) {
    global $post, $pagelist_unq_settings;
    $return = '';
    
    // Start with defaults from the original plugin plus add accordion parameter
    $defaults = $pagelist_unq_settings['page_list_defaults'];
    $defaults['accordion_subpages'] = 1; // Add accordion parameter with default enabled
    
    $atts = shortcode_atts($defaults, $atts);
    
    // Set up parameters similar to the original subpages shortcode
    $child_of = isset($post->ID) ? $post->ID : 0;
    
    // Get all direct children of the current page
    $subpages_args = array(
        'parent' => $child_of,
        'sort_order' => $atts['sort_order'],
        'sort_column' => $atts['sort_column'],
        'exclude' => pagelist_unqprfx_norm_params($atts['exclude']),
        'exclude_tree' => pagelist_unqprfx_norm_params($atts['exclude_tree']),
        'include' => pagelist_unqprfx_norm_params($atts['include']),
        'post_type' => $atts['post_type'],
        'post_status' => $atts['post_status']
    );
    
    $subpages = get_pages($subpages_args);
    $list_pages_html = '';
    
    if (!empty($subpages)) {
        foreach ($subpages as $page) {
            // Check if this subpage has children
            $has_children = count(get_pages(array('parent' => $page->ID))) > 0;
            $page_class = $has_children && $atts['accordion_subpages'] ? 'subpage-item has-children' : 'subpage-item';
            
            $link = get_permalink($page->ID);
            $list_pages_html .= '<li class="' . $page_class . '">';
            
            // Title with dropdown indicator for pages with children
            if ($has_children && $atts['accordion_subpages']) {
                $list_pages_html .= '<div class="page-title-wrapper">';
                $list_pages_html .= '<a href="' . $link . '" title="' . esc_attr($page->post_title) . '">' . $page->post_title . '</a>';
                $list_pages_html .= '<span class="dropdown-indicator" aria-hidden="true"></span>';
                $list_pages_html .= '</div>';
            } else {
                $list_pages_html .= '<a href="' . $link . '" title="' . esc_attr($page->post_title) . '">' . $page->post_title . '</a>';
            }
            
            // Add nested subpages as accordion if this page has children
            if ($has_children && $atts['accordion_subpages']) {
                // Get subpages of this page (second level)
                $nested_args = array(
                    'parent' => $page->ID,
                    'sort_order' => $atts['sort_order'],
                    'sort_column' => $atts['sort_column'],
                    'post_type' => $atts['post_type'],
                    'post_status' => $atts['post_status']
                );
                
                $nested_pages = get_pages($nested_args);
                
                if (!empty($nested_pages)) {
                    $list_pages_html .= '<ul class="subpages-accordion">';
                    
                    foreach ($nested_pages as $subpage) {
                        $subpage_link = get_permalink($subpage->ID);
                        $list_pages_html .= '<li class="nested-subpage-item">';
                        $list_pages_html .= '<a href="' . $subpage_link . '" title="' . esc_attr($subpage->post_title) . '">' . $subpage->post_title . '</a>';
                        $list_pages_html .= '</li>';
                    }
                    
                    $list_pages_html .= '</ul>';
                }
            }
            
            $list_pages_html .= '</li>';
        }
    }
    
    // Add JavaScript for accordion functionality
    if ($atts['accordion_subpages']) {
        $accordion_js = '
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            var accordionItems = document.querySelectorAll(".subpages-page-list .subpage-item.has-children .page-title-wrapper");
            accordionItems.forEach(function(item) {
                item.addEventListener("click", function(e) {
                    // Prevent navigation if clicking on the dropdown indicator or title wrapper
                    if (e.target.tagName !== "A") {
                        e.preventDefault();
                        var parent = this.closest(".subpage-item");
                        parent.classList.toggle("expanded");
                        var accordion = parent.querySelector(".subpages-accordion");
                        if (parent.classList.contains("expanded")) {
                            accordion.style.maxHeight = accordion.scrollHeight + "px";
                        } else {
                            accordion.style.maxHeight = "0";
                        }
                    }
                });
            });
        });
        </script>';
        
        $return .= $accordion_js;
    }
    
    $return .= $pagelist_unq_settings['powered_by'];
    if ($list_pages_html) {
        $return .= '<h2> Subpages </h2>' . '<ul class="page-list subpages-page-list ' . esc_attr($atts['class']) . '">' . "\n" . $list_pages_html . "\n" . '</ul>';
    } else {
        $return .= '<!-- no pages to show -->';
    }
    return $return;
}

// Register our custom version
add_shortcode('subpages', 'my_custom_subpages_shortcode');
add_shortcode('sub_pages', 'my_custom_subpages_shortcode');

// Add additional CSS for the subpages shortcode
function add_subpages_accordion_styles() {
    // Only add these styles if not already added by the pagelist-accordion.css
    $css = '
    /* Additional styles for [subpages] shortcode with accordion */
    .subpages-page-list .subpage-item.has-children {
        list-style: none;
        margin-bottom: 8px;
    }
    
    .subpages-page-list .subpage-item {
        position: relative;
    }
    
    .subpages-page-list .page-title-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
    }
    
    .subpages-page-list .dropdown-indicator {
        width: 20px;
        height: 20px;
        position: relative;
    }
    
    .subpages-page-list .dropdown-indicator:before,
    .subpages-page-list .dropdown-indicator:after {
        content: \'\';
        position: absolute;
        background-color: #333;
        transition: all 0.3s ease;
    }
    
    .subpages-page-list .dropdown-indicator:before {
        /* Vertical line of the + */
        width: 2px;
        height: 12px;
        top: 4px;
        left: 9px;
    }
    
    .subpages-page-list .dropdown-indicator:after {
        /* Horizontal line of the + */
        width: 12px;
        height: 2px;
        top: 9px;
        left: 4px;
    }
    
    /* When expanded, hide the vertical line to show only - */
    .subpages-page-list .subpage-item.expanded .dropdown-indicator:before {
        opacity: 0;
    }
    
    .subpages-page-list .subpages-accordion {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
        padding-left: 20px;
    }
    
    .subpages-page-list .subpage-item.expanded .subpages-accordion {
        max-height: 1000px;
    }
    
    .subpages-page-list .nested-subpage-item {
        margin: 5px 0;
    }
    ';
    
    wp_add_inline_style('pagelist-accordion-styles', $css);
}
add_action('wp_enqueue_scripts', 'add_subpages_accordion_styles', 12); // Higher priority number to load after main styles

/**
 * Get the department root ID by walking up the page hierarchy
 * 
 * This function starts from the current page and walks up through all ancestors
 * to find the first page that has a department_id defined. If no department_id
 * is found in the entire hierarchy, it returns null.
 * 
 * @param int $post_id Optional. The post ID to start from. Defaults to current post.
 * @return string|null The department_id if found, null otherwise
 */
function get_department_root_id($post_id = null) {
    // If no post_id provided, use current post
    if ($post_id === null) {
        global $post;
        if (!$post) {
            return null;
        }
        $post_id = $post->ID;
    }
    
    // Start with the current page
    $current_id = $post_id;
    
    // Walk up the hierarchy until we find a department_id or reach the top
    while ($current_id > 0) {
        // Check if current page has department_id
        $department_id = get_post_meta($current_id, 'department_id', true);
        if (!empty($department_id)) {
            return $department_id;
        }
        
        // Get the parent page
        $parent_id = wp_get_post_parent_id($current_id);
        if ($parent_id === 0) {
            // We've reached the top of the hierarchy
            break;
        }
        
        $current_id = $parent_id;
    }
    
    // No department_id found in the entire hierarchy
    return null;
}

/**
 * Get the department root name by walking up the page hierarchy
 * 
 * This function works similarly to get_department_root_id but returns
 * the department_name instead of department_id.
 * 
 * @param int $post_id Optional. The post ID to start from. Defaults to current post.
 * @return string|null The department_name if found, null otherwise
 */
function get_department_root_name($post_id = null) {
    // If no post_id provided, use current post
    if ($post_id === null) {
        global $post;
        if (!$post) {
            return null;
        }
        $post_id = $post->ID;
    }
    
    // Start with the current page
    $current_id = $post_id;
    
    // Walk up the hierarchy until we find a department_name or reach the top
    while ($current_id > 0) {
        // Check if current page has department_name
        $department_name = get_post_meta($current_id, 'department_name', true);
        if (!empty($department_name)) {
            return $department_name;
        }
        
        // Get the parent page
        $parent_id = wp_get_post_parent_id($current_id);
        if ($parent_id === 0) {
            // We've reached the top of the hierarchy
            break;
        }
        
        $current_id = $parent_id;
    }
    
    // No department_name found in the entire hierarchy
    return null;
}


