<div class="clearfix"></div>

    <div id="footer" role="contentinfo">
        
        <div class="container_alt container_vis"> 
        
			<?php get_template_part('/includes/uni-bottombox'); ?>
            
        </div> 
        
        <div class="clearfix"></div> 
        
        <div class="container_vis">
        
        	<div id="footop" class="footop <?php $themnific_redux = get_option( 'themnific_redux' ); if(empty($themnific_redux['tmnf-footer-editor'])) { } else { echo 'populated';} ?>">
                        
            	<div class="footop-right">

        			<?php get_template_part('/includes/uni-social' ); ?>   
                    
                </div>
            
                <?php 
					if(empty($themnific_redux['tmnf-footer-editor'])) { } else {
						echo '<h2 class="footer_text">' . wp_kses_post($themnific_redux['tmnf-footer-editor']). '</h2>';
					}
				?>   
        
        	</div><!-- end #footop -->
            
        </div>  <!-- end .container_vis --> 
        
        <div class="clearfix"></div> 

		<div class="footer-menu">
        
			<div class="container">
                
            	<?php if ( function_exists('has_nav_menu') && has_nav_menu('bottom-menu') ) {wp_nav_menu( array( 'depth' => 1, 'sort_column' => 'menu_order', 'container' => 'ul', 'menu_class' => 'bottom-menu', 'menu_id' => '' , 'theme_location' => 'bottom-menu') );}  ?>

                <?php $themnific_redux = get_option('themnific_redux');
                    if (!empty($themnific_redux['tmnf-footer-credits'])) {
                        $footer_text = wp_kses_post($themnific_redux['tmnf-footer-credits']);
                        $copyright = '&copy; ' . date('Y') . ' ' . get_bloginfo('name') . ' &bull; ';
                        echo '<div class="footer_credits">' . $copyright . ' ' . $footer_text . '</div>';
                    }
                ?>
                
            </div>   
            
		</div><!-- end #footer-logo -->
        
		<div class="clearfix"></div> 
            
    </div><!-- /#footer  -->
    
<div id="curtain" class="tranz">
	
	<?php get_search_form();?>
    
    <a class='curtainclose' href="<?php esc_url('#'); ?>" ><i class="fa fa-times"></i><span class="screen-reader-text"><?php echo _x( 'Close Search Window', 'label', 'citygov' ); ?></span></a>
    
</div>
    
<div class="scrollTo_top ribbon">

    <a title="<?php esc_attr_e('Scroll to top','citygov');?>" class="rad" href="<?php esc_url('#'); ?>">&uarr;</a>
    
</div>
</div><!-- /.upper class  -->
</div><!-- /.wrapper  -->
<?php get_template_part('partials/exit-modal'); ?>
<?php wp_footer(); ?>
</body>
</html>