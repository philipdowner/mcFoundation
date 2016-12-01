<?php
/**
* SIDEBARS
* Sidebar related theme functions 
*
* @author Philip Downer <philip@manifestbozeman.com>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @version v1.0
*/

/**
* Default Sidebars
*
* @filtered mcFoundation_default_sidebar_args
* @filtered mcFoundation_default_sidebars
*/
function mcFoundation_default_sidebars() {
	
	$defaults = apply_filters('mcFoundation_default_sidebar_args', array(
		'id' => 'default',
		'name' => 'Default Sidebar',
		'description' => 'Used when no other sidebars are applicable',
		'before_widget' => '<li id="%1$s" class="widget %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h5 class="widgetTitle">',
		'after_title' => '</h5>'
	));
	
	$sidebars = array();
	
	//DEFAULT SIDEBAR
	$sidebars['default'] = $defaults;
	
	//FRONT PAGE SIDEBAR
	$sidebars['front'] = array_merge($defaults, array('id' => 'front', 'name' => 'Home Page', 'description' => 'Shown on the home page only.'));
	
	//SETUP THE PAGES SIDEBAR
	$sidebars['pages'] = array_merge($defaults, array('id' => 'pages', 'name' => 'Pages', 'description' => 'Shown on each page.'));
	
	//SINGLE POSTS SIDEBAR
	$sidebars['single'] = array_merge($defaults, array('id' => 'single', 'name' => 'Posts', 'description' => 'Shown on single posts only.'));
	
	$sidebars = apply_filters('mcFoundation_default_sidebars', $sidebars);
	
	foreach( $sidebars as $sidebar ) {
		register_sidebar($sidebar);
	}
}//mc_default_sidebars()

/**
* MC GET SIDEBAR
* Based on the $post global, attempts to choose a sidebar
*
* @filtered mc_get_sidebar
*/
function mcFoundation_get_sidebar() {
	global $post;
	
	$sidebar = 'default';
	
	if( is_front_page() && is_active_sidebar('front') ) {
		$sidebar = 'front';
	}
	elseif( is_page() && is_active_sidebar('pages') ) {
		$sidebar = 'pages';
	}
	elseif( is_single() && is_active_sidebar('single') ) {
		$sidebar = 'single';
	}
	
	$sidebar = apply_filters('mcFoundation_get_sidebar', $sidebar);
	
	dynamic_sidebar($sidebar);
}