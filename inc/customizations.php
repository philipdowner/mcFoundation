<?php
/**
* CUSTOMIZATIONS
* Theme customizations specific to this client.
*
* @author Philip Downer <philip@manifestbozeman.com>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @version v1.0
*/

/**
* Top-Level Filters & Actions
*/
add_action('mcFoundation_after_body', 'mc_primary_header', 10);//Include the primary header
add_action('mcFoundation_off_canvas_menu', 'mc_off_canvas_menu', 10);
add_action('mcFoundation_footer', 'mc_primary_footer', 10);
add_action('widgets_init', 'mcFoundation_default_sidebars'); //Setup default, dynamic sidebars
add_action('mcFoundation_before_content', array('ManifestFramework', 'get_archive_titles'));//Show different titles based on archive type
add_filter('get_search_form', array('ManifestFramework', 'get_search_form')); //Improve the search form

/**
* PRIMARY HEADER
*/
function mc_primary_header() {
	get_template_part('templates/header', 'primary');
}

/**
* Primary Nav Menu
* Calls the primary menu. Basically allows for it to be hooked into.
*/
function mc_primary_nav() {
	get_template_part('templates/nav', 'primary');
}

/**
* Off Canvas Menu
*
*/
function mc_off_canvas_menu() {
	get_template_part('templates/nav', 'mobile');
}

/**
* PRIMARY FOOTER
*/
function mc_primary_footer() {
	get_template_part('templates/footer', 'primary');
}

/** 
	* PRIMARY SIDEBAR
**/
function mc_maybe_show_sidebar() {
	$show = false;
	if( is_home() || is_singular('post') || is_page_template('template-page-sidebar.php') ) {
		$show = true;
	}
	return apply_filters('mcFoundation_maybe_show_sidebar', $show);
}

add_action('mcFoundation_before_content_container', 'mc_primary_sidebar_open');
function mc_primary_sidebar_open() {
	if( mc_maybe_show_sidebar() ) {
		echo '<div class="row">';
			echo '<div class="small-12 medium-8 columns">';
	}
}
add_action('mcFoundation_after_content_container', 'mc_primary_sidebar');
function mc_primary_sidebar() {
	global $post;
	if( mc_maybe_show_sidebar() ) {
			echo '</div>';
			echo '<aside id="sidebar-primary" class="sidebar small-12 medium-3 medium-offset-1 columns">';
			get_sidebar();
			echo '</aside>';
		echo '</div>';
	}
}//mc_primary_sidebar();

/**
* Filter Social Classes
*
* @param array $options
* @param string $listID
* @return array
*/
//add_filter('mcFoundation_social_list_options', 'ra_social_list_options', 10, 2);
function ra_social_list_options($options, $listID) {
	if( !isset($options['location']) || 'header' != $options['location']) return $options;
	$options['stack'] = false;
	$options['listClass']['colors'] = '';
	
	$square = array('facebook', 'twitter', 'youtube', 'pinterest');
	foreach( $options['networkIcons'] as $name => $class ) {
		if( in_array($name, $square) ) {
			$options['networkIcons'][$name][0] = 'fa-'.$name.'-square';
		}
	}
	return $options;
}

/**
* FILTER SOCIAL LIST ITEMS
*
* @param array $items Social list items
* @param string $listID
* @param array $options
* @return array
*/
//add_filter('mcFoundation_social_list_items', 'mcFoundation_social_list_items', 10, 3);
function mcFoundation_social_list_items($items, $listID, $options) {
	if( !isset($options['location']) || 'header' != $options['location']) return $items;
	
	$items[] = '<li class="contact"><a href="'.get_permalink(get_field('config_contact', 'option')).'"><i class="fa fa-envelope fa-inverse"></i></a></li>';
	return $items;
}

/**
* ARCHIVE TITLES
*
* @return string
*/
add_action('mcFoundation_before_content_container', 'mcFoundation_archive_titles');
function mcFoundation_archive_titles() {
	
	if( !is_archive() ) return;
	
	$title = get_the_archive_title();
	
	if( is_tax() ) {
		$title = single_term_title('', false);
	}
	
	if( is_post_type_archive() ) {
		$title = get_queried_object()->label;
	}
	
	echo '<header class="row entryTitle archiveTitle">';
		echo '<h1>'.$title.'</h1>';
	echo '</header>';	
}

/***********************************************************************
* !COMMENTS
/***********************************************************************/
add_filter('mcFoundation_comment_avatar_args', 'mcFoundation_comment_avatar_args');
function mcFoundation_comment_avatar_args($args) {
	$class = 'th';
	if( isset($args['class']) ) {
		$args['class'] = ' '.$class;
	}
	else {
		$args['class'] = $class;
	}
	return $args;
}

/***********************************************************************
* !PAGE BUILDER
* Client specific customizations to the PageBuilder
/***********************************************************************/
if( class_exists('PageBuilder') ):
	
	/**
	* SET THUMBNAIL GALLERY THUMB SIZE
	*
	* @param array $sizes Array containing 'images' and 'thumbnails' sizes
	* @param string $name The module/column name
	* @param array $column The current module/column settings
	* @param obj $pb The PageBuilder object
	* @return array
	*/
	//add_filter('mcFoundation_builder_thumb_size', 'mcFoundation_builder_set_thumb_size', 10, 4);
	function mcFoundation_builder_set_thumb_size($sizes, $name, $column, $pb) {
		$sizes['thumbnails'] = 'mc_crop_200';
		return $sizes;
	}
	
	/**
	* HERO IMAGE CONTENT COLUMNS
	*
	* @param array $classes Array of class names
	* @param $array $column The pageBuilder column
	* @param obj $pb The pageBuilder class instance
	* @return array
	*/
	//add_filter('mcFoundation_builder_hero_content_class', 'mcFoundation_builder_hero_content_class', 10, 3);
	function mcFoundation_builder_hero_content_class($classes, $column, $pb) {
		if( is_front_page() ) return $classes;
		
		$classes['default'] .= ' small-12 columns';
		$classes['medium'] = 'medium-9';
		return $classes;
	}
	
endif;//class_exists('PageBuilder')
