<?php
/**
 * Template for single Document Library Pro documents,
 * styled like the blog post layout but without blog header,
 * and with PDF embed in content area and metadata in sidebar.
 */

use Barn2\Plugin\Document_Library_Pro\Util\Options;
use Barn2\Plugin\Document_Library_Pro\Frontend_Scripts;

add_filter( 'document_library_pro_enable_single_content_customization', '__return_false' );

get_header();

while ( have_posts() ) :
    the_post();
    $document = dlp_get_document( get_the_ID() );
    $display_options = Options::get_document_display_fields();
    $options = Options::get_shortcode_options();
    $pdf_url = $document->get_download_url();
?>

<div id="primary" class="content-area">
  <main id="main" class="site-main">

    <div class="custom-single-post-layout">
      <div class="main-post-content-wrapper">
        <div class="main-post-content">

          <header class="entry-header">
            <h1 class="entry-title"><?php the_title(); ?></h1>
          </header>

          <div class="entry-content">
            <?php
                $file_type = strtolower( $document->get_file_type() );
                $can_embed = $pdf_url && $file_type === 'pdf' && shortcode_exists( 'pdf-embedder' );
                $fallback_image = get_theme_file_uri('/assets/images/pdf-fallback.png'); // Change path if needed
                $is_image = in_array( $file_type, ['jpg', 'jpeg', 'png', 'gif', 'webp'] );
                ?>

                <div id="pdf-container" class="pdf-embed-wrapper">
                    <div id="pdf-loading" class="pdf-loading-message">
                        <p>Loading document...</p>
                        <div class="spinner"></div>
                    </div>

                    <?php if ( $can_embed ) : ?>
                        <div id="pdf-embed" class="pdf-embed-content">
                        <?php echo do_shortcode( '[pdf-embedder url="' . esc_url( $pdf_url ) . '"]' ); ?>
                        </div>
                        <noscript>
                        <div class="pdf-fallback">
                            <img src="<?php echo esc_url( $fallback_image ); ?>" alt="PDF preview not available" />
                            <p>
                                <a href="<?php echo esc_url( $pdf_url ); ?>" download class="download-button">Download the PDF</a>
                                <a href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" class="view-button">View in Browser</a>
                            </p>
                        </div>
                        </noscript>
                    <?php elseif ( $is_image ) : ?>
                        <div class="image-preview">
                            <img src="<?php echo esc_url( $pdf_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" />
                            <p>
                                <a href="<?php echo esc_url( $pdf_url ); ?>" download class="download-button">Download the Image</a>
                                <a href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" class="view-button">View in Browser</a>
                            </p>
                        </div>
                    <?php else : ?>
                        <div class="pdf-fallback">
                        <img src="<?php echo esc_url( $fallback_image ); ?>" alt="Document preview not available" />
                        <p>
                            <a href="<?php echo esc_url( $pdf_url ); ?>" download class="download-button">Download the Document</a>
                            <a href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" class="view-button">View in Browser</a>
                        </p>
                        </div>
                    <?php endif; ?>
                </div>
          </div>

        </div><!-- .main-post-content -->

        <aside class="sticky-sidebar">
          <div class="sidebar-content">

            <?php if ( $document->get_download_url() ) : ?>
              <?php Frontend_Scripts::load_download_count_scripts(); ?>
              <div class="pdf-download sticky-sidebar-item">
                <h3>Document Downloads</h3>
                <?php echo $document->get_download_button(
                  $options['link_text'],
                  $options['link_style'],
                  'direct',
                  $options['link_target']
                ); ?>
              </div>
            <?php endif; ?>

            <div class="dlp-document-meta sticky-sidebar-item">
              <?php if ( $document->get_file_type() && in_array( 'file_type', $display_options, true ) ) : ?>
                <p><strong>File Type:</strong> <?php echo esc_html( $document->get_file_type() ); ?></p>
              <?php endif; ?>

              <?php if ( $document->get_category_list() && in_array( 'doc_categories', $display_options, true ) ) : ?>
                <p><strong>Categories:</strong> <?php echo $document->get_category_list(); ?></p>
              <?php endif; ?>

              <?php if ( $document->get_tag_list() && in_array( 'doc_tags', $display_options, true ) ) : ?>
                <p><strong>Tags:</strong> <?php echo $document->get_tag_list(); ?></p>
              <?php endif; ?>

              <?php if ( $document->get_author_list() && in_array( 'doc_author', $display_options, true ) ) : ?>
                <p><strong>Author:</strong> <?php echo $document->get_author_list(); ?></p>
              <?php endif; ?>

              <?php if ( $document->get_download_count() && in_array( 'download_count', $display_options, true ) ) : ?>
                <p><strong>Downloads:</strong> <?php echo esc_html( $document->get_download_count() ); ?></p>
              <?php endif; ?>
            </div>

          </div>
        </aside><!-- .sticky-sidebar -->
      </div><!-- .main-post-content-wrapper -->
    </div><!-- .custom-single-post-layout -->

  </main><!-- #main -->
</div><!-- #primary -->

<?php
endwhile;
get_footer();
