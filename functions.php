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

function enqueue_department_menu_script() {
    wp_enqueue_script(
        'department-menu',
        get_stylesheet_directory_uri() . '/js/department-menu.js',
        array(),
        filemtime(get_stylesheet_directory() . '/js/department-menu.js'),
        true
    );
}
add_action('wp_enqueue_scripts', 'enqueue_department_menu_script');

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
    
    // Add JavaScript for accordion functionality at the end of the return
    if ($accordion_subpages) {
        $accordion_js = '
        <script>
        function initializeAccordions() {
            // Target all accordion items in any location
            var accordionItems = document.querySelectorAll(".page-list-ext-item.has-children .page-title-wrapper, .subpages-page-list .subpage-item.has-children .page-title-wrapper");
            accordionItems.forEach(function(item) {
                // Remove existing listeners to prevent duplicates
                item.removeEventListener("click", handleAccordionClick);
                item.addEventListener("click", handleAccordionClick);
            });
        }
        
        function handleAccordionClick(e) {
            // Prevent navigation if clicking on the dropdown indicator or title wrapper
            if (e.target.tagName !== "A") {
                e.preventDefault();
                var parent = this.closest(".page-list-ext-item, .subpage-item");
                parent.classList.toggle("expanded");
                var accordion = parent.querySelector(".subpages-accordion");
                if (parent.classList.contains("expanded")) {
                    accordion.style.maxHeight = accordion.scrollHeight + "px";
                } else {
                    accordion.style.maxHeight = "0";
                }
            }
        }
        
        // Initialize on DOM ready
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", initializeAccordions);
        } else {
            initializeAccordions();
        }
        
        // Also initialize when content is dynamically loaded (for popups)
        document.addEventListener("DOMContentLoaded", function() {
            // Use MutationObserver to watch for new content
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === "childList" && mutation.addedNodes.length > 0) {
                        // Check if any new nodes contain accordion elements
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1 && (node.querySelector(".page-list-ext-item.has-children") || node.querySelector(".subpage-item.has-children"))) {
                                initializeAccordions();
                            }
                        });
                    }
                });
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
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
        function initializeAccordions() {
            // Target all accordion items in any location
            var accordionItems = document.querySelectorAll(".page-list-ext-item.has-children .page-title-wrapper, .subpages-page-list .subpage-item.has-children .page-title-wrapper");
            accordionItems.forEach(function(item) {
                // Remove existing listeners to prevent duplicates
                item.removeEventListener("click", handleAccordionClick);
                item.addEventListener("click", handleAccordionClick);
            });
        }
        
        function handleAccordionClick(e) {
            // Prevent navigation if clicking on the dropdown indicator or title wrapper
            if (e.target.tagName !== "A") {
                e.preventDefault();
                var parent = this.closest(".page-list-ext-item, .subpage-item");
                parent.classList.toggle("expanded");
                var accordion = parent.querySelector(".subpages-accordion");
                if (parent.classList.contains("expanded")) {
                    accordion.style.maxHeight = accordion.scrollHeight + "px";
                } else {
                    accordion.style.maxHeight = "0";
                }
            }
        }
        
        // Initialize on DOM ready
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", initializeAccordions);
        } else {
            initializeAccordions();
        }
        
        // Also initialize when content is dynamically loaded (for popups)
        document.addEventListener("DOMContentLoaded", function() {
            // Use MutationObserver to watch for new content
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === "childList" && mutation.addedNodes.length > 0) {
                        // Check if any new nodes contain accordion elements
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1 && (node.querySelector(".page-list-ext-item.has-children") || node.querySelector(".subpage-item.has-children"))) {
                                initializeAccordions();
                            }
                        });
                    }
                });
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
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
 * Get the department root page ID by walking up the page hierarchy
 * 
 * This function walks up the page hierarchy until it finds a page that has
 * a department_id meta field, then returns that page's ID.
 * 
 * @param int $post_id Optional. The post ID to start from. Defaults to current post.
 * @return int|null The page ID of the department root page, or null if not found
 */
function get_department_root_page_id($post_id = null) {
    // If no post_id provided, use current post
    if ($post_id === null) {
        global $post;
        if (!$post) {
            error_log("get_department_root_page_id: No current post found");
            return null;
        }
        $post_id = $post->ID;
    }
    
    error_log("get_department_root_page_id: Starting with post_id = {$post_id}");
    
    // Start with the current page
    $current_id = $post_id;
    
    // Walk up the hierarchy until we find a department_id or reach the top
    while ($current_id > 0) {
        // Check if current page has department_id
        $department_id = get_post_meta($current_id, 'department_id', true);
        error_log("get_department_root_page_id: Checking page {$current_id}, department_id = '{$department_id}'");
        
        if (!empty($department_id)) {
            error_log("get_department_root_page_id: Found department_id '{$department_id}' on page {$current_id}");
            return $current_id; // Return the page ID, not the department_id value
        }
        
        // Get the parent page
        $parent_id = wp_get_post_parent_id($current_id);
        error_log("get_department_root_page_id: Parent of {$current_id} is {$parent_id}");
        
        if ($parent_id === 0) {
            // We've reached the top of the hierarchy
            error_log("get_department_root_page_id: Reached top of hierarchy, no department_id found");
            break;
        }
        
        $current_id = $parent_id;
    }
    
    // No department_id found in the entire hierarchy
    error_log("get_department_root_page_id: No department_id found in hierarchy starting from {$post_id}");
    return null;
}

/**
 * Get the department ID value by walking up the page hierarchy
 * 
 * This function walks up the page hierarchy until it finds a page that has
 * a department_id meta field, then returns the department_id value.
 * 
 * @param int $post_id Optional. The post ID to start from. Defaults to current post.
 * @return string|null The department_id value if found, null otherwise
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
            return $department_id; // Return the department_id value
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

/**
 * Generate a navigation menu for a department including all subpages and sub-subpages
 * 
 * This function creates a hierarchical menu structure similar to the subpages shortcode
 * but as a WordPress navigation menu that can be used in mobile popups or other contexts.
 * 
 * @param int $department_root_id The ID of the department root page
 * @param bool $force_regenerate Whether to regenerate the menu even if it exists
 * @return int|false The menu ID on success, false on failure
 */
function ensure_department_menu_exists($department_root_id, $force_regenerate = false) {
    error_log("ensure_department_menu_exists: Starting with department_root_id = {$department_root_id}");
    
    // Get the department ID from the root page
    $dept_id = get_post_meta($department_root_id, 'department_id', true);
    if (!$dept_id) {
        error_log("ensure_department_menu_exists: No department_id found for page ID: {$department_root_id}");
        return false;
    }
    
    error_log("ensure_department_menu_exists: Found department_id = {$dept_id}");
    
    // Create user-friendly menu name
    $department_name = get_the_title($department_root_id);
    $menu_name = "Department Menu - " . $department_name;
    
    // Create a unique slug that's safe for WordPress but includes department info
    $menu_slug = "department_menu_" . sanitize_title($department_name) . "_{$dept_id}";
    
    // Debug: Log what we're looking for
    error_log("ensure_department_menu_exists: Looking for department menu - Slug: {$menu_slug}, Name: {$menu_name}, Dept ID: {$dept_id}");
    
    // Check if menu already exists by slug
    $existing_menu = wp_get_nav_menu_object($menu_slug);
    
    if ($existing_menu && !$force_regenerate) {
        // Menu exists and we're not forcing regeneration
        error_log("ensure_department_menu_exists: Found existing menu: {$existing_menu->name} (ID: {$existing_menu->term_id})");
        return $existing_menu->term_id;
    }
    
    // Also check if a menu with the same name exists (in case it was created manually)
    $all_menus = wp_get_nav_menus();
    $existing_menu_by_name = null;
    foreach ($all_menus as $menu) {
        if ($menu->name === $menu_name) {
            $existing_menu_by_name = $menu;
            break;
        }
    }
    
    if ($existing_menu_by_name && !$force_regenerate) {
        // Menu with same name exists, use it
        error_log("ensure_department_menu_exists: Found existing menu by name: {$existing_menu_by_name->name} (ID: {$existing_menu_by_name->term_id})");
        return $existing_menu_by_name->term_id;
    }
    
    // If menu exists and we're forcing regeneration, delete it first
    if ($existing_menu && $force_regenerate) {
        error_log("ensure_department_menu_exists: Deleting existing menu for regeneration: {$existing_menu->name}");
        wp_delete_nav_menu($existing_menu->term_id);
    }
    
    if ($existing_menu_by_name && $force_regenerate) {
        error_log("ensure_department_menu_exists: Deleting existing menu by name for regeneration: {$existing_menu_by_name->name}");
        wp_delete_nav_menu($existing_menu_by_name->term_id);
    }
    
    // Create new menu with the user-friendly name as the slug initially
    // This ensures the display name starts correctly
    error_log("ensure_department_menu_exists: Creating new menu with name: {$menu_name}");
    $menu_id = wp_create_nav_menu($menu_name);
    if (is_wp_error($menu_id)) {
        error_log("ensure_department_menu_exists: Failed to create menu for department {$dept_id}: " . $menu_id->get_error_message());
        return false;
    }
    
    error_log("ensure_department_menu_exists: Successfully created menu with ID: {$menu_id}");
    
    // Now update the slug to our desired format while keeping the name
    $menu_object = wp_get_nav_menu_object($menu_id);
    if ($menu_object) {
        // Update the term to have our custom slug while keeping the name
        $update_result = wp_update_term($menu_id, 'nav_menu', array(
            'name' => $menu_name,
            'slug' => $menu_slug
        ));
        
        if (is_wp_error($update_result)) {
            error_log("ensure_department_menu_exists: Failed to update menu slug: " . $update_result->get_error_message());
        } else {
            error_log("ensure_department_menu_exists: Successfully updated menu slug to: {$menu_slug}");
        }
    }
    
    error_log("ensure_department_menu_exists: Created menu: {$menu_name} (ID: {$menu_id})");
    
    // Add some common department-specific menu items FIRST
    $additional_items = array(
        array(
            'title' => 'Department Home',
            'url' => get_permalink($department_root_id),
            'type' => 'custom'
        ),
        array(
            'title' => 'Contact Us',
            'url' => get_permalink($department_root_id) . '#contact',
            'type' => 'custom',
            'classes' => array('contact-us-menu-item')
        ),
        array(
            'title' => 'Staff Directory',
            'url' => home_url('/directory/' . generate_directory_slug(get_the_title($department_root_id)) . '/'),
            'type' => 'custom'
        )
    );
    
    $additional_items_created = 0;
    foreach ($additional_items as $item) {
        $additional_item_data = array(
            'menu-item-title' => $item['title'],
            'menu-item-url' => $item['url'],
            'menu-item-type' => $item['type'],
            'menu-item-status' => 'publish'
        );
        
        if (isset($item['classes'])) {
            $additional_item_data['menu-item-classes'] = implode(' ', $item['classes']);
        }
        
        $additional_item_id = wp_update_nav_menu_item($menu_id, 0, $additional_item_data);
        if ($additional_item_id && !is_wp_error($additional_item_id)) {
            $additional_items_created++;
        }
    }
    
    error_log("ensure_department_menu_exists: Added {$additional_items_created} additional items");
    
    // Get all direct children of the department root
    $subpages_args = array(
        'parent' => $department_root_id,
        'sort_order' => 'ASC',
        'sort_column' => 'menu_order, post_title',
        'hierarchical' => 0,
        'post_type' => 'page',
        'post_status' => 'publish'
    );
    
    $subpages = get_pages($subpages_args);
    error_log("ensure_department_menu_exists: Found " . count($subpages) . " subpages for department {$department_root_id}");
    
    $menu_items_created = 0;
    
    if (!empty($subpages)) {
        foreach ($subpages as $page) {
            // Check if this subpage has children
            $has_children = count(get_pages(array('parent' => $page->ID))) > 0;
            
            // Create the main menu item
            $menu_item_data = array(
                'menu-item-title' => $page->post_title,
                'menu-item-object' => 'page',
                'menu-item-object-id' => $page->ID,
                'menu-item-type' => 'post_type',
                'menu-item-status' => 'publish',
                'menu-item-url' => get_permalink($page->ID)
            );
            
            $parent_menu_item_id = wp_update_nav_menu_item($menu_id, 0, $menu_item_data);
            
            if ($parent_menu_item_id && !is_wp_error($parent_menu_item_id)) {
                $menu_items_created++;
                
                // If this page has children, add them as sub-menu items
                if ($has_children) {
                    $nested_args = array(
                        'parent' => $page->ID,
                        'sort_order' => 'ASC',
                        'sort_column' => 'menu_order, post_title',
                        'post_type' => 'page',
                        'post_status' => 'publish'
                    );
                    
                    $nested_pages = get_pages($nested_args);
                    
                    if (!empty($nested_pages)) {
                        foreach ($nested_pages as $subpage) {
                            $submenu_item_data = array(
                                'menu-item-title' => $subpage->post_title,
                                'menu-item-object' => 'page',
                                'menu-item-object-id' => $subpage->ID,
                                'menu-item-type' => 'post_type',
                                'menu-item-status' => 'publish',
                                'menu-item-url' => get_permalink($subpage->ID),
                                'menu-item-parent-id' => $parent_menu_item_id
                            );
                            
                            $submenu_item_id = wp_update_nav_menu_item($menu_id, 0, $submenu_item_data);
                            if ($submenu_item_id && !is_wp_error($submenu_item_id)) {
                                $menu_items_created++;
                            }
                        }
                    }
                }
            }
        }
    }
    
    error_log("ensure_department_menu_exists: Created department menu for {$dept_id} with {$additional_items_created} additional items and {$menu_items_created} page items");
    return $menu_id;
}

/**
 * Generate menus for all departments that have department_id set
 * 
 * This is a bulk function to create menus for all departments at once.
 * Useful for initial setup or when adding new departments.
 * 
 * @param bool $force_regenerate Whether to regenerate existing menus
 * @return array Array of results for each department processed
 */
function generate_all_department_menus($force_regenerate = false) {
    $results = array();
    
    // Get all pages that have department_id set
    $args = array(
        'post_type' => 'page',
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'department_id',
                'compare' => 'EXISTS'
            )
        ),
        'posts_per_page' => -1
    );
    
    $department_pages = get_posts($args);
    
    foreach ($department_pages as $page) {
        $dept_id = get_post_meta($page->ID, 'department_id', true);
        $menu_id = ensure_department_menu_exists($page->ID, $force_regenerate);
        
        $results[] = array(
            'page_id' => $page->ID,
            'page_title' => $page->post_title,
            'department_id' => $dept_id,
            'menu_id' => $menu_id,
            'success' => $menu_id !== false
        );
    }
    
    return $results;
}

/**
 * Get a department menu by department ID
 * 
 * @param string $department_id The department ID
 * @return WP_Term|false The menu object or false if not found
 */
function get_department_menu($department_id) {
    // First try to find by the new slug format
    $menus = wp_get_nav_menus();
    foreach ($menus as $menu) {
        if (strpos($menu->slug, "department_menu_") === 0 && strpos($menu->slug, "_{$department_id}") !== false) {
            return $menu;
        }
    }
    
    // Fallback to old format for backward compatibility
    $menu_slug = "department_menu_{$department_id}";
    return wp_get_nav_menu_object($menu_slug);
}

/**
 * Display a department menu
 * 
 * @param string $department_id The department ID
 * @param array $args Additional arguments for wp_nav_menu()
 * @return string|false The menu HTML or false if menu not found
 */
function display_department_menu($department_id, $args = array()) {
    $menu = get_department_menu($department_id);
    if (!$menu) {
        return false;
    }
    
    $default_args = array(
        'menu' => $menu->term_id,
        'container' => 'nav',
        'container_class' => 'department-menu',
        'menu_class' => 'department-menu-list',
        'echo' => false
    );
    
    $args = wp_parse_args($args, $default_args);
    
    return wp_nav_menu($args);
}

/**
 * Custom Walker for Department Menus with Accordion Toggles
 */
/* Removing custom walker to use standard WordPress menu system with hover dropdowns
class Department_Menu_Walker extends Walker_Nav_Menu {
    
    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $indent = ($depth) ? str_repeat("\t", $depth) : '';
        
        $li_attributes = '';
        $class_names = $value = '';
        
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;
        
        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
        $class_names = ' class="' . esc_attr($class_names) . '"';
        
        $id = apply_filters('nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args);
        $id = strlen($id) ? ' id="' . esc_attr($id) . '"' : '';
        
        $output .= $indent . '<li' . $id . $value . $class_names . $li_attributes . '>';
        
        $attributes = ! empty($item->attr_title) ? ' title="'  . esc_attr($item->attr_title) .'"' : '';
        $attributes .= ! empty($item->target)     ? ' target="' . esc_attr($item->target     ) .'"' : '';
        $attributes .= ! empty($item->xfn)        ? ' rel="'    . esc_attr($item->xfn        ) .'"' : '';
        $attributes .= ! empty($item->url)        ? ' href="'   . esc_attr($item->url        ) .'"' : '';
        
        $item_output = $args->before;
        $item_output .= '<a'. $attributes .'>';
        $item_output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;
        $item_output .= '</a>';
        
        // Add toggle button for items with children
        if (in_array('menu-item-has-children', $classes)) {
            $item_output .= '<a href="#" class="accordion-toggle" aria-label="Toggle submenu" tabindex="0">+</a>';
        }
        
        $item_output .= $args->after;
        
        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }
}
*/

/**
 * List all existing department menus for debugging
 * 
 * @return array Array of department menus with their details
 */
function list_all_department_menus() {
    $all_menus = wp_get_nav_menus();
    $department_menus = array();
    
    foreach ($all_menus as $menu) {
        // Check if this is a department menu (either old or new format)
        if (strpos($menu->slug, 'department_menu_') === 0) {
            $department_menus[] = array(
                'id' => $menu->term_id,
                'name' => $menu->name,
                'slug' => $menu->slug,
                'count' => $menu->count,
                'format' => strpos($menu->slug, 'department_menu_') === 0 && preg_match('/_\d+$/', $menu->slug) ? 'new' : 'old'
            );
        }
    }
    
    return $department_menus;
}

/**
 * Delete all existing department menus
 * 
 * This function finds and deletes all menus that match the department menu pattern
 * without affecting other menus like the main navigation.
 * 
 * @return array Array of deleted menu information
 */
function delete_all_department_menus() {
    $all_menus = wp_get_nav_menus();
    $deleted_menus = array();
    
    foreach ($all_menus as $menu) {
        // Check if this is a department menu by either:
        // 1. Slug starts with 'department_menu_'
        // 2. Display name starts with 'Department Menu -'
        $is_department_menu = false;
        $reason = '';
        
        if (strpos($menu->slug, 'department_menu_') === 0) {
            $is_department_menu = true;
            $reason = 'slug pattern';
        } elseif (strpos($menu->name, 'Department Menu -') === 0) {
            $is_department_menu = true;
            $reason = 'name pattern';
        }
        
        if ($is_department_menu) {
            error_log("delete_all_department_menus: Deleting department menu - {$menu->name} (ID: {$menu->term_id}, Slug: {$menu->slug}, Reason: {$reason})");
            
            $deleted = wp_delete_nav_menu($menu->term_id);
            if ($deleted) {
                $deleted_menus[] = array(
                    'id' => $menu->term_id,
                    'name' => $menu->name,
                    'slug' => $menu->slug,
                    'reason' => $reason,
                    'success' => true
                );
            } else {
                $deleted_menus[] = array(
                    'id' => $menu->term_id,
                    'name' => $menu->name,
                    'slug' => $menu->slug,
                    'reason' => $reason,
                    'success' => false,
                    'error' => 'Failed to delete menu'
                );
            }
        }
    }
    
    error_log("delete_all_department_menus: Deleted " . count($deleted_menus) . " department menus");
    return $deleted_menus;
}

/**
 * Reset and regenerate all department menus
 * 
 * This function deletes all existing department menus and then regenerates them
 * from scratch. This is useful when there are conflicts or when you want to
 * ensure all menus are using the latest format.
 * 
 * @return array Array of results for each department processed
 */
function reset_and_regenerate_all_department_menus() {
    // First, delete all existing department menus
    $deleted_menus = delete_all_department_menus();
    
    // Then regenerate all department menus
    $results = generate_all_department_menus(false); // false = don't force regenerate since we just deleted them
    
    // Combine the results
    $combined_results = array(
        'deleted_menus' => $deleted_menus,
        'regenerated_menus' => $results
    );
    
    return $combined_results;
}

/**
 * Temporary admin page for generating department menus
 * Remove this function after you're done testing
 */
function add_department_menu_generator_page() {
    add_management_page(
        'Generate Department Menus',
        'Generate Dept Menus',
        'manage_options',
        'generate-department-menus',
        'department_menu_generator_page'
    );
}
add_action('admin_menu', 'add_department_menu_generator_page');

function department_menu_generator_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }
    
    $message = '';
    $results = array();
    $deleted_menus = array();
    
    if (isset($_POST['generate_menus'])) {
        $force_regenerate = isset($_POST['force_regenerate']);
        $results = generate_all_department_menus($force_regenerate);
        
        $success_count = count(array_filter($results, function($r) { return $r['success']; }));
        $total_count = count($results);
        
        $message = "<div class='notice notice-success'><p>Generated {$success_count} out of {$total_count} department menus successfully!</p></div>";
    }
    
    if (isset($_POST['migrate_menus'])) {
        $results = migrate_department_menus_to_new_format();
        
        $migrated_count = count(array_filter($results, function($r) { return $r['action'] === 'migrated'; }));
        $already_new_count = count(array_filter($results, function($r) { return $r['action'] === 'already_new_format'; }));
        $total_count = count($results);
        
        $message = "<div class='notice notice-success'><p>Migration completed: {$migrated_count} menus migrated, {$already_new_count} already in new format, {$total_count} total processed.</p></div>";
    }
    
    if (isset($_POST['reset_and_regenerate'])) {
        $combined_results = reset_and_regenerate_all_department_menus();
        $deleted_menus = $combined_results['deleted_menus'];
        $results = $combined_results['regenerated_menus'];
        
        $deleted_count = count(array_filter($deleted_menus, function($d) { return $d['success']; }));
        $success_count = count(array_filter($results, function($r) { return $r['success']; }));
        $total_count = count($results);
        
        $message = "<div class='notice notice-success'><p>Reset and regenerate completed: {$deleted_count} menus deleted, {$success_count} out of {$total_count} department menus regenerated successfully!</p></div>";
    }
    
    // Get current department menus for display
    $current_menus = list_all_department_menus();
    
    ?>
    <div class="wrap">
        <h1>Generate Department Menus</h1>
        
        <?php if ($message): ?>
            <?php echo $message; ?>
        <?php endif; ?>
        
        <div class="card">
            <h2>Current Department Menus</h2>
            <?php if (!empty($current_menus)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Menu ID</th>
                            <th>Display Name</th>
                            <th>Slug</th>
                            <th>Items</th>
                            <th>Format</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($current_menus as $menu): ?>
                            <tr>
                                <td><?php echo esc_html($menu['id']); ?></td>
                                <td><?php echo esc_html($menu['name']); ?></td>
                                <td><code><?php echo esc_html($menu['slug']); ?></code></td>
                                <td><?php echo esc_html($menu['count']); ?></td>
                                <td>
                                    <?php if ($menu['format'] === 'new'): ?>
                                        <span style="color: green;">New Format</span>
                                    <?php else: ?>
                                        <span style="color: orange;">Old Format</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No department menus found.</p>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2>Reset and Regenerate All Department Menus</h2>
            <p><strong>⚠️ WARNING:</strong> This will delete ALL existing department menus and recreate them from scratch.</p>
            <p>This is useful when there are naming conflicts or when you want to ensure all menus use the latest format.</p>
            <p><strong>Safe:</strong> This will NOT affect other menus like your main navigation, footer menu, etc.</p>
            
            <form method="post">
                <p>
                    <input type="submit" name="reset_and_regenerate" class="button button-primary" value="Reset and Regenerate All Department Menus" onclick="return confirm('Are you sure you want to delete all department menus and regenerate them? This action cannot be undone.');">
                </p>
            </form>
        </div>
        
        <div class="card">
            <h2>Generate All Department Menus</h2>
            <p>This will create navigation menus for all departments that have a <code>department_id</code> set.</p>
            <p><strong>New Format:</strong> Menus will now be named "Department Menu - {Department Name}" for better user-friendliness.</p>
            
            <form method="post">
                <p>
                    <label>
                        <input type="checkbox" name="force_regenerate" value="1">
                        Force regenerate existing menus (will delete and recreate)
                    </label>
                </p>
                <p>
                    <input type="submit" name="generate_menus" class="button button-secondary" value="Generate Department Menus">
                </p>
            </form>
        </div>
        
        <div class="card">
            <h2>Migrate Existing Menus to New Format</h2>
            <p>If you have existing department menus with the old naming format, you can migrate them to the new user-friendly format.</p>
            <p>This will preserve all menu items while updating the menu names to "Department Menu - {Department Name}".</p>
            
            <form method="post">
                <p>
                    <input type="submit" name="migrate_menus" class="button button-secondary" value="Migrate Existing Menus">
                </p>
            </form>
        </div>
        
        <?php if (!empty($results)): ?>
            <div class="card">
                <h2>Results</h2>
                
                <?php if (!empty($deleted_menus)): ?>
                    <h3>Deleted Menus</h3>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Menu ID</th>
                                <th>Display Name</th>
                                <th>Slug</th>
                                <th>Reason</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($deleted_menus as $menu): ?>
                                <tr>
                                    <td><?php echo esc_html($menu['id']); ?></td>
                                    <td><?php echo esc_html($menu['name']); ?></td>
                                    <td><code><?php echo esc_html($menu['slug']); ?></code></td>
                                    <td>
                                        <?php if ($menu['reason'] === 'slug pattern'): ?>
                                            <span style="color: blue;">Slug Pattern</span>
                                        <?php elseif ($menu['reason'] === 'name pattern'): ?>
                                            <span style="color: orange;">Name Pattern</span>
                                        <?php else: ?>
                                            <?php echo esc_html($menu['reason']); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($menu['success']): ?>
                                            <span style="color: green;">✓ Deleted</span>
                                        <?php else: ?>
                                            <span style="color: red;">✗ Failed</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                
                <h3>Regenerated Menus</h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Page Title</th>
                            <th>Page ID</th>
                            <th>Department ID</th>
                            <th>Menu ID</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $result): ?>
                            <tr>
                                <td><?php echo esc_html($result['page_title']); ?></td>
                                <td><?php echo esc_html($result['page_id']); ?></td>
                                <td><?php echo esc_html($result['department_id']); ?></td>
                                <td>
                                    <?php 
                                    if (isset($result['new_menu_id'])) {
                                        echo esc_html($result['new_menu_id']);
                                    } elseif (isset($result['menu_id'])) {
                                        echo esc_html($result['menu_id']);
                                    } else {
                                        echo 'Failed';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($result['success']): ?>
                                        <span style="color: green;">✓ Success</span>
                                    <?php else: ?>
                                        <span style="color: red;">✗ Failed</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    if (isset($result['action'])) {
                                        switch ($result['action']) {
                                            case 'migrated':
                                                echo '<span style="color: blue;">Migrated (' . $result['items_copied'] . ' items)</span>';
                                                break;
                                            case 'already_new_format':
                                                echo '<span style="color: green;">Already New Format</span>';
                                                break;
                                            case 'migration_failed':
                                                echo '<span style="color: red;">Migration Failed</span>';
                                                break;
                                            case 'no_menu_found':
                                                echo '<span style="color: orange;">No Menu Found</span>';
                                                break;
                                            default:
                                                echo esc_html($result['action']);
                                        }
                                    } else {
                                        echo 'Generated';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <h2>Manual Generation</h2>
            <p>You can also generate a menu for a specific department by calling:</p>
            <code>ensure_department_menu_exists($page_id);</code>
            <p>Where <code>$page_id</code> is the ID of the department root page.</p>
        </div>
    </div>
    <?php
}

/**
 * Migrate existing department menus to the new naming format
 * 
 * This function finds existing menus with the old format and updates them
 * to use the new user-friendly naming while preserving their content.
 * 
 * @return array Array of migration results
 */
function migrate_department_menus_to_new_format() {
    $results = array();
    
    // Get all pages that have department_id set
    $args = array(
        'post_type' => 'page',
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'department_id',
                'compare' => 'EXISTS'
            )
        ),
        'posts_per_page' => -1
    );
    
    $department_pages = get_posts($args);
    
    foreach ($department_pages as $page) {
        $dept_id = get_post_meta($page->ID, 'department_id', true);
        $department_name = $page->post_title;
        
        // Check for old format menu
        $old_menu_slug = "department_menu_{$dept_id}";
        $old_menu = wp_get_nav_menu_object($old_menu_slug);
        
        // Check for new format menu
        $new_menu_slug = "department_menu_" . sanitize_title($department_name) . "_{$dept_id}";
        $new_menu = wp_get_nav_menu_object($new_menu_slug);
        
        if ($old_menu && !$new_menu) {
            // Migrate old menu to new format
            $menu_items = wp_get_nav_menu_items($old_menu->term_id);
            
            // Create new menu with user-friendly name
            $new_menu_id = wp_create_nav_menu($new_menu_slug);
            if (!is_wp_error($new_menu_id)) {
                // Set the user-friendly name
                wp_update_nav_menu_object($new_menu_id, array('menu-name' => "Department Menu - " . $department_name));
                
                // Copy all menu items
                $items_copied = 0;
                foreach ($menu_items as $item) {
                    $new_item_data = array(
                        'menu-item-title' => $item->title,
                        'menu-item-object' => $item->object,
                        'menu-item-object-id' => $item->object_id,
                        'menu-item-type' => $item->type,
                        'menu-item-status' => 'publish',
                        'menu-item-url' => $item->url,
                        'menu-item-parent-id' => $item->menu_item_parent
                    );
                    
                    $new_item_id = wp_update_nav_menu_item($new_menu_id, 0, $new_item_data);
                    if ($new_item_id && !is_wp_error($new_item_id)) {
                        $items_copied++;
                    }
                }
                
                // Delete old menu
                wp_delete_nav_menu($old_menu->term_id);
                
                $results[] = array(
                    'page_id' => $page->ID,
                    'page_title' => $page->post_title,
                    'department_id' => $dept_id,
                    'old_menu_id' => $old_menu->term_id,
                    'new_menu_id' => $new_menu_id,
                    'items_copied' => $items_copied,
                    'success' => true,
                    'action' => 'migrated'
                );
            } else {
                $results[] = array(
                    'page_id' => $page->ID,
                    'page_title' => $page->post_title,
                    'department_id' => $dept_id,
                    'success' => false,
                    'error' => 'Failed to create new menu',
                    'action' => 'migration_failed'
                );
            }
        } elseif ($new_menu) {
            $results[] = array(
                'page_id' => $page->ID,
                'page_title' => $page->post_title,
                'department_id' => $dept_id,
                'menu_id' => $new_menu->term_id,
                'success' => true,
                'action' => 'already_new_format'
            );
        } else {
            $results[] = array(
                'page_id' => $page->ID,
                'page_title' => $page->post_title,
                'department_id' => $dept_id,
                'success' => false,
                'error' => 'No existing menu found',
                'action' => 'no_menu_found'
            );
        }
    }
    
    return $results;
}

/**
 * Generate directory slug from department name
 * 
 * @param string $name Department name
 * @return string URL-friendly slug
 */
function generate_directory_slug($name) {
    // Convert department name to lowercase and replace spaces with hyphens
    $slug = sanitize_title($name);
    
    // Ensure uniqueness if the slug already exists
    $original_slug = $slug;
    $counter = 1;
    while (get_page_by_path($slug, OBJECT, 'directory_page')) {
        $slug = $original_slug . '-' . $counter;
        $counter++;
    }
    
    return $slug;
}


