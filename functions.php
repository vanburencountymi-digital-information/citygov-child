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
            return $current_id; // Return the page ID, not the department_id value
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
    // Get the department ID from the root page
    $dept_id = get_post_meta($department_root_id, 'department_id', true);
    if (!$dept_id) {
        error_log("No department_id found for page ID: {$department_root_id}");
        return false;
    }
    
    $menu_name = "Department Menu - " . get_the_title($department_root_id);
    $menu_slug = "department_menu_{$dept_id}";
    
    // Check if menu already exists
    $existing_menu = wp_get_nav_menu_object($menu_slug);
    
    if ($existing_menu && !$force_regenerate) {
        // Menu exists and we're not forcing regeneration
        return $existing_menu->term_id;
    }
    
    // If menu exists and we're forcing regeneration, delete it first
    if ($existing_menu && $force_regenerate) {
        wp_delete_nav_menu($existing_menu->term_id);
    }
    
    // Create new menu
    $menu_id = wp_create_nav_menu($menu_slug);
    if (is_wp_error($menu_id)) {
        error_log("Failed to create menu for department {$dept_id}: " . $menu_id->get_error_message());
        return false;
    }
    
    // Set menu name
    wp_update_nav_menu_object($menu_id, array('menu-name' => $menu_name));
    
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
    
    // Add some common department-specific menu items
    $additional_items = array(
        array(
            'title' => 'Contact Us',
            'url' => get_permalink($department_root_id) . '#contact',
            'type' => 'custom'
        ),
        array(
            'title' => 'Staff Directory',
            'url' => get_permalink($department_root_id) . '#staff',
            'type' => 'custom'
        ),
        array(
            'title' => 'Department Home',
            'url' => get_permalink($department_root_id),
            'type' => 'custom'
        )
    );
    
    foreach ($additional_items as $item) {
        $additional_item_data = array(
            'menu-item-title' => $item['title'],
            'menu-item-url' => $item['url'],
            'menu-item-type' => $item['type'],
            'menu-item-status' => 'publish'
        );
        
        $additional_item_id = wp_update_nav_menu_item($menu_id, 0, $additional_item_data);
        if ($additional_item_id && !is_wp_error($additional_item_id)) {
            $menu_items_created++;
        }
    }
    
    error_log("Created department menu for {$dept_id} with {$menu_items_created} items");
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
    
    if (isset($_POST['generate_menus'])) {
        $force_regenerate = isset($_POST['force_regenerate']);
        $results = generate_all_department_menus($force_regenerate);
        
        $success_count = count(array_filter($results, function($r) { return $r['success']; }));
        $total_count = count($results);
        
        $message = "<div class='notice notice-success'><p>Generated {$success_count} out of {$total_count} department menus successfully!</p></div>";
    }
    
    ?>
    <div class="wrap">
        <h1>Generate Department Menus</h1>
        
        <?php if ($message): ?>
            <?php echo $message; ?>
        <?php endif; ?>
        
        <div class="card">
            <h2>Generate All Department Menus</h2>
            <p>This will create navigation menus for all departments that have a <code>department_id</code> set.</p>
            
            <form method="post">
                <p>
                    <label>
                        <input type="checkbox" name="force_regenerate" value="1">
                        Force regenerate existing menus (will delete and recreate)
                    </label>
                </p>
                <p>
                    <input type="submit" name="generate_menus" class="button button-primary" value="Generate Department Menus">
                </p>
            </form>
        </div>
        
        <?php if (!empty($results)): ?>
            <div class="card">
                <h2>Results</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Page Title</th>
                            <th>Page ID</th>
                            <th>Department ID</th>
                            <th>Menu ID</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $result): ?>
                            <tr>
                                <td><?php echo esc_html($result['page_title']); ?></td>
                                <td><?php echo esc_html($result['page_id']); ?></td>
                                <td><?php echo esc_html($result['department_id']); ?></td>
                                <td><?php echo $result['menu_id'] ? esc_html($result['menu_id']) : 'Failed'; ?></td>
                                <td>
                                    <?php if ($result['success']): ?>
                                        <span style="color: green;">✓ Success</span>
                                    <?php else: ?>
                                        <span style="color: red;">✗ Failed</span>
                                    <?php endif; ?>
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


