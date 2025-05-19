<?php
/*
Template Name: Sheriff Post Template
Template Post Type: post
*/
?>

<?php get_header(); ?>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<div class="container_alt post tmnf_page sheriff-article">

    <div id="core" class="postbar fullwidth">

        <div id="content_start" class="tmnf_anchor"></div>

        <div id="content" class="twelvecol first">

            <div <?php post_class('item_inn p-border'); ?>>

                <!-- Sheriff Header Section -->
                <?php get_template_part('template-parts/sheriff-header'); ?>

                <div class="entry">

                    <!-- Override breadcrumbs manually -->
                    <div class="main-breadcrumbs subpage-breadcrumbs">
                        <a href="/departments/sheriff">← Sheriff’s Office</a>
                        <a href="/category/sheriff">All Sheriff’s News</a>
                    </div>

                    <h1 itemprop="headline" class="entry-title sheriff-post-title"><?php the_title(); ?></h1>

                    <?php the_content(); ?>

                </div><!-- end .entry -->

                <div class="clearfix"></div>

                <?php
                    // Pagination support (if using <!--nextpage--> tags)
                    echo '<div class="post-pagination">';
                    wp_link_pages([
                        'before' => '<div class="page-link">', 'after' => '</div>',
                        'link_before' => '<span>', 'link_after' => '</span>',
                    ]);
                    echo '</div>';

                    comments_template();
                ?>

                <!-- Sheriff Crime Tips Footer -->
                <?php get_template_part('template-parts/sheriff-footer'); ?>

            </div>

        </div><!-- #content -->

    </div><!-- end #core -->

</div><!-- end .container -->


<?php endwhile; else : ?>

    <p><?php esc_html_e('Sorry, no posts matched your criteria.', 'citygov'); ?></p>

<?php endif; ?>

<?php get_footer(); ?>
