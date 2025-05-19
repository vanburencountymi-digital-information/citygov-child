<?php
/*
Template Name: Department Subpage (No Sidebar)
Template Post Type: post, page, event
*/
?>
<?php get_header(); ?>
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<div class="container_alt post tmnf_page compact-article">

    <div id="core" class="postbarLeft postbarLeftNarrow">
    
        <div id="content_start" class="tmnf_anchor"></div>

        <div id="content" class="fullcontent">

            <div <?php post_class('item_inn p-border'); ?>>

                <?php if (is_single()) { ?>
                    <div class="meta-single p-border">
                        <?php citygov_meta_full(); ?>
                    </div>
                <?php } ?>

                <div class="clearfix"></div>

                <div class="entry">
                    <div class="main-breadcrumbs subpage-breadcrumbs">
                        <?php citygov_breadcrumbs(); ?>
                    </div>

                    <h1 itemprop="headline" class="entry-title"><?php the_title(); ?></h1>
                    <?php the_content(); ?>
                </div><!-- end .entry -->

                <div class="clearfix"></div>

                <?php 
                    echo '<div class="post-pagination">';
                    wp_link_pages(array(
                        'before' => '<div class="page-link">', 'after' => '</div>',
                        'link_before' => '<span>', 'link_after' => '</span>',
                    ));
                    wp_link_pages(array(
                        'before' => '<p>',
                        'after' => '</p>',
                        'next_or_number' => 'next_and_number',
                        'nextpagelink' => esc_html__('Next','citygov'),
                        'previouspagelink' => esc_html__('Previous','citygov'),
                        'pagelink' => '%',
                        'echo' => 1
                    ));
                    echo '</div>';

                    if (is_single()) { get_template_part('/single-info'); }

                    comments_template(); 
                ?>

            </div>

        <?php endwhile; else: ?>

            <p><?php esc_html_e('Sorry, no posts matched your criteria','citygov'); ?></p>

        <?php endif; ?>

            <div style="clear: both;"></div>

        </div><!-- #content -->

    </div><!-- end #core -->

</div><!-- end .container -->

<style>

</style>

<?php get_footer(); ?>
