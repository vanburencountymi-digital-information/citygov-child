<?php
/**
 * Template for displaying the forms archive
 * 
 * This template shows all available forms with their categories
 */

get_header(); ?>

<div class="forms-archive-container">
    <header class="forms-archive-header">
        <h1 class="forms-archive-title">County Forms</h1>
        <p class="forms-archive-description">Find and complete the forms you need for Van Buren County services.</p>
    </header>

    <?php if (have_posts()) : ?>
        
        <div class="forms-grid">
            <?php while (have_posts()) : the_post(); ?>
                
                <article id="post-<?php the_ID(); ?>" <?php post_class('form-card'); ?>>
                    
                    <div class="form-card-content">
                        <h2 class="form-card-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                        
                        <?php if (has_excerpt()) : ?>
                            <div class="form-card-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (vbc_get_form_instructions()) : ?>
                            <div class="form-card-instructions">
                                <?php 
                                $instructions = vbc_get_form_instructions();
                                echo wp_trim_words($instructions, 20, '...');
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (vbc_get_contact_name() || vbc_get_contact_email()) : ?>
                            <div class="form-card-contact">
                                <strong>Contact:</strong> 
                                <?php 
                                if (vbc_get_contact_name()) {
                                    echo esc_html(vbc_get_contact_name());
                                }
                                if (vbc_get_contact_email()) {
                                    echo ' (' . esc_html(vbc_get_contact_email()) . ')';
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="form-card-categories">
                            <?php
                            $categories = get_the_terms(get_the_ID(), 'form_category');
                            if ($categories && !is_wp_error($categories)) {
                                echo '<strong>Categories:</strong> ';
                                $cat_names = array();
                                foreach ($categories as $category) {
                                    $cat_names[] = '<a href="' . get_term_link($category) . '">' . esc_html($category->name) . '</a>';
                                }
                                echo implode(', ', $cat_names);
                            }
                            ?>
                        </div>
                        
                        <div class="form-card-action">
                            <a href="<?php the_permalink(); ?>" class="form-card-button">
                                Complete Form
                            </a>
                        </div>
                    </div>
                    
                </article>
                
            <?php endwhile; ?>
        </div>
        
        <?php
        // Pagination
        the_posts_pagination(array(
            'mid_size' => 2,
            'prev_text' => '&laquo; Previous',
            'next_text' => 'Next &raquo;',
        ));
        ?>
        
    <?php else : ?>
        
        <div class="no-forms-found">
            <h2>No Forms Available</h2>
            <p>No forms have been published yet. Please check back later.</p>
        </div>
        
    <?php endif; ?>
</div>

<style>
/* Archive page styling */
.forms-archive-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.forms-archive-header {
    text-align: center;
    margin-bottom: 40px;
}

.forms-archive-title {
    font-size: 2.5em;
    margin-bottom: 10px;
    color: #333;
}

.forms-archive-description {
    font-size: 1.2em;
    color: #666;
}

.forms-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

.form-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 25px;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.form-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.form-card-title {
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 1.4em;
}

.form-card-title a {
    color: #007cba;
    text-decoration: none;
}

.form-card-title a:hover {
    text-decoration: underline;
}

.form-card-excerpt {
    color: #666;
    margin-bottom: 15px;
    line-height: 1.5;
}

.form-card-instructions {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 15px;
    font-size: 0.9em;
    color: #555;
    border-left: 3px solid #007cba;
}

.form-card-contact {
    margin-bottom: 15px;
    font-size: 0.9em;
    color: #666;
}

.form-card-categories {
    margin-bottom: 20px;
    font-size: 0.9em;
    color: #666;
}

.form-card-categories a {
    color: #007cba;
    text-decoration: none;
}

.form-card-categories a:hover {
    text-decoration: underline;
}

.form-card-action {
    text-align: center;
}

.form-card-button {
    display: inline-block;
    background: #007cba;
    color: white;
    padding: 12px 24px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    transition: background-color 0.2s ease;
}

.form-card-button:hover {
    background: #005a8b;
    color: white;
    text-decoration: none;
}

.no-forms-found {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.no-forms-found h2 {
    margin-bottom: 15px;
    color: #333;
}

/* Pagination styling */
.navigation.pagination {
    text-align: center;
    margin-top: 40px;
}

.navigation.pagination .nav-links {
    display: inline-flex;
    gap: 10px;
}

.navigation.pagination .page-numbers {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-decoration: none;
    color: #007cba;
}

.navigation.pagination .page-numbers.current {
    background: #007cba;
    color: white;
    border-color: #007cba;
}

.navigation.pagination .page-numbers:hover {
    background: #f8f9fa;
}

/* Responsive design */
@media (max-width: 768px) {
    .forms-archive-container {
        padding: 10px;
    }
    
    .forms-archive-title {
        font-size: 2em;
    }
    
    .forms-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .form-card {
        padding: 20px;
    }
}
</style>

<?php get_footer(); ?> 