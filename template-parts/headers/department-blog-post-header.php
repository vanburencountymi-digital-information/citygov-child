<?php
/**
 * Reusable department header template part.
 * Expects these variables to be defined before inclusion:
 * $department_name
 * $department_slug
 * $department_news_slug
 * $department_logo_url (optional - if not using HTML logo)
 * $department_address
 * $department_phone
 * $department_fax (optional)
 * $department_leadership = [
 *    ['name' => '...', 'title' => '...'],
 *    ...
 * ]
 * $use_html_logo (boolean - set to true to use HTML logo instead of image)
 */

// Extract variables from $args if they exist
if (isset($args) && is_array($args)) {
    extract($args);
}

// Default values for variables if not set
$department_name = isset($department_name) ? $department_name : '';
$department_slug = isset($department_slug) ? $department_slug : '';
$department_news_slug = isset($department_news_slug) ? $department_news_slug : '';
$department_logo_url = isset($department_logo_url) ? $department_logo_url : '';
$department_address = isset($department_address) ? $department_address : '';
$department_phone = isset($department_phone) ? $department_phone : '';
$department_fax = isset($department_fax) ? $department_fax : '';
$department_leadership = [];
?>

<header class="department-header" role="banner" aria-label="<?php echo esc_attr($department_name); ?> header">
   <div class="department-header__container">
        <!-- Top row: logo + main info -->
        <div class="department-header__top">
            <div class="department-header__logo">
                <?php if (isset($use_html_logo) && $use_html_logo) : ?>
                    <!-- HTML Logo -->
                    <div class="logo-container">
                      <h1 class="logo-text">
                        <span class="logo-word digital">Digital</span>
                        <span class="logo-word information">Information</span>
                      </h1>
                      <div class="logo-department">DEPARTMENT</div>
                    </div>
                <?php else : ?>
                    <!-- Image Logo -->
                    <img 
                        src="<?php echo esc_url($department_logo_url); ?>" 
                        alt="Logo of <?php echo esc_attr($department_name); ?>" 
                    />
                <?php endif; ?>
            </div>
            <div class="department-header__info">
                <p class="department-header__address"><?php echo $department_address; ?></p>
                <p class="department-header__contact">
                    Telephone <?php echo $department_phone; ?>
                    <?php if (!empty($department_fax)) : ?>
                        – Fax <?php echo $department_fax; ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <?php if (!empty($department_leadership)) : ?>
        <!-- Bottom row: officer names -->
        <div class="department-header__bottom">
            <?php foreach ($department_leadership as $officer) : ?>
                <div class="department-header__officer">
                    <p class="officer-name"><?php echo $officer['name']; ?></p>
                    <p class="officer-title"><?php echo $officer['title']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
   </div>
</header>
