<?php
/**
 * Template for displaying search results
 * 
 * This template shows search results as cards, similar to other index pages
 */

get_header(); 

// Get current filter
$current_filter = isset($_GET['post_type_filter']) ? sanitize_text_field($_GET['post_type_filter']) : '';

// If we have a post type filter, we need to modify the query
if (!empty($current_filter)) {
    // Create a new query for filtered results
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $filtered_query = new WP_Query(array(
        's' => get_search_query(),
        'post_type' => $current_filter,
        'paged' => $paged,
        'posts_per_page' => get_option('posts_per_page')
    ));
    
    // Get distinct post types from original search results (before filtering)
    global $wp_query;
    $post_types = array();
    if ($wp_query->have_posts()) {
        $temp_posts = $wp_query->posts;
        foreach ($temp_posts as $post) {
            $post_type = $post->post_type;
            if (!in_array($post_type, $post_types)) {
                $post_types[] = $post_type;
            }
        }
    }
} else {
    // No filter applied, use original query
    global $wp_query;
    $filtered_query = $wp_query;
    
    // Get distinct post types from current search results
    $post_types = array();
    if ($wp_query->have_posts()) {
        $temp_posts = $wp_query->posts;
        foreach ($temp_posts as $post) {
            $post_type = $post->post_type;
            if (!in_array($post_type, $post_types)) {
                $post_types[] = $post_type;
            }
        }
        // Reset the query so we can loop through posts again
        rewind_posts();
    }
}
?>

<div class="search-results-container">
    <header class="search-results-header">
        <h1 class="search-results-title">
            Search Results for: <span class="search-query"><?php echo esc_html(get_search_query()); ?></span>
        </h1>
        <p class="search-results-count">
            <?php 
            $total_results = $filtered_query->found_posts;
            if ($total_results == 1) {
                echo '1 result found';
            } else {
                echo number_format($total_results) . ' results found';
            }
            ?>
        </p>
        
        <div class="new-search-container">
            <form method="get" action="<?php echo esc_url(home_url('/')); ?>" class="new-search-form">
                <input type="hidden" name="s" value="">
                <label for="new_search" class="new-search-label">Search for something else:</label>
                <div class="new-search-input-group">
                    <input type="text" name="s" id="new_search" class="new-search-input" placeholder="Enter your search terms..." value="">
                    <button type="submit" class="new-search-submit">Search</button>
                </div>
            </form>
        </div>
    </header>

    <?php if ($filtered_query->have_posts() && count($post_types) > 1) : ?>
        <div class="search-filter-container">
            <form method="get" class="search-filter-form">
                <input type="hidden" name="s" value="<?php echo esc_attr(get_search_query()); ?>">
                <label for="post_type_filter" class="filter-label">Filter by content type:</label>
                <select name="post_type_filter" id="post_type_filter" class="post-type-filter">
                    <option value="">All content types</option>
                    <?php foreach ($post_types as $post_type) : 
                        $post_type_obj = get_post_type_object($post_type);
                        $selected = ($current_filter === $post_type) ? 'selected' : '';
                    ?>
                        <option value="<?php echo esc_attr($post_type); ?>" <?php echo $selected; ?>>
                            <?php echo esc_html($post_type_obj->labels->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="filter-submit">Filter</button>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($filtered_query->have_posts()) : ?>
        
        <div class="search-results-grid">
            <?php while ($filtered_query->have_posts()) : $filtered_query->the_post(); ?>
                
                <article id="post-<?php the_ID(); ?>" <?php post_class('search-result-card'); ?>>
                    
                    <div class="search-result-card-content">
                        <h2 class="search-result-card-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                        
                        <div class="search-result-card-meta">
                            <span class="search-result-card-type">
                                <?php 
                                $post_type = get_post_type();
                                $post_type_obj = get_post_type_object($post_type);
                                echo esc_html($post_type_obj->labels->singular_name);
                                ?>
                            </span>
                            <span class="search-result-card-date">
                                <?php echo get_the_date(); ?>
                            </span>
                        </div>
                        
                        <?php if (has_excerpt()) : ?>
                            <div class="search-result-card-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                        <?php else : ?>
                            <div class="search-result-card-excerpt">
                                <?php 
                                $content = get_the_content();
                                $content = strip_tags($content);
                                echo wp_trim_words($content, 25, '...');
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="search-result-card-action">
                            <a href="<?php the_permalink(); ?>" class="search-result-card-button">
                                Read More
                            </a>
                        </div>
                    </div>
                    
                </article>
                
            <?php endwhile; ?>
        </div>
        
        <?php
        // Custom pagination for filtered results
        $big = 999999999;
        $pagination_args = array(
            'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
            'format' => '?paged=%#%',
            'current' => max(1, get_query_var('paged')),
            'total' => $filtered_query->max_num_pages,
            'mid_size' => 2,
            'prev_text' => '&laquo; Previous',
            'next_text' => 'Next &raquo;',
            'add_args' => array(
                's' => get_search_query(),
                'post_type_filter' => $current_filter
            )
        );
        
        echo '<div class="pagination">';
        echo paginate_links($pagination_args);
        echo '</div>';
        ?>
        
    <?php else : ?>
        
        <div class="no-search-results">
            <h2>No Results Found</h2>
            <p>Sorry, but nothing matched your search terms. Please try again with some different keywords.</p>
            
            <div class="search-form-container">
                <?php get_search_form(); ?>
            </div>
            
            <div class="search-suggestions">
                <h3>Search Suggestions:</h3>
                <ul>
                    <li>Make sure all words are spelled correctly</li>
                    <li>Try different keywords</li>
                    <li>Try more general keywords</li>
                    <li>Try fewer keywords</li>
                </ul>
            </div>
        </div>
        
    <?php endif; ?>
    
    <?php 
    // Reset post data
    wp_reset_postdata();
    ?>
</div>

<style>
/* Search results page styling */
.search-results-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.search-results-header {
    text-align: center;
    margin-bottom: 40px;
    padding: 30px 0;
    border-bottom: 2px solid #e0e0e0;
}

.search-results-title {
    font-size: 2.5em;
    margin-bottom: 10px;
    color: #333;
}

.search-query {
    color: #a3473b;
    font-weight: 600;
}

.search-results-count {
    font-size: 1.1em;
    color: #666;
    margin: 0 0 30px 0;
}

/* New search form styling */
.new-search-container {
    margin-top: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
}

.new-search-form {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
}

.new-search-label {
    font-weight: 600;
    color: #333;
    margin: 0;
    font-size: 1.1em;
}

.new-search-input-group {
    display: flex;
    gap: 10px;
    width: 100%;
    max-width: 500px;
}

.new-search-input {
    flex: 1;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1em;
    background: white;
}

.new-search-input:focus {
    outline: none;
    border-color: #a3473b;
    box-shadow: 0 0 0 2px rgba(163, 71, 59, 0.2);
}

.new-search-submit {
    background: #a3473b;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: background-color 0.2s ease;
    white-space: nowrap;
}

.new-search-submit:hover {
    background: #902f29;
}

/* Search filter styling */
.search-filter-container {
    margin-bottom: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
}

.search-filter-form {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.filter-label {
    font-weight: 600;
    color: #333;
    margin: 0;
}

.post-type-filter {
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    background: white;
    font-size: 1em;
    min-width: 200px;
    cursor: pointer;
}

.post-type-filter:focus {
    outline: none;
    border-color: #a3473b;
    box-shadow: 0 0 0 2px rgba(163, 71, 59, 0.2);
}

.filter-submit {
    background: #a3473b;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: background-color 0.2s ease;
}

.filter-submit:hover {
    background: #902f29;
}

.search-results-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

.search-result-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 25px;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.search-result-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.search-result-card-title {
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 1.4em;
    line-height: 1.3;
}

.search-result-card-title a {
    color: #a3473b;
    text-decoration: none;
}

.search-result-card-title a:hover {
    text-decoration: underline;
}

.search-result-card-meta {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    font-size: 0.9em;
    color: #666;
}

.search-result-card-type {
    background: #6d5c52;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: 600;
    color: #fff;
}

.search-result-card-date {
    color: #888;
}

.search-result-card-excerpt {
    color: #666;
    margin-bottom: 20px;
    line-height: 1.5;
}

.search-result-card-action {
    text-align: center;
}

.search-result-card-button {
    display: inline-block;
    background: #a3473b;
    color: white;
    padding: 12px 24px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    transition: background-color 0.2s ease;
}

.search-result-card-button:hover {
    background: #902f29;
    color: white;
    text-decoration: none;
}

.no-search-results {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.no-search-results h2 {
    margin-bottom: 15px;
    color: #333;
    font-size: 2em;
}

.no-search-results p {
    font-size: 1.1em;
    margin-bottom: 30px;
}

.search-form-container {
    max-width: 500px;
    margin: 0 auto 30px;
}

.search-form-container .search-form {
    display: flex;
    gap: 10px;
}

.search-form-container .search-field {
    flex: 1;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1em;
}

.search-form-container .search-submit {
    background: #a3473b;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: background-color 0.2s ease;
}

.search-form-container .search-submit:hover {
    background: #902f29;
}

.search-suggestions {
    max-width: 600px;
    margin: 0 auto;
    text-align: left;
}

.search-suggestions h3 {
    color: #333;
    margin-bottom: 15px;
}

.search-suggestions ul {
    list-style: none;
    padding: 0;
}

.search-suggestions li {
    padding: 8px 0;
    border-bottom: 1px solid #eee;
    color: #666;
}

.search-suggestions li:last-child {
    border-bottom: none;
}

/* Pagination styling */
.pagination {
    text-align: center;
    margin-top: 40px;
}

.pagination .page-numbers {
    display: inline-block;
    padding: 8px 12px;
    margin: 0 5px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-decoration: none;
    color: #a3473b;
}

.pagination .page-numbers.current {
    background: #a3473b;
    color: white;
    border-color: #a3473b;
}

.pagination .page-numbers:hover {
    background: #f8f9fa;
}

/* Responsive design */
@media (max-width: 768px) {
    .search-results-container {
        padding: 10px;
    }
    
    .search-results-title {
        font-size: 2em;
    }
    
    .search-filter-form {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-label {
        text-align: center;
    }
    
    .post-type-filter {
        min-width: auto;
    }
    
    .new-search-input-group {
        flex-direction: column;
        gap: 10px;
    }
    
    .new-search-submit {
        width: 100%;
    }
    
    .search-results-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .search-result-card {
        padding: 20px;
    }
    
    .search-result-card-meta {
        flex-direction: column;
        gap: 8px;
    }
    
    .search-form-container .search-form {
        flex-direction: column;
    }
}
</style>

<script>
// Auto-submit form when dropdown changes
document.addEventListener('DOMContentLoaded', function() {
    const filterSelect = document.getElementById('post_type_filter');
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }
});
</script>

<?php get_footer(); ?> 