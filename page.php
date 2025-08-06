<?php
/*
Template Name: Right Sidebar Layout
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

    	<div class="breadcrumbs">
        
        	<?php citygov_breadcrumbs()?>
            
        </div>

        <h1 itemprop="headline" class="entry-title"><?php the_title(); ?></h1>
    
    </div>
        
</div>

<div class="container_alt post tmnf_page">

    <div id="core" class="postbar postbarRight">
    
    	<div id="content_start" class="tmnf_anchor"></div>
    
        <div id="content" class="eightcol first">
        
            <div <?php post_class('item_inn p-border'); ?>>
    
				<?php if (is_singular( 'post' )) {?>
            
                    <div class="meta-single p-border">
                        
                        <?php citygov_meta_full(); ?>
                        
                    </div>
                
                <?php } ?>
        
                <div class="clearfix"></div>
                
                <div class="entry">
                    
                    <?php the_content();  ?>
                    
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
        
        <?php get_sidebar(); ?>
    
    </div><!-- end #core -->

</div><!-- end .container -->

<?php get_footer(); ?>