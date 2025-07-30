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
    color: #666;
    font-style: italic;
}

.form-content {
    display: grid;
    gap: 30px;
}

.form-instructions-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #007cba;
}

.form-instructions h3 {
    margin-top: 0;
    color: #007cba;
}

.form-iframe-section {
    min-height: 800px;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
}

.form-iframe-section iframe {
  width: 100%;
  max-width: 800px; /* Reasonable max width for readability */
  height: 1000px; /* You may need to adjust based on form length or set dynamically */
  margin: 2rem auto; /* Center the form and give some spacing */
  border: none; /* Remove the default iframe border */
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1); /* Soft shadow for subtle depth */
  border-radius: 8px; /* Slight rounding of corners */
  background-color: transparent; /* Keep the background consistent */
  display: block;
}

.form-contact-section {
    background: #e7f3ff;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #007cba;
}

.form-contact-info h3 {
    margin-top: 0;
    color: #007cba;
}

.form-contact-info p {
    margin: 8px 0;
}

.form-contact-info a {
    color: #007cba;
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
    
    .form-iframe-section iframe {
        height: 600px;
    }
}
</style>

<?php get_footer(); ?> 