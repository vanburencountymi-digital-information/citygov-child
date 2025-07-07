
        
		<?php if ( is_active_sidebar( 'tmnf-footer-1' ) ) { ?>
    
    <div class="footer-top-row"> 
        <div class="foocol first"> 
        
            <div class="footer-logo">

                 <?php  $themnific_redux = get_option( 'themnific_redux' ); if(empty($themnific_redux['tmnf-footer-logo']['url'])) { }
                        
                    else { ?>
                                
                        <a class="logo" href="<?php echo esc_url(home_url('/')); ?>">
                        
                            <img class="tranz" src="<?php echo esc_url($themnific_redux['tmnf-footer-logo']['url']);?>" alt="<?php bloginfo('name'); ?>"/>
                                
                        </a>
                        
                <?php } ?>
                    
            </div><div class="clearfix"></div> 
        
            <?php dynamic_sidebar('tmnf-footer-1')?>
            
        </div>
    </div>

<?php } ?>

<div class="footer-bottom-row">
    <?php if ( is_active_sidebar( 'tmnf-footer-2' ) ) { ?>

        <div class="foocol sec"> 
        
            <?php dynamic_sidebar('tmnf-footer-2')?>
            
        </div>

    <?php } ?>


    <?php if ( is_active_sidebar( 'tmnf-footer-3' ) ) { ?>

        <div class="foocol">
        
            <?php dynamic_sidebar('tmnf-footer-3') ?>
            
        </div>

    <?php } ?>


    <?php if ( is_active_sidebar( 'tmnf-footer-4' ) ) { ?>

        <div id="foo-spec" class="foocol last"> 
        
            <?php dynamic_sidebar('tmnf-footer-4') ?>
            
        </div>

    <?php } ?>
</div>