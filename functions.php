<?php

function vbc_enqueue_custom_scripts() {
    // Only enqueue on the homepage
    if (is_front_page()) {
        wp_enqueue_script(
            'vbc-fix-swiper-conflict',
            get_stylesheet_directory_uri() . '/js/fix-swiper-conflict.js',
            array(), // add 'jquery' here if your script depends on it
            filemtime(get_stylesheet_directory() . '/js/fix-swiper-conflict.js'),
            true // load in footer
        );
    }
}
add_action('wp_enqueue_scripts', 'vbc_enqueue_custom_scripts');

function citygov_child_enqueue_styles() {
    // Load the parent stylesheet directly from the parent directory
    wp_enqueue_style('citygov-style', get_template_directory_uri() . '/style.css');

    // Then load the child stylesheet
    wp_enqueue_style('citygov-child-style', get_stylesheet_uri(), array('citygov-style'), wp_get_theme()->get('Version'));
}
add_action('wp_enqueue_scripts', 'citygov_child_enqueue_styles');

// Enqueue PDF replacement modal script
function enqueue_pdf_replace_modal_script() {
    if ( is_singular( 'dlp_document' ) && current_user_can( 'edit_posts' ) ) {
        wp_enqueue_script(
            'pdf-replace-modal',
            get_stylesheet_directory_uri() . '/js/pdf-replace-modal.js',
            array(),
            filemtime(get_stylesheet_directory() . '/js/pdf-replace-modal.js'),
            true
        );
        
        // Localize script with AJAX URL
        wp_localize_script('pdf-replace-modal', 'ajaxurl', admin_url('admin-ajax.php'));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_pdf_replace_modal_script');
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
            $page_class = 'subpage-item';
            $list_pages_html .= '<li class="' . $page_class . '">';
            $list_pages_html .= '<h4 class="subpage-title"><a href="' . get_permalink($page->ID) . '" title="' . esc_attr($page->post_title) . '">' . $page->post_title . '</a></h4>';
            
            // Optionally show excerpt for subpages
            if ($atts['show_content'] == 1) {
                if (!empty($page->post_excerpt)) {
                    $text_content = $page->post_excerpt;
                } else {
                    $text_content = $page->post_content;
                }
                
                if (!post_password_required($page)) {
                    $content = pagelist_unqprfx_parse_content($text_content, $atts['limit_content'], $atts['strip_tags'], $atts['strip_shortcodes'], $atts['more_tag']);
                    $content = do_shortcode($content);
                    $list_pages_html .= '<div class="subpage-content">' . $content . '</div>';
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
    
    // Also check if a menu with the same name exists (in case it was created manually)
    $all_menus = wp_get_nav_menus();
    $existing_menu_by_name = null;
    foreach ($all_menus as $menu) {
        if ($menu->name === $menu_name) {
            $existing_menu_by_name = $menu;
            break;
        }
    }
    
    // Determine which existing menu to use (if any)
    $menu_to_update = null;
    if ($existing_menu) {
        $menu_to_update = $existing_menu;
    } elseif ($existing_menu_by_name) {
        $menu_to_update = $existing_menu_by_name;
    }
    
    if ($menu_to_update && !$force_regenerate) {
        // Menu exists and we're not forcing regeneration, but we should still update it
        error_log("ensure_department_menu_exists: Found existing menu: {$menu_to_update->name} (ID: {$menu_to_update->term_id}) - will update items");
        $menu_id = $menu_to_update->term_id;
    } else {
        // Need to create a new menu or force regenerate
        if ($menu_to_update && $force_regenerate) {
            error_log("ensure_department_menu_exists: Deleting existing menu for regeneration: {$menu_to_update->name}");
            wp_delete_nav_menu($menu_to_update->term_id);
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
    }
    
    // Now we have a menu_id (either existing or new) - let's update/add the menu items
    error_log("ensure_department_menu_exists: Updating menu items for menu ID: {$menu_id}");
    
    // Get existing menu items to check for duplicates
    $existing_items = wp_get_nav_menu_items($menu_id);
    $existing_item_ids = array();
    if (!empty($existing_items)) {
        error_log("ensure_department_menu_exists: Found " . count($existing_items) . " existing menu items");
        foreach ($existing_items as $item) {
            if ($item->object === 'page') {
                $existing_item_ids[] = $item->object_id;
            }
        }
        error_log("ensure_department_menu_exists: Existing page IDs: " . implode(', ', $existing_item_ids));
    }
    
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
        // Check if this additional item already exists (by URL)
        $item_exists = false;
        foreach ($existing_items as $existing_item) {
            if ($existing_item->url === $item['url']) {
                $item_exists = true;
                error_log("ensure_department_menu_exists: Additional item '{$item['title']}' already exists");
                break;
            }
        }
        
        if (!$item_exists) {
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
                error_log("ensure_department_menu_exists: Added additional item '{$item['title']}'");
            }
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
            // Check if this page already exists in the menu
            if (in_array($page->ID, $existing_item_ids)) {
                error_log("ensure_department_menu_exists: Page '{$page->post_title}' (ID: {$page->ID}) already exists in menu, skipping");
                continue;
            }
            
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
                error_log("ensure_department_menu_exists: Added page '{$page->post_title}' (ID: {$page->ID})");
                
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
                            // Check if this nested page already exists in the menu
                            if (in_array($subpage->ID, $existing_item_ids)) {
                                error_log("ensure_department_menu_exists: Nested page '{$subpage->post_title}' (ID: {$subpage->ID}) already exists in menu, skipping");
                                continue;
                            }
                            
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
                                error_log("ensure_department_menu_exists: Added nested page '{$subpage->post_title}' (ID: {$subpage->ID})");
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
    
    if (isset($_POST['regenerate_single_menu'])) {
        // Verify nonce
        if (!wp_verify_nonce($_POST['regenerate_menu_nonce'], 'regenerate_single_menu')) {
            $message = "<div class='notice notice-error'><p>Security check failed. Please try again.</p></div>";
        } else {
            $page_id = intval($_POST['department_page_id']);
            $force_regenerate = isset($_POST['force_regenerate_single']);
            
            if ($page_id > 0) {
                $page = get_post($page_id);
                if ($page && $page->post_type === 'page') {
                    $dept_id = get_post_meta($page_id, 'department_id', true);
                    if (!empty($dept_id)) {
                        $menu_id = ensure_department_menu_exists($page_id, $force_regenerate);
                        
                        if ($menu_id !== false) {
                            $action_text = $force_regenerate ? 'regenerated' : 'updated';
                            $message = "<div class='notice notice-success'><p>Successfully {$action_text} menu for department: <strong>{$page->post_title}</strong> (Menu ID: {$menu_id})</p></div>";
                        } else {
                            $message = "<div class='notice notice-error'><p>Failed to regenerate menu for department: <strong>{$page->post_title}</strong></p></div>";
                        }
                    } else {
                        $message = "<div class='notice notice-error'><p>Selected page does not have a department_id set.</p></div>";
                    }
                } else {
                    $message = "<div class='notice notice-error'><p>Invalid page selected.</p></div>";
                }
            } else {
                $message = "<div class='notice notice-error'><p>Please select a department from the dropdown.</p></div>";
            }
        }
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
            <p>Select a department from the dropdown below and click "Regenerate Menu" to update its navigation menu. This is useful when you've made changes to the department's subpages.</p>
            
            <?php
            // Get all department pages for the dropdown
            $department_args = array(
                'post_type' => 'page',
                'post_status' => 'publish',
                'meta_query' => array(
                    array(
                        'key' => 'department_id',
                        'compare' => 'EXISTS'
                    )
                ),
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC'
            );
            $department_pages = get_posts($department_args);
            ?>
            
            <?php if (!empty($department_pages)): ?>
                <form method="post" id="manual-menu-regeneration-form">
                    <?php wp_nonce_field('regenerate_single_menu', 'regenerate_menu_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="department_page_id">Select Department:</label>
                            </th>
                            <td>
                                <select name="department_page_id" id="department_page_id" style="min-width: 300px;">
                                    <option value="">-- Select a Department --</option>
                                    <?php foreach ($department_pages as $page): ?>
                                        <?php 
                                        $dept_id = get_post_meta($page->ID, 'department_id', true);
                                        $menu_exists = get_department_menu($dept_id);
                                        $menu_status = $menu_exists ? ' (Menu exists)' : ' (No menu)';
                                        ?>
                                        <option value="<?php echo esc_attr($page->ID); ?>" data-dept-id="<?php echo esc_attr($dept_id); ?>">
                                            <?php echo esc_html($page->post_title); ?> - ID: <?php echo esc_html($page->ID); ?> - Dept: <?php echo esc_html($dept_id); ?><?php echo esc_html($menu_status); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="force_regenerate_single">Force Regenerate:</label>
                            </th>
                            <td>
                                <input type="checkbox" name="force_regenerate_single" id="force_regenerate_single" value="1">
                                <label for="force_regenerate_single">Delete existing menu and recreate from scratch</label>
                            </td>
                        </tr>
                    </table>
                    
                    <p>
                        <button type="submit" name="regenerate_single_menu" class="button button-primary" id="regenerate-single-menu-btn">
                            Regenerate Menu
                        </button>
                        <span id="regeneration-status" style="margin-left: 10px; display: none;"></span>
                    </p>
                </form>
                
                <div id="regeneration-results" style="margin-top: 20px; display: none;"></div>
            <?php else: ?>
                <p><em>No department pages found. Make sure you have pages with a <code>department_id</code> meta field set.</em></p>
            <?php endif; ?>
            
            <hr style="margin: 20px 0;">
            <p><strong>Programmatic Usage:</strong> You can also generate a menu for a specific department by calling:</p>
            <code>ensure_department_menu_exists($page_id);</code>
            <p>Where <code>$page_id</code> is the ID of the department root page.</p>
        </div>
    </div>
    
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Handle manual menu regeneration form
        $('#manual-menu-regeneration-form').on('submit', function(e) {
            var selectedDept = $('#department_page_id').val();
            if (!selectedDept) {
                e.preventDefault();
                alert('Please select a department from the dropdown.');
                return false;
            }
            
            // Show loading state
            $('#regenerate-single-menu-btn').prop('disabled', true).text('Regenerating...');
            $('#regeneration-status').show().html('<span style="color: blue;">Processing...</span>');
        });
        
        // Update status when department is selected
        $('#department_page_id').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var deptId = selectedOption.data('dept-id');
            var pageTitle = selectedOption.text();
            
            if (deptId) {
                $('#regeneration-status').show().html('<span style="color: green;">Ready to regenerate menu for: ' + pageTitle + '</span>');
            } else {
                $('#regeneration-status').hide();
            }
        });
        
        // Add AJAX alternative (optional - can be used for future enhancements)
        function regenerateMenuAjax(pageId, forceRegenerate) {
            var data = {
                action: 'regenerate_department_menu',
                page_id: pageId,
                force_regenerate: forceRegenerate,
                regenerate_menu_nonce: $('#regenerate_menu_nonce').val()
            };
            
            $.post(ajaxurl, data, function(response) {
                try {
                    var result = JSON.parse(response);
                    if (result.success) {
                        $('#regeneration-status').html('<span style="color: green;">✓ ' + result.message + '</span>');
                    } else {
                        $('#regeneration-status').html('<span style="color: red;">✗ ' + result.message + '</span>');
                    }
                } catch (e) {
                    $('#regeneration-status').html('<span style="color: red;">✗ Error processing response</span>');
                }
                
                // Reset button
                $('#regenerate-single-menu-btn').prop('disabled', false).text('Regenerate Menu');
            }).fail(function() {
                $('#regeneration-status').html('<span style="color: red;">✗ Network error occurred</span>');
                $('#regenerate-single-menu-btn').prop('disabled', false).text('Regenerate Menu');
            });
        }
    });
    </script>
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

/**
 * AJAX handler for replacing PDF documents
 */
function handle_replace_pdf_document() {
    // Check nonce for security
    if (!wp_verify_nonce($_POST['pdf_replace_nonce'], 'replace_pdf_nonce')) {
        wp_die(json_encode(array('success' => false, 'data' => 'Security check failed')));
    }
    
    // Check user permissions
    if (!current_user_can('edit_posts')) {
        wp_die(json_encode(array('success' => false, 'data' => 'Insufficient permissions')));
    }
    
    // Check if file was uploaded
    if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
        wp_die(json_encode(array('success' => false, 'data' => 'No file uploaded or upload error')));
    }
    
    $file = $_FILES['pdf_file'];
    $post_id = intval($_POST['post_id']);
    $new_title = sanitize_text_field($_POST['pdf_title']);
    
    // Validate post exists and is a document
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'dlp_document') {
        wp_die(json_encode(array('success' => false, 'data' => 'Invalid document')));
    }
    
    // Validate file type
    $file_type = wp_check_filetype($file['name']);
    if ($file_type['type'] !== 'application/pdf') {
        wp_die(json_encode(array('success' => false, 'data' => 'Only PDF files are allowed')));
    }
    
    // Validate file size (10MB limit)
    if ($file['size'] > 10 * 1024 * 1024) {
        wp_die(json_encode(array('success' => false, 'data' => 'File size must be less than 10MB')));
    }
    
    // Get the current document object
    $document = dlp_get_document($post_id);
    if (!$document) {
        wp_die(json_encode(array('success' => false, 'data' => 'Could not load document')));
    }
    
    // Verify this is a valid document
    if (!is_object($document) || !method_exists($document, 'get_download_url')) {
        wp_die(json_encode(array('success' => false, 'data' => 'Invalid document object')));
    }
    
    // Debug: Log all post meta to understand Document Library Pro structure
    $all_post_meta = get_post_meta($post_id);
    error_log('Document Library Pro Debug - All post meta for post ' . $post_id . ': ' . print_r($all_post_meta, true));
    
    // Get current file ID from post meta (this is what Document Library Pro actually uses)
    $current_file_id = get_post_meta($post_id, '_dlp_attached_file_id', true);
    error_log('Document Library Pro Debug - Current file ID: ' . $current_file_id);
    
    // Debug: Check what methods are available on the document object
    if (is_object($document)) {
        $methods = get_class_methods($document);
        error_log('Document Library Pro Debug - Available methods: ' . print_r($methods, true));
        
        // Try to get download URL to see how it works
        if (method_exists($document, 'get_download_url')) {
            $download_url = $document->get_download_url();
            error_log('Document Library Pro Debug - Download URL: ' . $download_url);
        }
    }
    
    // Upload the new file
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    
    $upload = wp_handle_upload($file, array('test_form' => false));
    
    if (isset($upload['error'])) {
        wp_die(json_encode(array('success' => false, 'data' => 'Upload failed: ' . $upload['error'])));
    }
    
    // Create attachment post
    $attachment = array(
        'post_title' => $new_title ?: basename($upload['file']),
        'post_content' => '',
        'post_status' => 'inherit',
        'post_mime_type' => $upload['type']
    );
    
    $attachment_id = wp_insert_attachment($attachment, $upload['file'], $post_id);
    
    if (is_wp_error($attachment_id)) {
        wp_die(json_encode(array('success' => false, 'data' => 'Failed to create attachment')));
    }
    
    // Generate attachment metadata
    $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
    wp_update_attachment_metadata($attachment_id, $attachment_data);
    
    // Debug: Log the new attachment ID
    error_log('Document Library Pro Debug - New attachment ID: ' . $attachment_id);
    
    // Use the document object's set_file_id method to properly update the file
    if (method_exists($document, 'set_file_id')) {
        $document->set_file_id($attachment_id);
        error_log('Document Library Pro Debug - Set file ID via document object');
    }
    
    // Update the correct post meta that Document Library Pro uses
    $update_result = update_post_meta($post_id, '_dlp_attached_file_id', $attachment_id);
    error_log('Document Library Pro Debug - Update _dlp_attached_file_id result: ' . ($update_result ? 'true' : 'false'));
    
    // Also update the attachment_id meta for consistency
    update_post_meta($post_id, '_dlp_attachment_id', $attachment_id);
    
    // Update post title if provided
    if ($new_title) {
        wp_update_post(array(
            'ID' => $post_id,
            'post_title' => $new_title
        ));
    }
    
    // Delete the old attachment if it exists and is different
    if ($current_file_id && $current_file_id !== $attachment_id) {
        $delete_result = wp_delete_attachment($current_file_id, true);
        error_log('Document Library Pro Debug - Delete old attachment result: ' . ($delete_result ? 'true' : 'false'));
    }
    
    // Debug: Log final post meta after update
    $final_post_meta = get_post_meta($post_id);
    error_log('Document Library Pro Debug - Final post meta: ' . print_r($final_post_meta, true));
    
    // Refresh the document object to ensure it has the new file
    $updated_document = dlp_get_document($post_id);
    if ($updated_document && method_exists($updated_document, 'get_download_url')) {
        $new_download_url = $updated_document->get_download_url();
        error_log('Document Library Pro Debug - New download URL: ' . $new_download_url);
    }
    
    wp_die(json_encode(array('success' => true, 'data' => 'PDF replaced successfully')));
}
add_action('wp_ajax_replace_pdf_document', 'handle_replace_pdf_document');

/**
 * AJAX handler for regenerating individual department menus
 */
function handle_regenerate_department_menu() {
    // Check nonce for security
    if (!wp_verify_nonce($_POST['regenerate_menu_nonce'], 'regenerate_single_menu')) {
        wp_die(json_encode(array('success' => false, 'message' => 'Security check failed')));
    }
    
    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_die(json_encode(array('success' => false, 'message' => 'Insufficient permissions')));
    }
    
    $page_id = intval($_POST['page_id']);
    $force_regenerate = isset($_POST['force_regenerate']) && $_POST['force_regenerate'] === 'true';
    
    if ($page_id <= 0) {
        wp_die(json_encode(array('success' => false, 'message' => 'Invalid page ID')));
    }
    
    $page = get_post($page_id);
    if (!$page || $page->post_type !== 'page') {
        wp_die(json_encode(array('success' => false, 'message' => 'Invalid page')));
    }
    
    $dept_id = get_post_meta($page_id, 'department_id', true);
    if (empty($dept_id)) {
        wp_die(json_encode(array('success' => false, 'message' => 'Page does not have a department_id set')));
    }
    
    $menu_id = ensure_department_menu_exists($page_id, $force_regenerate);
    
    if ($menu_id !== false) {
        $action_text = $force_regenerate ? 'regenerated' : 'updated';
        $message = "Successfully {$action_text} menu for department: {$page->post_title} (Menu ID: {$menu_id})";
        wp_die(json_encode(array('success' => true, 'message' => $message, 'menu_id' => $menu_id)));
    } else {
        wp_die(json_encode(array('success' => false, 'message' => "Failed to regenerate menu for department: {$page->post_title}")));
    }
}
add_action('wp_ajax_regenerate_department_menu', 'handle_regenerate_department_menu');

/**
 * Add CSV export functionality to Broken Link Checker plugin
 */
function citygov_add_blc_csv_export() {
    // Only run on the broken link checker admin page
    if (!isset($_GET['page']) || $_GET['page'] !== 'blc_local') {
        return;
    }
    
    // Add export button to the admin page
    add_action('admin_footer', 'citygov_blc_export_button');
    
    // Handle CSV export
    if (isset($_GET['blc_export_csv']) && wp_verify_nonce($_GET['_wpnonce'], 'blc_export_csv')) {
        citygov_export_blc_csv();
    }
}
add_action('admin_init', 'citygov_add_blc_csv_export');

/**
 * Add export button to the broken links page
 */
function citygov_blc_export_button() {
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Add export button to the actions area
        var exportButton = '<a href="<?php echo wp_nonce_url(admin_url('admin.php?page=blc_local&blc_export_csv=1'), 'blc_export_csv'); ?>" class="button button-secondary" style="margin-left: 10px;">Export to CSV</a>';
        
        // Find the actions area and add our button
        $('.sui-actions-right').append(exportButton);
    });
    </script>
    <?php
}

/**
 * Export broken links to CSV
 */
function citygov_export_blc_csv() {
    global $wpdb;
    
    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    
    // Get all broken links with page information
    $query = "
        SELECT 
            l.link_id,
            l.url,
            l.final_url,
            l.http_code,
            l.status_code,
            l.status_text,
            l.broken,
            l.warning,
            l.redirect_count,
            l.last_check,
            l.last_success,
            l.check_count,
            l.request_duration,
            l.dismissed,
            l.first_failure,
            l.log,
            i.container_id,
            i.container_type,
            i.link_text,
            i.raw_url,
            p.post_title,
            p.post_type,
            p.guid
        FROM {$wpdb->prefix}blc_links l
        LEFT JOIN {$wpdb->prefix}blc_instances i ON l.link_id = i.link_id
        LEFT JOIN {$wpdb->posts} p ON i.container_id = p.ID AND i.container_type = 'post'
        WHERE l.broken = 1 OR l.warning = 1
        ORDER BY l.last_check DESC
    ";
    
    $links = $wpdb->get_results($query, ARRAY_A);
    
    if (empty($links)) {
        wp_die('No broken links found to export');
    }
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="broken-links-' . date('Y-m-d-H-i-s') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    $headers = array(
        'Link ID',
        'URL',
        'Final URL',
        'HTTP Code',
        'Status Code',
        'Status Text',
        'Broken',
        'Warning',
        'Redirect Count',
        'Last Check',
        'Last Success',
        'Check Count',
        'Request Duration',
        'Dismissed',
        'First Failure',
        'Log',
        'Page ID',
        'Page Type',
        'Page Title',
        'Page URL',
        'Link Text',
        'Raw URL'
    );
    
    fputcsv($output, $headers);
    
    // Add data rows
    foreach ($links as $link) {
        $row = array(
            $link['link_id'],
            $link['url'],
            $link['final_url'],
            $link['http_code'],
            $link['status_code'],
            $link['status_text'],
            $link['broken'] ? 'Yes' : 'No',
            $link['warning'] ? 'Yes' : 'No',
            $link['redirect_count'],
            $link['last_check'],
            $link['last_success'],
            $link['check_count'],
            $link['request_duration'],
            $link['dismissed'] ? 'Yes' : 'No',
            $link['first_failure'],
            $link['log'],
            $link['container_id'] ?: '',
            $link['container_type'] ?: '',
            $link['post_title'] ?: '',
            $link['guid'] ?: '',
            $link['link_text'] ?: '',
            $link['raw_url'] ?: ''
        );
        
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

/**
 * Add functionality to detect missing subpages on admin menu page
 */
function add_department_menu_missing_pages_notice() {
    global $pagenow;
    
    // Only run on the nav-menus.php page
    if ($pagenow !== 'nav-menus.php') {
        return;
    }
    
    // Get the current menu being edited
    $nav_menu_selected_id = isset($_REQUEST['menu']) ? (int) $_REQUEST['menu'] : 0;
    if (!$nav_menu_selected_id) {
        return;
    }
    
    // Check if this is a department menu
    $menu = wp_get_nav_menu_object($nav_menu_selected_id);
    if (!$menu) {
        return;
    }
    
    // Check if this is a department menu by slug pattern
    if (strpos($menu->slug, 'department_menu_') !== 0) {
        return;
    }
    
    // Extract department ID from menu slug
    $dept_id = null;
    if (preg_match('/department_menu_.*_(\d+)$/', $menu->slug, $matches)) {
        $dept_id = $matches[1];
    }
    
    if (!$dept_id) {
        return;
    }
    
    // Find the department root page
    $department_pages = get_posts(array(
        'post_type' => 'page',
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'department_id',
                'value' => $dept_id,
                'compare' => '='
            )
        ),
        'posts_per_page' => 1
    ));
    
    if (empty($department_pages)) {
        return;
    }
    
    $department_root = $department_pages[0];
    
    // Get current menu items
    $menu_items = wp_get_nav_menu_items($nav_menu_selected_id);
    $existing_page_ids = array();
    foreach ($menu_items as $item) {
        if ($item->object === 'page') {
            $existing_page_ids[] = $item->object_id;
        }
    }
    
    // Get all subpages of the department
    $subpages = get_pages(array(
        'parent' => $department_root->ID,
        'sort_order' => 'ASC',
        'sort_column' => 'menu_order, post_title',
        'hierarchical' => 0,
        'post_type' => 'page',
        'post_status' => 'publish'
    ));
    
    if ($subpages === false) {
        $subpages = array();
    }
    
    // Find missing subpages
    $missing_subpages = array();
    foreach ($subpages as $subpage) {
        if (!in_array($subpage->ID, $existing_page_ids)) {
            $missing_subpages[] = $subpage;
        }
    }
    
    // Also check for missing nested subpages
    foreach ($subpages as $subpage) {
        $nested_pages = get_pages(array(
            'parent' => $subpage->ID,
            'sort_order' => 'ASC',
            'sort_column' => 'menu_order, post_title',
            'post_type' => 'page',
            'post_status' => 'publish'
        ));
        
        if ($nested_pages !== false) {
            foreach ($nested_pages as $nested_page) {
                if (!in_array($nested_page->ID, $existing_page_ids)) {
                    $missing_subpages[] = $nested_page;
                }
            }
        }
    }
    
    // If there are missing subpages, show notice
    if (!empty($missing_subpages)) {
        add_action('admin_notices', function() use ($missing_subpages, $nav_menu_selected_id, $department_root) {
            $missing_count = count($missing_subpages);
            $missing_titles = array();
            foreach ($missing_subpages as $page) {
                $missing_titles[] = $page->post_title;
            }
            
            $nonce = wp_create_nonce('add_missing_subpages');
            $add_url = admin_url('admin-ajax.php?action=add_missing_subpages&menu_id=' . $nav_menu_selected_id . '&_wpnonce=' . $nonce);
            
            ?>
            <div class="notice notice-warning is-dismissible department-menu-notice">
                <p>
                    <strong>Department Menu Update Available</strong><br>
                    Found <?php echo $missing_count; ?> new subpage<?php echo $missing_count > 1 ? 's' : ''; ?> for 
                    <strong><?php echo esc_html($department_root->post_title); ?></strong> that are not in this menu:
                    <em><?php echo esc_html(implode(', ', $missing_titles)); ?></em>
                </p>
                <p>
                    <a href="<?php echo esc_url($add_url); ?>" class="button button-primary">
                        Add Missing Subpages to Menu
                    </a>
                    <a href="<?php echo esc_url(admin_url('nav-menus.php?menu=' . $nav_menu_selected_id)); ?>" class="button">
                        Continue Editing Menu
                    </a>
                </p>
            </div>
            <?php
        });
    }
}
add_action('admin_init', 'add_department_menu_missing_pages_notice');

/**
 * AJAX handler for adding missing subpages to department menu
 */
function handle_add_missing_subpages() {
    // Check nonce for security
    if (!wp_verify_nonce($_GET['_wpnonce'], 'add_missing_subpages')) {
        wp_die('Security check failed');
    }
    
    // Check user permissions
    if (!current_user_can('edit_theme_options')) {
        wp_die('Insufficient permissions');
    }
    
    $menu_id = intval($_GET['menu_id']);
    if (!$menu_id) {
        wp_die('Invalid menu ID');
    }
    
    $menu = wp_get_nav_menu_object($menu_id);
    if (!$menu) {
        wp_die('Menu not found');
    }
    
    // Extract department ID from menu slug
    $dept_id = null;
    if (preg_match('/department_menu_.*_(\d+)$/', $menu->slug, $matches)) {
        $dept_id = $matches[1];
    }
    
    if (!$dept_id) {
        wp_die('Not a department menu');
    }
    
    // Find the department root page
    $department_pages = get_posts(array(
        'post_type' => 'page',
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'department_id',
                'value' => $dept_id,
                'compare' => '='
            )
        ),
        'posts_per_page' => 1
    ));
    
    if (empty($department_pages)) {
        wp_die('Department not found');
    }
    
    $department_root = $department_pages[0];
    
    // Get current menu items
    $menu_items = wp_get_nav_menu_items($menu_id);
    $existing_page_ids = array();
    $parent_menu_items = array(); // Track parent menu items for nested pages
    
    foreach ($menu_items as $item) {
        if ($item->object === 'page') {
            $existing_page_ids[] = $item->object_id;
            // Store parent menu items for nested pages
            $parent_menu_items[$item->object_id] = $item->ID;
        }
    }
    
    // Get all subpages of the department
    $subpages = get_pages(array(
        'parent' => $department_root->ID,
        'sort_order' => 'ASC',
        'sort_column' => 'menu_order, post_title',
        'hierarchical' => 0,
        'post_type' => 'page',
        'post_status' => 'publish'
    ));
    
    if ($subpages === false) {
        $subpages = array();
    }
    
    $added_count = 0;
    $added_items = array();
    
    // Add missing subpages
    foreach ($subpages as $subpage) {
        if (!in_array($subpage->ID, $existing_page_ids)) {
            $menu_item_data = array(
                'menu-item-title' => $subpage->post_title,
                'menu-item-object' => 'page',
                'menu-item-object-id' => $subpage->ID,
                'menu-item-type' => 'post_type',
                'menu-item-status' => 'publish',
                'menu-item-url' => get_permalink($subpage->ID)
            );
            
            $menu_item_id = wp_update_nav_menu_item($menu_id, 0, $menu_item_data);
            if ($menu_item_id && !is_wp_error($menu_item_id)) {
                $added_count++;
                $added_items[] = $subpage->post_title;
                $parent_menu_items[$subpage->ID] = $menu_item_id; // Store for nested pages
            }
        }
    }
    
    // Add missing nested subpages
    foreach ($subpages as $subpage) {
        $nested_pages = get_pages(array(
            'parent' => $subpage->ID,
            'sort_order' => 'ASC',
            'sort_column' => 'menu_order, post_title',
            'post_type' => 'page',
            'post_status' => 'publish'
        ));
        
        if ($nested_pages !== false) {
            foreach ($nested_pages as $nested_page) {
                if (!in_array($nested_page->ID, $existing_page_ids)) {
                    $parent_menu_item_id = isset($parent_menu_items[$subpage->ID]) ? $parent_menu_items[$subpage->ID] : 0;
                    
                    $menu_item_data = array(
                        'menu-item-title' => $nested_page->post_title,
                        'menu-item-object' => 'page',
                        'menu-item-object-id' => $nested_page->ID,
                        'menu-item-type' => 'post_type',
                        'menu-item-status' => 'publish',
                        'menu-item-url' => get_permalink($nested_page->ID),
                        'menu-item-parent-id' => $parent_menu_item_id
                    );
                    
                    $menu_item_id = wp_update_nav_menu_item($menu_id, 0, $menu_item_data);
                    if ($menu_item_id && !is_wp_error($menu_item_id)) {
                        $added_count++;
                        $added_items[] = $nested_page->post_title;
                    }
                }
            }
        }
    }
    
    // Redirect back to menu page with success message
    $redirect_url = admin_url('nav-menus.php?menu=' . $menu_id . '&subpages_added=' . $added_count . '&added_items=' . urlencode(implode(', ', $added_items)));
    wp_redirect($redirect_url);
    exit;
}
add_action('wp_ajax_add_missing_subpages', 'handle_add_missing_subpages');

/**
 * Show success message after adding missing subpages
 */
function show_subpages_added_notice() {
    if (isset($_GET['subpages_added']) && $_GET['subpages_added'] > 0) {
        $added_count = intval($_GET['subpages_added']);
        $added_items = isset($_GET['added_items']) ? urldecode($_GET['added_items']) : '';
        
        ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <strong>Success!</strong> Added <?php echo $added_count; ?> new subpage<?php echo $added_count > 1 ? 's' : ''; ?> to the menu.
                <?php if ($added_items): ?>
                    <br>Added: <em><?php echo esc_html($added_items); ?></em>
                <?php endif; ?>
            </p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'show_subpages_added_notice');

/**
 * Add CSS styling for department menu notices
 */
function add_department_menu_notice_styles() {
    global $pagenow;
    
    // Only add styles on the nav-menus.php page
    if ($pagenow !== 'nav-menus.php') {
        return;
    }
    
    ?>
    <style type="text/css">
    .department-menu-notice {
        border-left-color: #ffb900 !important;
        background-color: #fff8e5;
    }
    .department-menu-notice .button {
        margin-right: 10px;
    }
    .department-menu-notice p {
        margin: 0.5em 0;
    }
    .department-menu-notice strong {
        color: #d63638;
    }
    </style>
    <?php
}
add_action('admin_head', 'add_department_menu_notice_styles');

/**
 * Add admin page for fixing invalid HTML blocks
 */
function add_html_block_fixer_page() {
    add_management_page(
        'Fix Invalid HTML Blocks',
        'Fix HTML Blocks',
        'manage_options',
        'fix-html-blocks',
        'html_block_fixer_page'
    );
}
add_action('admin_menu', 'add_html_block_fixer_page');

/**
 * Admin page for fixing invalid HTML blocks
 */
function html_block_fixer_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }
    
    $message = '';
    $results = array();
    
    if (isset($_POST['fix_html_blocks'])) {
        // Verify nonce
        if (!wp_verify_nonce($_POST['fix_html_nonce'], 'fix_html_blocks')) {
            $message = "<div class='notice notice-error'><p>Security check failed. Please try again.</p></div>";
        } else {
            $dry_run = isset($_POST['dry_run']);
            $post_types = isset($_POST['post_types']) ? $_POST['post_types'] : array('post', 'page');
            $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 0;
            
            $results = fix_invalid_html_blocks($dry_run, $post_types, $limit);
            
            if ($dry_run) {
                $message = "<div class='notice notice-info'><p>Dry run completed: Found {$results['found_issues']} posts with HTML issues that would be fixed.</p></div>";
            } else {
                $message = "<div class='notice notice-success'><p>Successfully fixed {$results['fixed_count']} out of {$results['total_count']} posts!</p></div>";
            }
        }
    }
    
    if (isset($_POST['fix_single_page_html'])) {
        // Verify nonce
        if (!wp_verify_nonce($_POST['fix_single_page_nonce'], 'fix_single_page_html')) {
            $message = "<div class='notice notice-error'><p>Security check failed. Please try again.</p></div>";
        } else {
            $page_id = intval($_POST['single_page_id']);
            $dry_run = isset($_POST['single_page_dry_run']);
            
            if ($page_id > 0) {
                $single_results = fix_single_page_html_blocks($page_id, $dry_run);
                
                if ($dry_run) {
                    $message = "<div class='notice notice-info'><p>Single page dry run completed: " . ($single_results['issues_found'] > 0 ? "Found {$single_results['issues_found']} HTML issues that would be fixed." : "No HTML issues found.") . "</p></div>";
                } else {
                    $message = "<div class='notice notice-success'><p>Successfully fixed HTML issues on the selected page!</p></div>";
                }
                
                $results = $single_results;
            } else {
                $message = "<div class='notice notice-error'><p>Please select a page to test.</p></div>";
            }
        }
    }
    
    ?>
    <div class="wrap">
        <h1>Fix Invalid HTML Blocks</h1>
        
        <div class="card">
            <h2>About This Tool</h2>
            <p>This tool fixes common HTML nesting issues that cause Gutenberg block validation errors, such as:</p>
            <ul>
                <li><code>&lt;p&gt;&lt;h2&gt;Heading&lt;/h2&gt;&lt;/p&gt;</code> → <code>&lt;h2&gt;Heading&lt;/h2&gt;</code></li>
                <li><code>&lt;p&gt;&lt;ul&gt;...&lt;/ul&gt;&lt;/p&gt;</code> → <code>&lt;ul&gt;...&lt;/ul&gt;</code></li>
                <li><code>&lt;p&gt;&lt;div&gt;...&lt;/div&gt;&lt;/p&gt;</code> → <code>&lt;div&gt;...&lt;/div&gt;</code></li>
                <li><code>&lt;p&gt;&lt;img&gt;&lt;/p&gt;</code> → <code>&lt;img&gt;</code></li>
            </ul>
            <p><strong>⚠️ Warning:</strong> Always backup your database before running this tool!</p>
        </div>
        
        <?php if ($message): ?>
            <?php echo $message; ?>
        <?php endif; ?>
        
        <div class="card">
            <h2>Fix Invalid HTML Blocks</h2>
            
            <form method="post">
                <?php wp_nonce_field('fix_html_blocks', 'fix_html_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="dry_run">Dry Run:</label>
                        </th>
                        <td>
                            <input type="checkbox" name="dry_run" id="dry_run" value="1" checked>
                            <label for="dry_run">Preview changes without making them (recommended for first run)</label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label>Post Types to Process:</label>
                        </th>
                        <td>
                            <?php
                            $post_types = get_post_types(array('public' => true), 'objects');
                            $selected_types = array('post', 'page');
                            
                            foreach ($post_types as $post_type) {
                                $checked = in_array($post_type->name, $selected_types) ? 'checked' : '';
                                ?>
                                <label style="display: block; margin-bottom: 5px;">
                                    <input type="checkbox" name="post_types[]" value="<?php echo esc_attr($post_type->name); ?>" <?php echo $checked; ?>>
                                    <?php echo esc_html($post_type->labels->name); ?> (<?php echo esc_html($post_type->name); ?>)
                                </label>
                                <?php
                            }
                            ?>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="limit">Limit:</label>
                        </th>
                        <td>
                            <input type="number" name="limit" id="limit" value="0" min="0" style="width: 100px;">
                            <label for="limit">Number of posts to process (0 = all posts)</label>
                        </td>
                    </tr>
                </table>
                
                <p>
                    <input type="submit" name="fix_html_blocks" class="button button-primary" value="Fix Invalid HTML Blocks">
                </p>
            </form>
        </div>
        
        <div class="card">
            <h2>Test on Single Page</h2>
            <p>Select a specific page to test the HTML fixes on. This is useful for testing before running on all posts.</p>
            
            <form method="post">
                <?php wp_nonce_field('fix_single_page_html', 'fix_single_page_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="single_page_id">Select Page:</label>
                        </th>
                        <td>
                            <select name="single_page_id" id="single_page_id" style="min-width: 300px;">
                                <option value="">-- Select a Page --</option>
                                <?php
                                // Get all published pages and posts
                                $all_posts = get_posts(array(
                                    'post_type' => array('post', 'page'),
                                    'post_status' => 'publish',
                                    'posts_per_page' => -1,
                                    'orderby' => 'title',
                                    'order' => 'ASC'
                                ));
                                
                                foreach ($all_posts as $post) {
                                    $post_type_label = $post->post_type === 'post' ? 'Post' : 'Page';
                                    $selected = '';
                                    if (isset($_POST['single_page_id']) && $_POST['single_page_id'] == $post->ID) {
                                        $selected = 'selected';
                                    }
                                    ?>
                                    <option value="<?php echo esc_attr($post->ID); ?>" <?php echo $selected; ?>>
                                        <?php echo esc_html($post->post_title); ?> (<?php echo $post_type_label; ?> ID: <?php echo $post->ID; ?>)
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="single_page_dry_run">Dry Run:</label>
                        </th>
                        <td>
                            <input type="checkbox" name="single_page_dry_run" id="single_page_dry_run" value="1" checked>
                            <label for="single_page_dry_run">Preview changes without making them</label>
                        </td>
                    </tr>
                </table>
                
                <p>
                    <input type="submit" name="fix_single_page_html" class="button button-secondary" value="Test HTML Fixes on Selected Page">
                </p>
            </form>
        </div>
        
        <?php if (!empty($results) && isset($results['details'])): ?>
            <div class="card">
                <h2>Results</h2>
                
                <?php if (count($results['details']) === 1 && isset($_POST['fix_single_page_html'])): ?>
                    <!-- Single page preview with before/after -->
                    <?php foreach ($results['details'] as $detail): ?>
                        <h3><?php echo esc_html($detail['title']); ?> (<?php echo esc_html($detail['post_type']); ?> ID: <?php echo esc_html($detail['post_id']); ?>)</h3>
                        
                        <div style="margin-bottom: 20px;">
                            <strong>Issues Found:</strong> <?php echo esc_html($detail['issues_found']); ?>
                            <br>
                            <strong>Status:</strong> 
                            <?php if ($detail['status'] === 'fixed'): ?>
                                <span style="color: green;">✓ Fixed</span>
                            <?php elseif ($detail['status'] === 'no_issues'): ?>
                                <span style="color: blue;">No Issues</span>
                            <?php elseif ($detail['status'] === 'error'): ?>
                                <span style="color: red;">✗ Error</span>
                            <?php else: ?>
                                <span style="color: orange;">Preview</span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($detail['original_content'] !== $detail['fixed_content']): ?>
                            <div style="display: flex; gap: 20px; margin-top: 20px;">
                                <div style="flex: 1; border: 1px solid #ddd; padding: 15px; background: #f9f9f9;">
                                    <h4 style="margin-top: 0; color: #d63638;">Before (Original Content)</h4>
                                    <div style="background: white; padding: 10px; border: 1px solid #ccc; max-height: 400px; overflow-y: auto; font-family: monospace; font-size: 12px; white-space: pre-wrap;">
                                        <?php echo esc_html($detail['original_content']); ?>
                                    </div>
                                </div>
                                
                                <div style="flex: 1; border: 1px solid #ddd; padding: 15px; background: #f9f9f9;">
                                    <h4 style="margin-top: 0; color: #00a32a;">After (Fixed Content)</h4>
                                    <div style="background: white; padding: 10px; border: 1px solid #ccc; max-height: 400px; overflow-y: auto; font-family: monospace; font-size: 12px; white-space: pre-wrap;">
                                        <?php echo esc_html($detail['fixed_content']); ?>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div style="background: #f0f6fc; padding: 15px; border-left: 4px solid #2271b1;">
                                <p><strong>No changes needed:</strong> This page doesn't have any HTML nesting issues that need to be fixed.</p>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Multiple posts results table -->
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Post Title</th>
                                <th>Post Type</th>
                                <th>Post ID</th>
                                <th>Issues Found</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results['details'] as $detail): ?>
                                <tr>
                                    <td><?php echo esc_html($detail['title']); ?></td>
                                    <td><?php echo esc_html($detail['post_type']); ?></td>
                                    <td><?php echo esc_html($detail['post_id']); ?></td>
                                    <td><?php echo esc_html($detail['issues_found']); ?></td>
                                    <td>
                                        <?php if ($detail['status'] === 'fixed'): ?>
                                            <span style="color: green;">✓ Fixed</span>
                                        <?php elseif ($detail['status'] === 'no_issues'): ?>
                                            <span style="color: blue;">No Issues</span>
                                        <?php elseif ($detail['status'] === 'error'): ?>
                                            <span style="color: red;">✗ Error</span>
                                        <?php else: ?>
                                            <span style="color: orange;">Preview</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Fix invalid HTML blocks in WordPress content
 * 
 * @param bool $dry_run Whether to preview changes without making them
 * @param array $post_types Array of post types to process
 * @param int $limit Maximum number of posts to process (0 = all)
 * @return array Results of the operation
 */
function fix_invalid_html_blocks($dry_run = true, $post_types = array('post', 'page'), $limit = 0) {
    $results = array(
        'total_count' => 0,
        'found_issues' => 0,
        'fixed_count' => 0,
        'details' => array()
    );
    
    // Build query args
    $args = array(
        'post_type' => $post_types,
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'ID',
        'order' => 'ASC'
    );
    
    if ($limit > 0) {
        $args['posts_per_page'] = $limit;
    }
    
    $posts = get_posts($args);
    $results['total_count'] = count($posts);
    
    foreach ($posts as $post) {
        $original_content = $post->post_content;
        $fixed_content = fix_single_post_html($original_content);
        $issues_found = 0;
        $status = 'no_issues';
        
        // Count issues by comparing content
        if ($original_content !== $fixed_content) {
            $issues_found = count_html_issues($original_content, $fixed_content);
            $results['found_issues']++;
            
            if ($dry_run) {
                $status = 'preview';
            } else {
                // Actually update the post
                $update_result = wp_update_post(array(
                    'ID' => $post->ID,
                    'post_content' => $fixed_content
                ));
                
                if ($update_result) {
                    $status = 'fixed';
                    $results['fixed_count']++;
                } else {
                    $status = 'error';
                }
            }
        }
        
        $results['details'][] = array(
            'post_id' => $post->ID,
            'title' => $post->post_title,
            'post_type' => $post->post_type,
            'issues_found' => $issues_found,
            'status' => $status,
            'original_content' => $original_content,
            'fixed_content' => $fixed_content
        );
    }
    
    return $results;
}

/**
 * Fix HTML issues in a single post's content
 * 
 * @param string $content The post content to fix
 * @return string The fixed content
 */
function fix_single_post_html($content) {
    if (empty($content)) {
        return $content;
    }
    
    $fixed = $content;
    
    // Fix 1: Unwrap headings from paragraphs
    // <p><h1>...</h1></p> → <h1>...</h1>
    $fixed = preg_replace('#<p>\s*(<h[1-6][^>]*>.*?</h[1-6]>)\s*</p>#is', '$1', $fixed);
    
    // Fix 2: Unwrap lists from paragraphs
    // <p><ul>...</ul></p> → <ul>...</ul>
    // <p><ol>...</ol></p> → <ol>...</ol>
    $fixed = preg_replace('#<p>\s*(<(?:ul|ol)[^>]*>.*?</(?:ul|ol)>)\s*</p>#is', '$1', $fixed);
    
    // Fix 3: Unwrap divs from paragraphs
    // <p><div>...</div></p> → <div>...</div>
    $fixed = preg_replace('#<p>\s*(<div[^>]*>.*?</div>)\s*</p>#is', '$1', $fixed);
    
    // Fix 4: Unwrap tables from paragraphs
    // <p><table>...</table></p> → <table>...</table>
    $fixed = preg_replace('#<p>\s*(<table[^>]*>.*?</table>)\s*</p>#is', '$1', $fixed);
    
    // Fix 5: Unwrap blockquotes from paragraphs
    // <p><blockquote>...</blockquote></p> → <blockquote>...</blockquote>
    $fixed = preg_replace('#<p>\s*(<blockquote[^>]*>.*?</blockquote>)\s*</p>#is', '$1', $fixed);
    
    // Fix 6: Unwrap pre tags from paragraphs
    // <p><pre>...</pre></p> → <pre>...</pre>
    $fixed = preg_replace('#<p>\s*(<pre[^>]*>.*?</pre>)\s*</p>#is', '$1', $fixed);
    
    // Fix 7: Unwrap images from paragraphs (standalone images)
    // <p><img /></p> → <img />
    $fixed = preg_replace('#<p>\s*(<img[^>]*/?>)\s*</p>#is', '$1', $fixed);
    
    // Fix 8: Unwrap iframes from paragraphs
    // <p><iframe>...</iframe></p> → <iframe>...</iframe>
    $fixed = preg_replace('#<p>\s*(<iframe[^>]*>.*?</iframe>)\s*</p>#is', '$1', $fixed);
    
    // Fix 9: Unwrap video/audio elements from paragraphs
    // <p><video>...</video></p> → <video>...</video>
    $fixed = preg_replace('#<p>\s*(<(?:video|audio)[^>]*>.*?</(?:video|audio)>)\s*</p>#is', '$1', $fixed);
    
    // Fix 10: Unwrap form elements from paragraphs
    // <p><form>...</form></p> → <form>...</form>
    $fixed = preg_replace('#<p>\s*(<form[^>]*>.*?</form>)\s*</p>#is', '$1', $fixed);
    
    // Fix 11: Unwrap address elements from paragraphs
    // <p><address>...</address></p> → <address>...</address>
    $fixed = preg_replace('#<p>\s*(<address[^>]*>.*?</address>)\s*</p>#is', '$1', $fixed);
    
    // Fix 12: Unwrap fieldset elements from paragraphs
    // <p><fieldset>...</fieldset></p> → <fieldset>...</fieldset>
    $fixed = preg_replace('#<p>\s*(<fieldset[^>]*>.*?</fieldset>)\s*</p>#is', '$1', $fixed);
    
    // Fix 13: Unwrap hr elements from paragraphs
    // <p><hr /></p> → <hr />
    $fixed = preg_replace('#<p>\s*(<hr[^>]*/?>)\s*</p>#is', '$1', $fixed);
    
    // Fix 14: Unwrap br elements from paragraphs (standalone)
    // <p><br /></p> → <br />
    $fixed = preg_replace('#<p>\s*(<br[^>]*/?>)\s*</p>#is', '$1', $fixed);
    
    // Fix 15: Clean up empty paragraphs that might be left
    // <p></p> → (remove)
    $fixed = preg_replace('#<p>\s*</p>#is', '', $fixed);
    
    // Fix 16: Clean up paragraphs with only whitespace
    // <p>   </p> → (remove)
    $fixed = preg_replace('#<p>\s+</p>#is', '', $fixed);
    
    // Fix 17: Unwrap multiple block elements from paragraphs
    // <p><h2>...</h2><p>...</p></p> → <h2>...</h2><p>...</p>
    $fixed = preg_replace_callback(
        '#<p>\s*((?:<(?:h[1-6]|ul|ol|div|table|blockquote|pre|iframe|video|audio|form|address|fieldset)[^>]*>.*?</(?:h[1-6]|ul|ol|div|table|blockquote|pre|iframe|video|audio|form|address|fieldset)>)+)\s*</p>#is',
        function ($matches) {
            return $matches[1];
        },
        $fixed
    );
    
    // Fix 18: Unwrap mixed content (headings + paragraphs)
    // <p><h2>...</h2><p>...</p></p> → <h2>...</h2><p>...</p>
    $fixed = preg_replace_callback(
        '#<p>\s*((?:<h[1-6]>.*?</h[1-6]>)+)\s*((?:<p>.*?</p>)+)\s*</p>#is',
        function ($matches) {
            return $matches[1] . "\n" . $matches[2];
        },
        $fixed
    );
    
    // Fix 19: Unwrap mixed content (block elements + paragraphs)
    // <p><div>...</div><p>...</p></p> → <div>...</div><p>...</p>
    $fixed = preg_replace_callback(
        '#<p>\s*((?:<(?:ul|ol|div|table|blockquote|pre|iframe|video|audio|form|address|fieldset)[^>]*>.*?</(?:ul|ol|div|table|blockquote|pre|iframe|video|audio|form|address|fieldset)>)+)\s*((?:<p>.*?</p>)+)\s*</p>#is',
        function ($matches) {
            return $matches[1] . "\n" . $matches[2];
        },
        $fixed
    );
    
    // Fix 20: Clean up multiple consecutive line breaks
    $fixed = preg_replace('#\n{3,}#', "\n\n", $fixed);
    
    return $fixed;
}

/**
 * Count the number of HTML issues found in content
 * 
 * @param string $original_content The original content
 * @param string $fixed_content The fixed content
 * @return int Number of issues found
 */
function count_html_issues($original_content, $fixed_content) {
    $issues = 0;
    
    // Count common patterns that indicate issues
    $patterns = array(
        '#<p>\s*<h[1-6][^>]*>.*?</h[1-6]>\s*</p>#is',
        '#<p>\s*<(?:ul|ol)[^>]*>.*?</(?:ul|ol)>\s*</p>#is',
        '#<p>\s*<div[^>]*>.*?</div>\s*</p>#is',
        '#<p>\s*<table[^>]*>.*?</table>\s*</p>#is',
        '#<p>\s*<blockquote[^>]*>.*?</blockquote>\s*</p>#is',
        '#<p>\s*<pre[^>]*>.*?</pre>\s*</p>#is',
        '#<p>\s*<img[^>]*/?>\s*</p>#is',
        '#<p>\s*<iframe[^>]*>.*?</iframe>\s*</p>#is',
        '#<p>\s*<(?:video|audio)[^>]*>.*?</(?:video|audio)>\s*</p>#is',
        '#<p>\s*<form[^>]*>.*?</form>\s*</p>#is',
        '#<p>\s*<address[^>]*>.*?</address>\s*</p>#is',
        '#<p>\s*<fieldset[^>]*>.*?</fieldset>\s*</p>#is',
        '#<p>\s*<hr[^>]*/?>\s*</p>#is',
        '#<p>\s*<br[^>]*/?>\s*</p>#is',
        '#<p>\s*</p>#is',
        '#<p>\s+</p>#is'
    );
    
    foreach ($patterns as $pattern) {
        $matches = preg_match_all($pattern, $original_content);
        if ($matches !== false) {
            $issues += $matches;
        }
    }
    
    return $issues;
}

/**
 * Fix HTML issues on a single page
 * 
 * @param int $page_id The page ID to fix
 * @param bool $dry_run Whether to preview changes without making them
 * @return array Results of the operation
 */
function fix_single_page_html_blocks($page_id, $dry_run = true) {
    $results = array(
        'total_count' => 1,
        'found_issues' => 0,
        'fixed_count' => 0,
        'details' => array()
    );
    
    $post = get_post($page_id);
    if (!$post) {
        $results['details'][] = array(
            'post_id' => $page_id,
            'title' => 'Page not found',
            'post_type' => 'unknown',
            'issues_found' => 0,
            'status' => 'error',
            'original_content' => '',
            'fixed_content' => ''
        );
        return $results;
    }
    
    $original_content = $post->post_content;
    $fixed_content = fix_single_post_html($original_content);
    $issues_found = 0;
    $status = 'no_issues';
    
    // Count issues by comparing content
    if ($original_content !== $fixed_content) {
        $issues_found = count_html_issues($original_content, $fixed_content);
        $results['found_issues'] = 1;
        
        if ($dry_run) {
            $status = 'preview';
        } else {
            // Actually update the post
            $update_result = wp_update_post(array(
                'ID' => $post->ID,
                'post_content' => $fixed_content
            ));
            
            if ($update_result) {
                $status = 'fixed';
                $results['fixed_count'] = 1;
            } else {
                $status = 'error';
            }
        }
    }
    
    $results['details'][] = array(
        'post_id' => $post->ID,
        'title' => $post->post_title,
        'post_type' => $post->post_type,
        'issues_found' => $issues_found,
        'status' => $status,
        'original_content' => $original_content,
        'fixed_content' => $fixed_content
    );
    
    return $results;
}

/**
 * AJAX handler for fixing HTML blocks
 */
function handle_fix_html_blocks_ajax() {
    // Check nonce for security
    if (!wp_verify_nonce($_POST['fix_html_nonce'], 'fix_html_blocks')) {
        wp_die(json_encode(array('success' => false, 'message' => 'Security check failed')));
    }
    
    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_die(json_encode(array('success' => false, 'message' => 'Insufficient permissions')));
    }
    
    $dry_run = isset($_POST['dry_run']) && $_POST['dry_run'] === 'true';
    $post_types = isset($_POST['post_types']) ? $_POST['post_types'] : array('post', 'page');
    $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 0;
    
    $results = fix_invalid_html_blocks($dry_run, $post_types, $limit);
    
    wp_die(json_encode(array(
        'success' => true,
        'results' => $results
    )));
}
add_action('wp_ajax_fix_html_blocks', 'handle_fix_html_blocks_ajax');

// ===================== BULK SET DEPARTMENT SUBPAGE TEMPLATE TOOL =====================
/**
 * Bulk set the template for all subpages of department homepages
 *
 * @param bool $dry_run If true, only preview changes
 * @param int $limit Max number of pages to process (0 = all)
 * @return array Report of changes
 */
function set_department_subpage_templates($dry_run = true, $limit = 0) {
    $report = array(
        'total_checked' => 0,
        'updated' => 0,
        'skipped' => 0,
        'details' => array(),
    );
    
    // Get all published pages
    $args = array(
        'post_type' => 'page',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'ID',
        'order' => 'ASC',
    );
    if ($limit > 0) {
        $args['posts_per_page'] = $limit;
    }
    $pages = get_posts($args);
    $template_file = 'template-department-subpage.php';
    $skip_templates = array('template-department-subpage.php', 'template-department-subpage-no-sidebar.php');
    
    foreach ($pages as $page) {
        $report['total_checked']++;
        // Use your existing function to find department root
        $dept_root_id = get_department_root_page_id($page->ID);
        if (!$dept_root_id || $dept_root_id == $page->ID) {
            $report['skipped']++;
            $report['details'][] = array(
                'post_id' => $page->ID,
                'title' => $page->post_title,
                'reason' => 'Not a subpage (or is department root)',
                'status' => 'skipped',
            );
            continue;
        }
        // Check current template
        $current_template = get_post_meta($page->ID, '_wp_page_template', true);
        if (in_array($current_template, $skip_templates)) {
            $report['skipped']++;
            $report['details'][] = array(
                'post_id' => $page->ID,
                'title' => $page->post_title,
                'reason' => 'Already set to department subpage template',
                'status' => 'skipped',
            );
            continue;
        }
        // Update if not dry run
        if (!$dry_run) {
            update_post_meta($page->ID, '_wp_page_template', $template_file);
        }
        $report['updated']++;
        $report['details'][] = array(
            'post_id' => $page->ID,
            'title' => $page->post_title,
            'old_template' => $current_template,
            'new_template' => $template_file,
            'status' => $dry_run ? 'would update' : 'updated',
        );
    }
    return $report;
}

/**
 * Add admin page for bulk setting department subpage templates
 */
function add_department_subpage_template_tool_page() {
    add_management_page(
        'Set Department Subpage Templates',
        'Set Dept Subpage Templates',
        'manage_options',
        'set-dept-subpage-templates',
        'department_subpage_template_tool_page'
    );
}
add_action('admin_menu', 'add_department_subpage_template_tool_page');

function department_subpage_template_tool_page() {
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }
    $message = '';
    $report = array();
    if (isset($_POST['set_templates'])) {
        // Verify nonce
        if (!wp_verify_nonce($_POST['set_templates_nonce'], 'set_templates_action')) {
            $message = "<div class='notice notice-error'><p>Security check failed. Please try again.</p></div>";
        } else {
            $dry_run = isset($_POST['dry_run']);
            $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 0;
            $report = set_department_subpage_templates($dry_run, $limit);
            if ($dry_run) {
                $message = "<div class='notice notice-info'><p>Dry run: {$report['updated']} pages would be updated out of {$report['total_checked']} checked.</p></div>";
            } else {
                $message = "<div class='notice notice-success'><p>Updated {$report['updated']} pages out of {$report['total_checked']} checked.</p></div>";
            }
        }
    }
    ?>
    <div class="wrap">
        <h1>Set Department Subpage Templates</h1>
        <div class="card">
            <h2>Bulk Set Template for Department Subpages</h2>
            <p>This tool will set the <code>template-department-subpage-no-sidebar.php</code> template for all subpages of department homepages (recursively), if not already set.</p>
            <p><strong>⚠️ Always backup your database before running bulk tools!</strong></p>
        </div>
        <?php if ($message) echo $message; ?>
        <form method="post">
            <?php wp_nonce_field('set_templates_action', 'set_templates_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="dry_run">Dry Run:</label></th>
                    <td>
                        <input type="checkbox" name="dry_run" id="dry_run" value="1" checked>
                        <label for="dry_run">Preview changes without making them</label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="limit">Limit:</label></th>
                    <td>
                        <input type="number" name="limit" id="limit" value="0" min="0" style="width: 100px;">
                        <label for="limit">Number of pages to process (0 = all)</label>
                    </td>
                </tr>
            </table>
            <p><input type="submit" name="set_templates" class="button button-primary" value="Set Templates"></p>
        </form>
        <?php if (!empty($report) && !empty($report['details'])): ?>
            <div class="card">
                <h2>Report</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Post ID</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Old Template</th>
                            <th>New Template</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report['details'] as $row): ?>
                            <tr>
                                <td><?php echo esc_html($row['post_id']); ?></td>
                                <td><?php echo esc_html($row['title']); ?></td>
                                <td><?php echo esc_html($row['status']); ?></td>
                                <td><?php echo isset($row['old_template']) ? esc_html($row['old_template']) : '-'; ?></td>
                                <td><?php echo isset($row['new_template']) ? esc_html($row['new_template']) : '-'; ?></td>
                                <td><?php echo isset($row['reason']) ? esc_html($row['reason']) : '-'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * FileBird Document Library Migration Tool
 * Migrates existing [filebird_docs] shortcodes to new Document Library custom post types
 */

// Add admin menu for the migration tool
function add_filebird_migration_tool_page() {
    add_management_page(
        'FileBird Migration Tool',
        'FileBird Migration',
        'manage_options',
        'filebird-migration-tool',
        'filebird_migration_tool_page'
    );
}
add_action('admin_menu', 'add_filebird_migration_tool_page');

// Add admin menu for the rename tool
function add_filebird_rename_tool_page() {
    add_management_page(
        'FileBird Rename Libraries',
        'FileBird Rename',
        'manage_options',
        'filebird-rename-tool',
        'filebird_rename_tool_page'
    );
}
add_action('admin_menu', 'add_filebird_rename_tool_page');

// Enqueue styles for the migration tool
function enqueue_filebird_migration_styles($hook) {
    if ($hook === 'tools_page_filebird-migration-tool') {
        wp_enqueue_style('filebird-migration-styles', get_stylesheet_directory_uri() . '/css/filebird-migration.css', array(), '1.0.0');
    }
}
add_action('admin_enqueue_scripts', 'enqueue_filebird_migration_styles');

// Migration tool page
function filebird_migration_tool_page() {
    // Check if required plugins are active
    $plugin_status = check_filebird_plugin_status();
    if (!$plugin_status['active']) {
        echo '<div class="notice notice-error"><p>' . esc_html($plugin_status['message']) . '</p></div>';
    }
    
    // Handle form submission
    if (isset($_POST['action']) && $_POST['action'] === 'migrate_shortcodes') {
        if (wp_verify_nonce($_POST['migration_nonce'], 'filebird_migration')) {
            $result = process_filebird_migration();
            if ($result['success']) {
                echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
            }
        } else {
            echo '<div class="notice notice-error"><p>Security check failed.</p></div>';
        }
    }

    // Scan for existing shortcodes
    $shortcodes = scan_for_filebird_shortcodes();
    
    ?>
    <div class="wrap filebird-migration-tool">
        <h1>FileBird Document Library Migration Tool</h1>
        
        <div class="card">
            <h2>Migration Overview</h2>
            <p>This tool will help you migrate existing <code>[filebird_docs]</code> shortcodes to the new Document Library custom post type system.</p>
            
            <h3>What this tool does:</h3>
            <ul>
                <li>Scans all posts and pages for existing <code>[filebird_docs]</code> shortcodes</li>
                <li>Creates new Document Library custom post types with the settings from each shortcode</li>
                <li>Replaces the old shortcodes with <code>[render_document_library id="X"]</code> shortcodes</li>
                <li>Provides a detailed report of the migration process</li>
            </ul>
            
            <h3>Benefits of the new system:</h3>
            <ul>
                <li><strong>Easier Management:</strong> Edit document library settings through the WordPress admin interface</li>
                <li><strong>Better Organization:</strong> All document libraries are stored as custom post types</li>
                <li><strong>Enhanced Features:</strong> Access to advanced features like document ordering and accordion controls</li>
                <li><strong>Future-Proof:</strong> Better integration with WordPress core functionality</li>
            </ul>
        </div>

        <div class="card">
            <h2>Found Shortcodes</h2>
            <?php if (empty($shortcodes)): ?>
                <p>No <code>[filebird_docs]</code> shortcodes found in your content.</p>
            <?php else: ?>
                <p>Found <strong><?php echo count($shortcodes); ?></strong> shortcodes to migrate:</p>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Post/Page</th>
                            <th>Shortcode</th>
                            <th>Folder ID</th>
                            <th>Layout</th>
                            <th>Other Settings</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shortcodes as $shortcode): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo get_edit_post_link($shortcode['post_id']); ?>" target="_blank" class="post-link">
                                        <?php echo esc_html($shortcode['post_title']); ?>
                                    </a>
                                </td>
                                <td><code><?php echo esc_html($shortcode['shortcode']); ?></code></td>
                                <td><span class="folder-name"><?php echo esc_html($shortcode['folder_id']); ?></span></td>
                                <td><?php echo esc_html($shortcode['layout']); ?></td>
                                <td class="settings-summary">
                                    <?php 
                                    $settings = array();
                                    if ($shortcode['include_subfolders']) $settings[] = 'Include Subfolders';
                                    if ($shortcode['group_by_folder']) $settings[] = 'Group by Folder';
                                    if ($shortcode['show_size']) $settings[] = 'Show Size';
                                    if ($shortcode['show_date']) $settings[] = 'Show Date';
                                    echo esc_html(implode(', ', $settings));
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <?php if (!empty($shortcodes)): ?>
            <div class="card">
                <h2>Start Migration</h2>
                <div class="warning">
                    <strong>Warning:</strong> This process will modify your posts and pages. It's recommended to backup your database before proceeding.
                </div>
                
                <form method="post" action="">
                    <?php wp_nonce_field('filebird_migration', 'migration_nonce'); ?>
                    <input type="hidden" name="action" value="migrate_shortcodes">
                    
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="dry_run" value="1" checked>
                            Run in dry-run mode (preview only, no changes made)
                        </label>
                    </div>
                    
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="create_libraries" value="1" checked>
                            Create Document Library custom post types
                        </label>
                    </div>
                    
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="replace_shortcodes" value="1" checked>
                            Replace shortcodes in posts/pages
                        </label>
                    </div>
                    
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="rename_libraries" value="1" checked>
                            Rename Document Libraries based on usage (where they are displayed)
                        </label>
                    </div>
                    
                    <div class="submit">
                        <input type="submit" name="submit" id="submit" class="button button-primary" value="Start Migration">
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

// Check if FileBird plugin is active and properly configured
function check_filebird_plugin_status() {
    $status = array(
        'active' => true,
        'message' => ''
    );
    
    // Check if FileBird plugin is active
    if (!class_exists('FileBird\Plugin') && !class_exists('FileBird')) {
        $status['active'] = false;
        $status['message'] = 'FileBird plugin is not active. Please install and activate the FileBird plugin first.';
        return $status;
    }
    
    // Check if Document Library custom post type is registered
    if (!post_type_exists('document_library')) {
        $status['active'] = false;
        $status['message'] = 'Document Library custom post type is not registered. Please ensure the FileBird Frontend Documents plugin is activated.';
        return $status;
    }
    
    return $status;
}

// Scan for existing filebird_docs shortcodes
function scan_for_filebird_shortcodes() {
    $shortcodes = array();
    
    // Get all posts and pages
    $posts = get_posts(array(
        'post_type' => array('post', 'page'),
        'post_status' => 'publish',
        'numberposts' => -1,
        'fields' => 'ids'
    ));
    
    foreach ($posts as $post_id) {
        $post = get_post($post_id);
        $content = $post->post_content;
        
        // Find all [filebird_docs] shortcodes
        preg_match_all('/\[filebird_docs\s+([^\]]+)\]/', $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $shortcode = $match[0];
            $attributes = $match[1];
            
            // Parse attributes
            $parsed_attrs = parse_shortcode_attributes($attributes);
            
            if (!empty($parsed_attrs['folder'])) {
                $shortcodes[] = array(
                    'post_id' => $post_id,
                    'post_title' => $post->post_title,
                    'shortcode' => $shortcode,
                    'folder_id' => $parsed_attrs['folder'],
                    'layout' => $parsed_attrs['layout'] ?? 'grid',
                    'include_subfolders' => $parsed_attrs['include_subfolders'] ?? false,
                    'group_by_folder' => $parsed_attrs['group_by_folder'] ?? false,
                    'show_size' => $parsed_attrs['show_size'] ?? false,
                    'show_date' => $parsed_attrs['show_date'] ?? false,
                    'show_thumbnail' => $parsed_attrs['show_thumbnail'] ?? true,
                    'columns' => $parsed_attrs['columns'] ?? 3,
                    'orderby' => $parsed_attrs['orderby'] ?? 'date',
                    'order' => $parsed_attrs['order'] ?? 'DESC',
                    'limit' => $parsed_attrs['limit'] ?? -1,
                    'accordion_default' => $parsed_attrs['accordion_default'] ?? 'closed',
                    'exclude_folders' => $parsed_attrs['exclude_folders'] ?? '',
                    'custom_class' => $parsed_attrs['class'] ?? '',
                    'parsed_attrs' => $parsed_attrs
                );
            }
        }
    }
    
    return $shortcodes;
}

// Parse shortcode attributes
function parse_shortcode_attributes($attributes_string) {
    $attributes = array();
    
    // Match key="value" pairs
    preg_match_all('/(\w+)\s*=\s*"([^"]*)"/', $attributes_string, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $match) {
        $key = $match[1];
        $value = $match[2];
        
        // Convert string values to appropriate types
        switch ($key) {
            case 'folder':
            case 'columns':
            case 'limit':
                $attributes[$key] = intval($value);
                break;
            case 'show_title':
            case 'show_size':
            case 'show_date':
            case 'show_thumbnail':
            case 'include_subfolders':
            case 'group_by_folder':
                $attributes[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                break;
            default:
                $attributes[$key] = $value;
        }
    }
    
    return $attributes;
}

// Process the migration
function process_filebird_migration() {
    $dry_run = isset($_POST['dry_run']) && $_POST['dry_run'] === '1';
    $create_libraries = isset($_POST['create_libraries']) && $_POST['create_libraries'] === '1';
    $replace_shortcodes = isset($_POST['replace_shortcodes']) && $_POST['replace_shortcodes'] === '1';
    $rename_libraries = isset($_POST['rename_libraries']) && $_POST['rename_libraries'] === '1';
    
    $shortcodes = scan_for_filebird_shortcodes();
    $results = array(
        'libraries_created' => 0,
        'shortcodes_replaced' => 0,
        'libraries_renamed' => 0,
        'errors' => array()
    );
    
    if (empty($shortcodes)) {
        return array(
            'success' => true,
            'message' => 'No shortcodes found to migrate.'
        );
    }
    
    foreach ($shortcodes as $shortcode_data) {
        try {
            // Create Document Library if requested
            $library_id = null;
            if ($create_libraries && !$dry_run) {
                $library_id = create_document_library($shortcode_data);
                if ($library_id) {
                    $results['libraries_created']++;
                }
            } elseif ($create_libraries && $dry_run) {
                $results['libraries_created']++;
            }
            
            // Replace shortcode if requested
            if ($replace_shortcodes && !$dry_run) {
                $new_shortcode = '[render_document_library id="' . $library_id . '"]';
                $replaced = replace_shortcode_in_post($shortcode_data['post_id'], $shortcode_data['shortcode'], $new_shortcode);
                if ($replaced) {
                    $results['shortcodes_replaced']++;
                }
            } elseif ($replace_shortcodes && $dry_run) {
                $results['shortcodes_replaced']++;
            }
            
            // Rename library based on usage if requested
            if ($rename_libraries && $library_id && !$dry_run) {
                $renamed = rename_document_library_based_on_usage($library_id, $shortcode_data['post_id'], $shortcode_data['post_title']);
                if ($renamed) {
                    $results['libraries_renamed']++;
                }
            } elseif ($rename_libraries && $library_id && $dry_run) {
                $results['libraries_renamed']++;
            }
            
        } catch (Exception $e) {
            $results['errors'][] = 'Error processing shortcode in post "' . $shortcode_data['post_title'] . '": ' . $e->getMessage();
        }
    }
    
    // Build result message
    $message = '';
    if ($dry_run) {
        $message .= 'Dry run completed. Would create ' . $results['libraries_created'] . ' Document Libraries, replace ' . $results['shortcodes_replaced'] . ' shortcodes';
        if ($results['libraries_renamed'] > 0) {
            $message .= ', and rename ' . $results['libraries_renamed'] . ' Document Libraries';
        }
        $message .= '.';
    } else {
        $message .= 'Migration completed. Created ' . $results['libraries_created'] . ' Document Libraries, replaced ' . $results['shortcodes_replaced'] . ' shortcodes';
        if ($results['libraries_renamed'] > 0) {
            $message .= ', and renamed ' . $results['libraries_renamed'] . ' Document Libraries';
        }
        $message .= '.';
        
        // Add helpful information about the new system
        if ($results['libraries_created'] > 0) {
            $message .= ' You can now manage these Document Libraries from the "Document Libraries" menu in your WordPress admin.';
        }
    }
    
    if (!empty($results['errors'])) {
        $message .= ' Errors: ' . implode('; ', $results['errors']);
    }
    
    return array(
        'success' => empty($results['errors']),
        'message' => $message,
        'results' => $results
    );
}

// Create a new Document Library custom post type
function create_document_library($shortcode_data) {
    // Check if document_library post type exists
    if (!post_type_exists('document_library')) {
        throw new Exception('Document Library custom post type is not registered. Please ensure the FileBird Frontend Documents plugin is activated.');
    }
    
    // Generate title based on folder and settings
    $folder_name = get_folder_name($shortcode_data['folder_id']);
    $title = 'Document Library - ' . $folder_name;
    
    // Create the post
    $post_data = array(
        'post_title' => $title,
        'post_content' => '',
        'post_status' => 'publish',
        'post_type' => 'document_library'
    );
    
    $library_id = wp_insert_post($post_data);
    
    if (is_wp_error($library_id)) {
        throw new Exception('Failed to create Document Library: ' . $library_id->get_error_message());
    }
    
    // Save the meta fields
    update_post_meta($library_id, '_document_library_folders', $shortcode_data['folder_id']);
    update_post_meta($library_id, '_document_library_layout', $shortcode_data['layout']);
    update_post_meta($library_id, '_document_library_columns', $shortcode_data['columns']);
    update_post_meta($library_id, '_document_library_orderby', $shortcode_data['orderby']);
    update_post_meta($library_id, '_document_library_order', $shortcode_data['order']);
    update_post_meta($library_id, '_document_library_limit', $shortcode_data['limit']);
    update_post_meta($library_id, '_document_library_show_title', $shortcode_data['show_title'] ? 'true' : 'false');
    update_post_meta($library_id, '_document_library_show_size', $shortcode_data['show_size'] ? 'true' : 'false');
    update_post_meta($library_id, '_document_library_show_date', $shortcode_data['show_date'] ? 'true' : 'false');
    update_post_meta($library_id, '_document_library_show_thumbnail', $shortcode_data['show_thumbnail'] ? 'true' : 'false');
    update_post_meta($library_id, '_document_library_include_subfolders', $shortcode_data['include_subfolders'] ? 'true' : 'false');
    update_post_meta($library_id, '_document_library_group_by_folder', $shortcode_data['group_by_folder'] ? 'true' : 'false');
    update_post_meta($library_id, '_document_library_accordion_default', $shortcode_data['accordion_default']);
    update_post_meta($library_id, '_document_library_exclude_folders', $shortcode_data['exclude_folders']);
    update_post_meta($library_id, '_document_library_custom_class', $shortcode_data['custom_class']);
    
    return $library_id;
}

// Get folder name from FileBird
function get_folder_name($folder_id) {
    // Try to get folder name from FileBird
    if (class_exists('FileBird_FD_Helper')) {
        $folder = FileBird_FD_Helper::getFolderById($folder_id);
        if ($folder && isset($folder->name)) {
            return $folder->name;
        }
    }
    
    // Try alternative class name
    if (class_exists('FileBird_FD_FileBird_Helper')) {
        $folder = FileBird_FD_FileBird_Helper::getFolderById($folder_id);
        if ($folder && isset($folder->name)) {
            return $folder->name;
        }
    }
    
    // Fallback
    return 'Folder ' . $folder_id;
}

// Replace shortcode in a post
function replace_shortcode_in_post($post_id, $old_shortcode, $new_shortcode) {
    $post = get_post($post_id);
    $content = $post->post_content;
    
    $new_content = str_replace($old_shortcode, $new_shortcode, $content);
    
    if ($new_content !== $content) {
        $updated_post = array(
            'ID' => $post_id,
            'post_content' => $new_content
        );
        
        $result = wp_update_post($updated_post);
        return !is_wp_error($result);
    }
    
    return false;
}

// Rename Document Library based on where it's used
function rename_document_library_based_on_usage($library_id, $post_id, $post_title) {
    // Clean the post title to make it suitable for a Document Library name
    $clean_title = sanitize_text_field($post_title);
    
    // Remove common prefixes/suffixes that might make the name too long
    $clean_title = preg_replace('/\s*-\s*Van Buren County\s*$/i', '', $clean_title);
    $clean_title = preg_replace('/\s*-\s*Government\s*$/i', '', $clean_title);
    $clean_title = preg_replace('/\s*Department\s*$/i', '', $clean_title);
    
    // Truncate if too long (WordPress title limit is typically 60 characters)
    if (strlen($clean_title) > 50) {
        $clean_title = substr($clean_title, 0, 47) . '...';
    }
    
    // Create the new title
    $new_title = 'Document Library - ' . $clean_title;
    
    // Update the Document Library post
    $updated_post = array(
        'ID' => $library_id,
        'post_title' => $new_title
    );
    
    $result = wp_update_post($updated_post);
    
    if (is_wp_error($result)) {
        return false;
    }
    
    // Update the usage tracking meta
    update_document_library_usage($library_id, $post_id, $post_title);
    
    return true;
}

// Update the usage tracking meta for a Document Library
function update_document_library_usage($library_id, $post_id, $post_title) {
    // Get current usage data
    $current_usage = get_post_meta($library_id, '_document_library_usage', true);
    if (!is_array($current_usage)) {
        $current_usage = array();
    }
    
    // Create usage entry
    $usage_entry = array(
        'type' => 'page',
        'id' => $post_id,
        'title' => $post_title,
        'url' => get_permalink($post_id),
        'edit_url' => get_edit_post_link($post_id, 'raw')
    );
    
    // Check if this usage entry already exists
    $exists = false;
    foreach ($current_usage as $usage) {
        if ($usage['id'] == $post_id && $usage['type'] == 'page') {
            $exists = true;
            break;
        }
    }
    
    // Add new usage entry if it doesn't exist
    if (!$exists) {
        $current_usage[] = $usage_entry;
        update_post_meta($library_id, '_document_library_usage', $current_usage);
    }
}

// Rename tool page for existing Document Libraries
function filebird_rename_tool_page() {
    // Check if required plugins are active
    $plugin_status = check_filebird_plugin_status();
    if (!$plugin_status['active']) {
        echo '<div class="notice notice-error"><p>' . esc_html($plugin_status['message']) . '</p></div>';
    }
    
    // Handle form submission
    if (isset($_POST['action']) && $_POST['action'] === 'rename_libraries') {
        if (wp_verify_nonce($_POST['rename_nonce'], 'filebird_rename')) {
            $result = process_library_renaming();
            if ($result['success']) {
                echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
            }
        } else {
            echo '<div class="notice notice-error"><p>Security check failed.</p></div>';
        }
    }

    // Get existing Document Libraries
    $libraries = get_posts(array(
        'post_type' => 'document_library',
        'post_status' => 'publish',
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ));
    
    ?>
    <div class="wrap filebird-migration-tool">
        <h1>FileBird Document Library Rename Tool</h1>
        
        <div class="card">
            <h2>Rename Existing Document Libraries</h2>
            <p>This tool will rename existing Document Libraries based on where they are used (as tracked in the <code>_document_library_usage</code> meta field).</p>
            
            <h3>What this tool does:</h3>
            <ul>
                <li>Scans all existing Document Libraries</li>
                <li>Reads the usage tracking data to see where each library is displayed</li>
                <li>Renames libraries based on the page/post where they are used</li>
                <li>Cleans up titles to make them more readable</li>
            </ul>
        </div>

        <div class="card">
            <h2>Existing Document Libraries</h2>
            <?php if (empty($libraries)): ?>
                <p>No Document Libraries found.</p>
            <?php else: ?>
                <p>Found <strong><?php echo count($libraries); ?></strong> Document Libraries:</p>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Current Name</th>
                            <th>Usage Information</th>
                            <th>Proposed New Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($libraries as $library): ?>
                            <?php 
                            $usage = get_post_meta($library->ID, '_document_library_usage', true);
                            $proposed_name = get_proposed_library_name($library->ID, $usage);
                            ?>
                            <tr>
                                <td>
                                    <a href="<?php echo get_edit_post_link($library->ID); ?>" target="_blank" class="post-link">
                                        <?php echo esc_html($library->post_title); ?>
                                    </a>
                                </td>
                                <td class="settings-summary">
                                    <?php if (is_array($usage) && !empty($usage)): ?>
                                        <?php 
                                        $usage_text = array();
                                        foreach ($usage as $usage_item) {
                                            $usage_text[] = $usage_item['title'];
                                        }
                                        echo esc_html(implode(', ', $usage_text));
                                        ?>
                                    <?php else: ?>
                                        <em>No usage data found</em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($proposed_name && $proposed_name !== $library->post_title): ?>
                                        <span class="folder-name"><?php echo esc_html($proposed_name); ?></span>
                                    <?php else: ?>
                                        <em>No change needed</em>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <?php if (!empty($libraries)): ?>
            <div class="card">
                <h2>Start Renaming</h2>
                <div class="warning">
                    <strong>Warning:</strong> This process will rename Document Libraries. It's recommended to backup your database before proceeding.
                </div>
                
                <form method="post" action="">
                    <?php wp_nonce_field('filebird_rename', 'rename_nonce'); ?>
                    <input type="hidden" name="action" value="rename_libraries">
                    
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="dry_run" value="1" checked>
                            Run in dry-run mode (preview only, no changes made)
                        </label>
                    </div>
                    
                    <div class="submit">
                        <input type="submit" name="submit" id="submit" class="button button-primary" value="Start Renaming">
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

// Process the library renaming
function process_library_renaming() {
    $dry_run = isset($_POST['dry_run']) && $_POST['dry_run'] === '1';
    
    $libraries = get_posts(array(
        'post_type' => 'document_library',
        'post_status' => 'publish',
        'numberposts' => -1
    ));
    
    $results = array(
        'libraries_renamed' => 0,
        'errors' => array()
    );
    
    foreach ($libraries as $library) {
        try {
            $usage = get_post_meta($library->ID, '_document_library_usage', true);
            $proposed_name = get_proposed_library_name($library->ID, $usage);
            
            if ($proposed_name && $proposed_name !== $library->post_title) {
                if (!$dry_run) {
                    $updated_post = array(
                        'ID' => $library->ID,
                        'post_title' => $proposed_name
                    );
                    
                    $result = wp_update_post($updated_post);
                    if (!is_wp_error($result)) {
                        $results['libraries_renamed']++;
                    } else {
                        $results['errors'][] = 'Error renaming library "' . $library->post_title . '": ' . $result->get_error_message();
                    }
                } else {
                    $results['libraries_renamed']++;
                }
            }
        } catch (Exception $e) {
            $results['errors'][] = 'Error processing library "' . $library->post_title . '": ' . $e->getMessage();
        }
    }
    
    // Build result message
    $message = '';
    if ($dry_run) {
        $message .= 'Dry run completed. Would rename ' . $results['libraries_renamed'] . ' Document Libraries.';
    } else {
        $message .= 'Renaming completed. Renamed ' . $results['libraries_renamed'] . ' Document Libraries.';
    }
    
    if (!empty($results['errors'])) {
        $message .= ' Errors: ' . implode('; ', $results['errors']);
    }
    
    return array(
        'success' => empty($results['errors']),
        'message' => $message,
        'results' => $results
    );
}

// Get proposed name for a library based on usage
function get_proposed_library_name($library_id, $usage) {
    if (!is_array($usage) || empty($usage)) {
        return false;
    }
    
    // Get the first usage entry (most relevant)
    $first_usage = $usage[0];
    $post_title = $first_usage['title'];
    
    // Clean the post title to make it suitable for a Document Library name
    $clean_title = sanitize_text_field($post_title);
    
    // Remove common prefixes/suffixes that might make the name too long
    $clean_title = preg_replace('/\s*-\s*Van Buren County\s*$/i', '', $clean_title);
    $clean_title = preg_replace('/\s*-\s*Government\s*$/i', '', $clean_title);
    $clean_title = preg_replace('/\s*Department\s*$/i', '', $clean_title);
    
    // Truncate if too long (WordPress title limit is typically 60 characters)
    if (strlen($clean_title) > 50) {
        $clean_title = substr($clean_title, 0, 47) . '...';
    }
    
    // Create the new title
    $new_title = 'Document Library - ' . $clean_title;
    
    return $new_title;
}

?>


