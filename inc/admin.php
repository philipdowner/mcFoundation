<?php
/**
* Admin functions 
*
* @author Philip Downer <philip@manifestbozeman.com>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @version v1.0
*/

/**
* ADD EDITOR STYLE
*/
add_action('admin_init', 'mc_add_editor_style');
function mc_add_editor_style() {
	add_editor_style('css/editor-style.min.css');
}

/**
* ACF - ADD OPTIONS PAGES
*/
if( function_exists('acf_add_options_page') ) {
	
	//Client theme options
	acf_add_options_page(array(
		'page_title' => 'Theme Options',
		'menu_title' => 'Theme Options',
		'menu_slug' => 'theme-options',
		'capability' => 'delete_others_posts',
		'redirect' => false
	));
	
//	acf_add_options_sub_page(array(
//		'page_title' => 'Company Information',
//		'menu_title' => 'Company',
//		'parent_slug' => 'theme-options'
//	));
	
	//Admin theme options
	acf_add_options_page(array(
		'page_title' => 'Site Configuration',
		'menu_title' => 'Site Config',
		'menu_slug' => 'site-config',
		'capability' => 'manage_options',
		'redirect' => false
	));	
}

/**
* TinyMCE Before INIT
* Filter the styles available in the tinyMCE editor
*
* @param array $args
* @return array
*/
add_filter('tiny_mce_before_init', 'mcFoundation_tiny_mce_before_init', 10, 2);
function mcFoundation_tiny_mce_before_init($args, $editor_id) {
	$args['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6;Preformatted=pre';
	return $args;
}

/**
* ADMIN PARSE QUERY
* Allows filtering by meta_key and meta_value
*
* @param obj $query
*/
add_filter('parse_query', 'mcFoundation_admin_parse_query');
function mcFoundation_admin_parse_query($query) {
	global $pagenow;
	if( (!is_admin() && $pagenow != 'edit.php') || !isset($_GET['meta_key']) || !isset($_GET['meta_value']) ) return;
	
	$query->set('meta_key', $_GET['meta_key']);
	$query->set('meta_value', $_GET['meta_value']);
}