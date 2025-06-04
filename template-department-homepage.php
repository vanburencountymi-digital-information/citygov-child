<?php
/*
Template Name: Department Homepage
Template Post Type: post, page, event
*/
?>
<?php get_header(); ?>
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<div class="page-header">
	
    <?php if ( has_post_thumbnail()){
		
		the_post_thumbnail('citygov_header',array('class' => 'standard grayscale grayscale-fade'));
    
    } else { 
    
    	if(empty($themnific_redux['tmnf-header-image']['url'])) {} else { ?>
            
                <img class="page-header-img" src="<?php echo esc_url($themnific_redux['tmnf-header-image']['url']);?>" alt="<?php the_title_attribute(); ?>"/>
                
        <?php } 
        
    } ?>
    
    <div class="container">

    	<div class="main-breadcrumbs">
        
        	<?php citygov_breadcrumbs()?>
            
        </div>

        <h1 itemprop="headline" class="entry-title"><?php the_title(); ?></h1>
    
    </div>
        
</div>

<div class="container_alt post tmnf_page">

    <div id="core" class="postbar postbarLeft">
    
    	<div id="content_start" class="tmnf_anchor"></div>
    
        <div id="content" class="eightcol first">
        
            <div <?php post_class('item_inn  p-border'); ?>>
    
				<?php if (is_single()) {?>
            
                    <div class="meta-single p-border">
                        
                        <?php citygov_meta_full(); ?>
                        
                    </div>
                
                <?php } ?>
        
                <div class="clearfix"></div>
                
                <div class="entry">
                    
                    <?php the_content();  ?>
                    <?php
                    // Retrieve the custom field 'department_id' for the current page
                    $department_id = get_post_meta(get_the_ID(), 'department_id', true);
                    $department_name = get_post_meta(get_the_ID(), 'department_name', true);

                    // Display department details
                    // if (!empty($department_id)) {
                    //     echo do_shortcode('[department_details department="' . esc_attr($department_id) . '" show="name,address,phone,fax"]');
                    // }
                    // if (!empty($department_name)) {
                    //     echo '<h2>' . esc_html($department_name) . ' Documents </h2>';
                    //     echo do_shortcode('[doc_library doc_category="' . esc_attr($department_name) . '"]');
                    // }
                    
                    // Display staff directory for multiple departments with department-specific headings
                    if (!empty($department_id)) {
                        // Split comma-separated department IDs and clean them up
                        $department_ids = array_map('trim', explode(',', $department_id));
                        
                        // Remove any empty values
                        $department_ids = array_filter($department_ids);
                        
                        if (!empty($department_ids)) {
                            // If we have the Airtable Directory plugin, use it to get department names
                            if (class_exists('Airtable_Directory_API')) {
                                // Get the API instance - check if it's available globally or create one
                                global $airtable_directory_api;
                                
                                // If global instance doesn't exist, create one
                                if (!$airtable_directory_api) {
                                    $airtable_directory_api = new Airtable_Directory_API();
                                }
                                
                                foreach ($department_ids as $dept_id) {
                                    // Use the built-in method to get department by ID (uses caching)
                                    $department_name_from_api = '';
                                    
                                    try {
                                        // Try to get department using the field ID method first
                                        $department_query_params = array(
                                            'filterByFormula' => "{fldwAR2a55bspWLPt} = '$dept_id'",
                                            'maxRecords' => 1
                                        );
                                        
                                        $departments = $airtable_directory_api->fetch_data(AIRTABLE_DEPARTMENT_TABLE, $department_query_params);
                                        
                                        if ($departments && isset($departments[0]['fields']['Department Name'])) {
                                            $department_name_from_api = $departments[0]['fields']['Department Name'];
                                        } else {
                                            // Fallback: try using the numeric ID method if the field ID doesn't work
                                            $department_record = $airtable_directory_api->get_department_by_id($dept_id);
                                            if ($department_record && isset($department_record['fields']['Department Name'])) {
                                                $department_name_from_api = $department_record['fields']['Department Name'];
                                            }
                                        }
                                    } catch (Exception $e) {
                                        error_log('Error fetching department name for ID ' . $dept_id . ': ' . $e->getMessage());
                                    }
                                    
                                    // Use the department name from API if available, otherwise use a generic heading
                                    $section_heading = !empty($department_name_from_api) ? 
                                        esc_html($department_name_from_api) . ' Staff' : 
                                        'Department Staff (ID: ' . esc_html($dept_id) . ')';
                                    
                                    echo '<h2>' . $section_heading . '</h2>';
                                    echo do_shortcode('[staff_directory department="' . esc_attr($dept_id) . '" show="name,title,email,phone,photo"]');
                                }
                            } else {
                                // Fallback if Airtable Directory plugin is not available
                                if (count($department_ids) === 1) {
                                    echo '<h2>Department Staff</h2>';
                                    echo do_shortcode('[staff_directory department="' . esc_attr($department_ids[0]) . '" show="name,title,email,phone,photo"]');
                                } else {
                                    foreach ($department_ids as $dept_id) {
                                        echo '<h2>Department Staff (ID: ' . esc_html($dept_id) . ')</h2>';
                                        echo do_shortcode('[staff_directory department="' . esc_attr($dept_id) . '" show="name,title,email,phone,photo"]');
                                    }
                                }
                            }
                        }
                    }
                    ?>

                </div><!-- end .entry -->
                
                <div class="clearfix"></div>
                
                <?php 
                    
                    echo '<div class="post-pagination">';
                    wp_link_pages( array( 'before' => '<div class="page-link">', 'after' => '</div>',
                    'link_before' => '<span>', 'link_after' => '</span>', ) );
                    wp_link_pages(array(
                        'before' => '<p>',
                        'after' => '</p>',
                        'next_or_number' => 'next_and_number', # activate parameter overloading
                        'nextpagelink' => esc_html__('Next','citygov'),
                        'previouspagelink' => esc_html__('Previous','citygov'),
                        'pagelink' => '%',
                        'echo' => 1 )
                    );
                    echo '</div>';
                
                    if (is_single()) {get_template_part('/single-info');} 
                    
                    comments_template(); 
                    
                ?>
                
            </div>
    
    
        <?php endwhile; else: ?>
    
            <p><?php esc_html_e('Sorry, no posts matched your criteria','citygov');?>.</p>
    
        <?php endif; ?>
    
                    <div style="clear: both;"></div>
    
        </div><!-- #content -->
    
        <?php 
            // Retrieve department details
            $department_name = get_post_meta(get_the_ID(), 'department_name', true);
            $department_id = get_post_meta(get_the_ID(), 'department_id', true);

            // Store them in global variables
            global $department_data;
            $department_data = array(
                'department_name' => esc_attr($department_name),
                'department_id' => $department_id
            );

            // Call the sidebar
            get_sidebar('department-homepage');
        ?>

    
    </div><!-- end #core -->

</div><!-- end .container -->

<?php get_footer(); ?>