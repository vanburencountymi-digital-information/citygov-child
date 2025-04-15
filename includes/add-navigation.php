<?php
if ( function_exists('has_nav_menu') && has_nav_menu('add-menu') ) { 
	wp_nav_menu( array( 'depth' => 2, 'sort_column' => 'menu_order', 'container' => 'ul', 'menu_class' => 'nav tranz', 'menu_id' => 'add-nav' , 'theme_location' => 'add-menu','walker'	=> new Aria_Walker_Nav_Menu(),'items_wrap' => '<ul id="%1$s" class="%2$s" role="menubar">%3$s</ul>',
));
} ?>