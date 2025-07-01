<?php
/**
 * Template Name: Custom Single Post With Sticky Sidebar
 * Template Post Type: post
 */

get_header(); ?>

<div id="primary" class="content-area">
  <main id="main" class="site-main">

    <div class="custom-single-post-layout">
      <?php 
        // Define department-specific variables
        $department_name = "Digital Information Department";
        $department_slug = "digital-information";
        $department_news_slug = "digital-information/news";
        $department_logo_url = "/wp-content/uploads/2025/05/DID-Logo.png"; // Still define this as fallback
        $department_address = "219 East Paw Paw Street – Paw Paw, MI 49079";
        $department_phone = "(269) 657-8253";
        $department_fax = ""; // Optional
        $department_email = ""; // Optional
        $department_website = "https://www.digitalinformation.com"; // Optional
        $department_hours = "Monday - Friday, 8:00 AM - 5:00 PM"; // Optional
        $department_description = "The Digital Information Department is responsible for managing and developing digital technologies and platforms for the city."; // Optional
        $department_leadership = [
            ['name' => 'Jerry Happel', 'title' => 'Director of Digital Innovation'],
        ];
        $use_html_logo = true; // Set this to true to use the HTML logo

        get_template_part('template-parts/headers/department-blog-post-header', null, compact('department_name', 'department_slug', 'department_news_slug', 'department_logo_url', 'department_address', 'department_phone', 'department_fax', 'department_email', 'department_website', 'department_hours', 'department_description')); 
      ?>
        <!-- Department Navigation -->
        <div class="department-navigation">
        <a class="back-btn" href="/departments/departments-offices/<?php echo esc_attr($department_slug); ?>/">
            Back to <?php echo $department_name; ?>
        </a>
        <a class="news-btn" href="/departments/departments-offices/<?php echo esc_attr($department_news_slug); ?>/">
            More News from <?php echo $department_name; ?>
        </a>
          </div>
      <div class="main-post-content-wrapper">
        <div class="main-post-content">

          
          <?php if ( has_post_thumbnail() ) : ?>
            <div class="post-featured-image">
              <?php the_post_thumbnail( 'large' ); ?>
            </div>
          <?php endif; ?>

          <header class="entry-header">
            <h1 class="entry-title"><?php the_title(); ?></h1>
            <div class="entry-meta">
              <span class="posted-on"><?php echo get_the_date(); ?></span>
              <span class="byline"> / <?php the_author(); ?></span>
              <span class="reading-time"> / <?php echo do_shortcode('[rt_reading_time label="Reading Time:" postfix="minutes" postfix_singular="minute"]'); ?></span>
            </div>
          </header>

          <div class="entry-content">
            <?php the_content(); ?>
          </div>

        </div><!-- .main-post-content -->

        <aside class="sticky-sidebar">
        <?php
        // Allow per-post custom fields for audio and PDF
        $audio_src = get_post_meta( get_the_ID(), 'sidebar_audio', true );
        $pdf_link  = get_post_meta( get_the_ID(), 'sidebar_pdf',   true );
        $cover      = get_post_meta( get_the_ID(), 'podcast_cover',      true );
        $title      = get_post_meta( get_the_ID(), 'podcast_title',      true );
        $host       = get_post_meta( get_the_ID(), 'podcast_host',       true );
        $episode    = get_post_meta( get_the_ID(), 'podcast_episode',    true );
        $transcript = get_post_meta( get_the_ID(), 'podcast_transcript', true );
      

        if ( is_active_sidebar( 'post-sidebar' ) ) :

            dynamic_sidebar( 'post-sidebar' );

        else :

            echo '<div class="sidebar-content">';

            // // Debug section to show available podcast metadata
            // echo '<div class="debug-info" style="background: #f8f8f8; border: 1px solid #ddd; padding: 10px; margin-bottom: 15px; font-size: 12px;">';
            // echo '<h4 style="margin-top: 0;">Podcast Debug Info:</h4>';
            // echo '<ul style="margin: 0; padding-left: 15px;">';
            // echo '<li>Audio Source: ' . (empty($audio_src) ? '<span style="color:red">MISSING</span>' : esc_html($audio_src)) . '</li>';
            // echo '<li>Cover Image: ' . (empty($cover) ? '<span style="color:#999">Not set</span>' : esc_html($cover)) . '</li>';
            // echo '<li>Title: ' . (empty($title) ? '<span style="color:#999">Not set</span>' : esc_html($title)) . '</li>';
            // echo '<li>Host: ' . (empty($host) ? '<span style="color:#999">Not set</span>' : esc_html($host)) . '</li>';
            // echo '<li>Episode: ' . (empty($episode) ? '<span style="color:#999">Not set</span>' : esc_html($episode)) . '</li>';
            // echo '<li>Transcript: ' . (empty($transcript) ? '<span style="color:#999">Not set</span>' : esc_html($transcript)) . '</li>';
            // echo '</ul>';
            // echo '</div>';

            // 1) Load your podcast‐player partial if there's an audio URL
            if ( $audio_src ) {
                get_template_part(
                    'template-parts/modules/podcast-player',
                    null,
                    compact( 'audio_src', 'cover', 'title', 'host', 'episode', 'transcript')
                );
            } else {
                // echo '<div class="no-audio-message" style="padding: 15px; background: #fff8e1; border-left: 4px solid #ffc107; margin-bottom: 20px;">';
                // echo '<h3 style="margin-top: 0; color: #795548;">No Audio Available</h3>';
                // echo '<p>No podcast audio source was found for this post. To add audio, set the <code>podcast_audio_src</code> custom field.</p>';
                // echo '</div>';
            }

            // 2) Fallback PDF link
            $pdf_link = get_post_meta( get_the_ID(), 'sidebar_pdf', true );
            if ( $pdf_link ) : ?>
            <div class="pdf-download sticky-sidebar-item">
                <h3>Document Downloads</h3>
                <a href="<?php echo esc_url( $pdf_link ); ?>"
                class="download-button"
                download>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="download-icon">
                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                </svg>
                Download Overview (PDF)
                </a>
            </div>
            <?php endif;

            echo '</div>';

        endif;
        ?>
        </aside><!-- .sticky-sidebar -->
      </div><!-- .main-post-content-wrapper -->
    </div><!-- .custom-single-post-layout -->

    <!-- Similar Posts Section -->
    <div class="similar-posts-section">
      <h2 class="similar-posts-title">Similar Posts</h2>
      <div class="similar-posts-container">
        <?php
        // Get current post tags
        $post_tags = wp_get_post_tags(get_the_ID());
        
        if ($post_tags) {
          $tag_ids = array();
          foreach($post_tags as $tag) $tag_ids[] = $tag->term_id;
          
          // Query similar posts based on tags
          $args = array(
            'tag__in' => $tag_ids,
            'post__not_in' => array(get_the_ID()),
            'posts_per_page' => 3, // Show 3 similar posts
            'orderby' => 'date',
            'order' => 'DESC'
          );
          
          $similar_query = new WP_Query($args);
          
          if ($similar_query->have_posts()) :
            while ($similar_query->have_posts()) : $similar_query->the_post();
            ?>
              <div class="similar-post">
                <?php if (has_post_thumbnail()) : ?>
                  <a href="<?php the_permalink(); ?>" class="similar-post-thumbnail">
                    <?php the_post_thumbnail('thumbnail'); ?>
                  </a>
                <?php endif; ?>
                <div class="similar-post-content">
                  <h3 class="similar-post-title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                  </h3>
                  <div class="similar-post-meta">
                    <span class="similar-post-date"><?php echo get_the_date(); ?></span>
                  </div>
                </div>
              </div>
            <?php
            endwhile;
          else:
            echo '<p>No similar posts found.</p>';
          endif;
          
          wp_reset_postdata();
        }
        ?>
      </div>
    </div><!-- .similar-posts-section -->

    <!-- Next/Previous Post Navigation -->
    <div class="post-navigation-section">
      <div class="post-navigation-container">
        <?php
        // Get posts in the same category
        $categories = get_the_category();
        $category_ids = array();
        foreach ($categories as $category) {
          $category_ids[] = $category->term_id;
        }
        
        // Previous post in same category
        $prev_post = get_previous_post(true, '', 'category');
        // Next post in same category
        $next_post = get_next_post(true, '', 'category');
        ?>
        
        <div class="post-navigation">
          <?php if (!empty($prev_post)) : ?>
            <div class="nav-previous">
              <span class="nav-subtitle">Previous Post</span>
              <a href="<?php echo get_permalink($prev_post->ID); ?>" class="nav-link">
                <?php echo get_the_post_thumbnail($prev_post->ID, 'thumbnail'); ?>
                <span class="nav-title"><?php echo $prev_post->post_title; ?></span>
              </a>
            </div>
          <?php endif; ?>
          
          <?php if (!empty($next_post)) : ?>
            <div class="nav-next">
              <span class="nav-subtitle">Next Post</span>
              <a href="<?php echo get_permalink($next_post->ID); ?>" class="nav-link">
                <?php echo get_the_post_thumbnail($next_post->ID, 'thumbnail'); ?>
                <span class="nav-title"><?php echo $next_post->post_title; ?></span>
              </a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div><!-- .post-navigation-section -->

  </main><!-- #main -->
</div><!-- #primary -->

<?php get_footer(); ?>
