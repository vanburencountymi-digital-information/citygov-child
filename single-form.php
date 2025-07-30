<?php
/**
 * Template for displaying a single form
 * 
 * This template uses the VBC Form Center helper functions
 * to display a form with its ACF fields.
 */

get_header(); ?>

<div class="form-container">
    <?php while (have_posts()) : the_post(); ?>
        
        <article id="post-<?php the_ID(); ?>" <?php post_class('form-post'); ?>>
            
            <header class="form-header">
                <h1 class="form-title"><?php the_title(); ?></h1>
                
                <?php if (has_excerpt()) : ?>
                    <div class="form-excerpt">
                        <?php the_excerpt(); ?>
                    </div>
                <?php endif; ?>
            </header>

            <div class="form-content">
                
                <?php if (vbc_get_form_instructions()) : ?>
                    <div class="form-instructions-section">
                        <?php vbc_display_form_instructions(); ?>
                    </div>
                <?php endif; ?>

                <div class="form-iframe-section">
                    <?php vbc_display_form_iframe(); ?>
                </div>

                <?php if (vbc_get_contact_name() || vbc_get_contact_email() || vbc_get_contact_phone()) : ?>
                    <div class="form-contact-section">
                        <?php vbc_display_contact_info(); ?>
                    </div>
                <?php endif; ?>

                <?php if (get_the_content()) : ?>
                    <div class="form-description">
                        <?php the_content(); ?>
                    </div>
                <?php endif; ?>

            </div>

        </article>

    <?php endwhile; ?>
</div>

<style>
/* Basic styling for the form template */
.form-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    margin-top: 20px;
}

.form-header {
    margin-bottom: 30px;
    text-align: center;
}

.form-title {
    font-size: 2.5em;
    margin-bottom: 10px;
    color: #333;
}

.form-excerpt {
    font-size: 1.2em;
    color: #000;
    font-style: italic;
}

.form-content {
    display: grid;
    gap: 30px;
}

.form-instructions-section {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #a34733;
}

.form-instructions h3 {
    margin-top: 0;
    color: #a34733;
}

.form-iframe-section {
    min-height: 80vh;
    padding: 20px;
    background: #fff;
}

.form-iframe-section iframe {
  width: 100%;
  max-width: 800px;
  height: 80vh; /* Fixed height - users can scroll the container */
  margin: 0 auto;
  border: none;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
  border-radius: 8px;
  background-color: transparent;
  display: block;
}

.form-contact-section {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #a34733;
}

.form-contact-info h3 {
    margin-top: 0;
    color: #a34733;
}

.form-contact-info p {
    margin: 8px 0;
}

.form-contact-info a {
    color: #a34733;
    text-decoration: none;
}

.form-contact-info a:hover {
    text-decoration: underline;
}

.form-description {
    line-height: 1.6;
}

/* Responsive design */
@media (max-width: 768px) {
    .form-container {
        padding: 10px;
    }
    
    .form-title {
        font-size: 2em;
    }
    
    .form-iframe-section {
        max-height: 70vh; /* Smaller on mobile */
    }
    
    .form-iframe-section iframe {
        height: 800px; /* Slightly taller on mobile for better scrolling */
    }
}
</style>

<script>
// Enhance iframe scrolling experience
document.addEventListener('DOMContentLoaded', function() {
    const iframeSection = document.querySelector('.form-iframe-section');
    const iframe = iframeSection ? iframeSection.querySelector('iframe') : null;
    
    if (iframe) {
        
        // Add smooth scrolling to the container
        iframeSection.style.scrollBehavior = 'smooth';
        
        // Optional: Auto-scroll to top when iframe loads
        iframe.addEventListener('load', function() {
            iframeSection.scrollTop = 0;
        });
    }
});
</script>

<?php get_footer(); ?> 