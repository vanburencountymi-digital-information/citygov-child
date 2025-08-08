<?php
/**
 * Shortcodes Module
 * 
 * Handles all custom shortcode registrations and callbacks for the child theme.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dynamic document library shortcode
 */
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
add_shortcode('dynamic_doc_library', 'dynamic_doc_library_shortcode');

/**
 * Modify shortcode attributes for department placeholders
 */
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

/**
 * Tell document library pro to stop overriding the content of the single document page
 */
add_filter('document_library_pro_enable_single_content_customization', '__return_false');

/**
 * Remove the original shortcodes
 */
remove_shortcode('pagelist_ext');
remove_shortcode('pagelistext');
remove_shortcode('subpages');
remove_shortcode('sub_pages');

/**
 * Custom pagelist_ext shortcode with accordion functionality
 */
function my_custom_pagelist_ext_shortcode($atts) {
    global $post, $pagelist_unq_settings;
    $return = '';
    extract(shortcode_atts(array(
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
    ), $atts));

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

    if ($child_of == '') { // show subpages if child_of is empty
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
    
    $list_pages = get_pages($page_list_ext_args);
    $list_pages_html = '';
    $count = 0;
    $offset_count = 0;
    
    if ($list_pages !== false && count($list_pages) > 0) {
        foreach($list_pages as $page){
            $count++;
            $offset_count++;
            if (!empty($offset) && is_numeric($offset) && $offset_count <= $offset) {
                $count = 0; // number counter to zero if offset is not finished
            }
            if ((!empty($number) && is_numeric($number) && $count <= $number) || (empty($number)) || (!empty($number) && !is_numeric($number))) {
                $link = get_permalink($page->ID);
                $has_children = count(get_pages(array('parent' => $page->ID))) > 0;
                $page_class = $has_children && $accordion_subpages ? 'page-list-ext-item has-children' : 'page-list-ext-item';
                
                $list_pages_html .= '<div class="' . $page_class . '">';
                if ($show_image == 1) {
                    if (get_the_post_thumbnail($page->ID)) { // if there is a featured image
                        $list_pages_html .= '<div class="page-list-ext-image"><a href="'.$link.'" title="'.esc_attr($page->post_title).'">';
                        $image = wp_get_attachment_image_src(get_post_thumbnail_id($page->ID), array($image_width,$image_height));
                        $img_url = $image[0]; // get the src of the featured image
                        $list_pages_html .= '<img src="'.$img_url.'" width="'.esc_attr($image_width).'" alt="'.esc_attr($page->post_title).'" />';
                        $list_pages_html .= '</a></div> ';
                    } else {
                        if ($show_first_image == 1) {
                            $img_scr = pagelist_unqprfx_get_first_image($page->post_content);
                            if (!empty($img_scr)) {
                                $list_pages_html .= '<div class="page-list-ext-image"><a href="'.$link.'" title="'.esc_attr($page->post_title).'">';
                                $list_pages_html .= '<img src="'.$img_scr.'" width="'.esc_attr($image_width).'" alt="'.esc_attr($page->post_title).'" />';
                                $list_pages_html .= '</a></div> ';
                            }
                        }
                    }
                }

                if ($show_title == 1) {
                    if ($has_children && $accordion_subpages) {
                        $list_pages_html .= '<div class="page-title-wrapper">';
                        $list_pages_html .= '<h3 class="page-list-ext-title"><a href="'.$link.'" title="'.esc_attr($page->post_title).'">'.$page->post_title.'</a></h3>';
                        $list_pages_html .= '<span class="dropdown-indicator" aria-hidden="true"></span>';
                        $list_pages_html .= '</div>';
                    } else {
                        $list_pages_html .= '<h3 class="page-list-ext-title"><a href="'.$link.'" title="'.esc_attr($page->post_title).'">'.$page->post_title.'</a></h3>';
                    }
                }
                
                if ($show_content == 1) {
                    if (!empty($page->post_excerpt)) {
                        $text_content = $page->post_excerpt;
                    } else {
                        $text_content = $page->post_content;
                    }

                    if (post_password_required($page)) {
                        $content = '<!-- password protected -->';
                    } else {
                        $content = pagelist_unqprfx_parse_content($text_content, $limit_content, $strip_tags, $strip_shortcodes, $more_tag);
                        $content = do_shortcode($content);

                        if ($show_title == 0) { // make content as a link if there is no title
                            $content = '<a href="'.$link.'">'.$content.'</a>';
                        }
                    }

                    $list_pages_html .= '<div class="page-list-ext-item-content">'.$content.'</div>';
                }
                
                if ($show_child_count == 1) {
                    $count_subpages = count(get_pages("child_of=".$page->ID));
                    if ($count_subpages > 0) { // hide empty
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
                
                if ($show_meta_key != '') {
                    $post_meta = do_shortcode(get_post_meta($page->ID, $show_meta_key, true));
                    if (!empty($post_meta)) { // hide empty
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

// Register the custom pagelist_ext shortcode
add_shortcode('pagelist_ext', 'my_custom_pagelist_ext_shortcode');
add_shortcode('pagelistext', 'my_custom_pagelist_ext_shortcode');

/**
 * Custom subpages shortcode with accordion functionality
 */
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

// Register the custom subpages shortcode
add_shortcode('subpages', 'my_custom_subpages_shortcode');
add_shortcode('sub_pages', 'my_custom_subpages_shortcode');

/**
 * [events_by_series series_id="123" limit="10" order="ASC" future_only="1"]
 * Renders a simple list of events in a Series.
 */
function events_by_series_shortcode($atts) {
    $atts = shortcode_atts([
        'series_id'   => 0,        // required
        'limit'       => 10,
        'order'       => 'ASC',    // ASC|DESC by start date
        'future_only' => '1',      // filter to upcoming only
        'show_time'   => '1',
    ], $atts, 'events_by_series');

    $series_id = absint($atts['series_id']);
    if (!$series_id) return '<em>No series_id provided.</em>';

    $series_tax = 'tribe_series';
    if (!taxonomy_exists($series_tax)) {
        return '<em>Series taxonomy not found.</em>';
    }

    // Base query: all events in this Series
    $q = new WP_Query([
        'post_type'      => 'tribe_events',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'tax_query'      => [[
            'taxonomy' => $series_tax,
            'field'    => 'term_id',
            'terms'    => [$series_id],
        ]],
    ]);

    $events = $q->posts;

    // Optional: keep only upcoming + sort by start date (uses TEC helpers if available)
    if (function_exists('tribe_get_start_date')) {
        if ($atts['future_only'] === '1') {
            $events = array_filter($events, function ($e) {
                return (int) tribe_get_start_date($e->ID, false, 'U') >= time();
            });
        }
        usort($events, function ($a, $b) use ($atts) {
            $a_s = (int) tribe_get_start_date($a->ID, false, 'U');
            $b_s = (int) tribe_get_start_date($b->ID, false, 'U');
            return strtoupper($atts['order']) === 'DESC' ? $b_s <=> $a_s : $a_s <=> $b_s;
        });
    }

    if (empty($events)) return '<em>No events found.</em>';

    $events = array_slice($events, 0, (int) $atts['limit']);

    ob_start();
    echo '<ul class="events-by-series">';
    foreach ($events as $e) {
        $title = esc_html(get_the_title($e));
        $url   = esc_url(get_permalink($e));
        echo "<li><a href=\"{$url}\">{$title}</a>";

        if (function_exists('tribe_get_start_date')) {
            $fmt  = get_option('date_format') . ($atts['show_time'] === '1' ? ' ' . get_option('time_format') : '');
            $date = esc_html(tribe_get_start_date($e->ID, false, $fmt));
            echo " — {$date}";
        }

        echo "</li>";
    }
    echo '</ul>';
    return ob_get_clean();
}
add_shortcode('events_by_series', 'events_by_series_shortcode');
