<?php
/**
* Name of the post type
* Describe what the post type will be used for.
*
* @author Philip Downer <philip@manifestbozeman.com>
* @version v1.0
*/
add_action( 'init', 'mc_cpt_type'); //Set the priority to 9 or lower to force lower submenu location.
function mc_cpt_type() {

	$singular = 'Custom Type';
	$plural = 'Custom Types';
	$singular_short = 'Type';
	$plural_short = 'Types';

	$labels = array(
		'name' => __($plural, 'post type general name'),
		'singular_name' => __($singular, 'post type singular name'),
		'add_new' => __('Add '.$singular_short, 'custom post type item'),
		'all_items' => __('All '.$plural_short, 'all custom post type items'),
		'add_new_item' => __('Add New '.$plural),
		'edit' => __( 'Edit' ),
		'edit_item' => __('Edit '.$singular_short),
		'new_item' => __('New '.$singular_short),
		'view_item' => __('View '.$singular_short),
		'search_items' => __('Search '.$plural),
		'not_found' =>  __('Nothing found in the Database.'),
		'not_found_in_trash' => __('Nothing found in Trash'),
		'parent_item_colon' => __('Parent '.$singular_short.':'),
	);
	
	$supports = array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes', 'post-formats');
	
	$capabilities = array(
		'edit_post' => 'edit_type_post',
		'read_post' => 'read_type_post',
		'delete_post' => 'delete_type_post',
		'edit_posts' => 'edit_type_posts',
		'edit_others_posts' => 'edit_others_type_posts',
		'publish_posts' => 'publish_type_posts',
		'read_private_posts' => 'read_private_types'
	);
	
	$args = array(
		'labels' => $labels,
		'description' => __( 'This is the example custom post type' ),
		'public' => true,
		'publicly_queryable' => true,
		'exclude_from_search' => false,
		'show_ui' => true,
		'show_in_menu' => true, //Alternative: a string like 'tools.php' or 'edit.php?post_type=page'
		'show_in_nav_menus' => true,
		'show_in_admin_bar' => true, //Whether to make this post type available in the WordPress admin bar.
		'menu_position' => 26, //Below 5=Posts, 10=Media, 15=Links, 20=Pages, 25=Comments, 60=First Separator, 65=Plugins, 70=Users, 75=Tools, 80=Settings, 100=Second Separator
		'menu_icon' => null,
//		'capabilities' => $capabilities,
//		'capability_type' => 'post', //Alternative: array('story','stories')
//		'map_meta_cap' => false, //Required if specifying capability types
		'hierarchical' => false,
		'supports' => $supports,
		'register_meta_box_cb' => null,
		'taxonomies' => array('category', 'post_tag'), //Empty array if no built_in taxonomies desired.
		'has_archive' => false,
		'rewrite' => true, //Also array('slug' => 'string', 'with_front' => bool, 'feeds' => bool, 'pages' => bool, 'ep_mask' => const);
		'query_var' => true, //Alternative: 'string'
		'can_export' => true,
	 	);
	register_post_type( 'mc_post_type', $args );
}

/**
* Registers the example taxonomy
* The example taxonomy is used to categorize the example post type
*
* @author Philip Downer <philip@manifestbozeman.com>
* @version v1.0
*/
add_action('init', 'mc_tax_taxname');
function mc_tax_taxname() {
	
	//Set the post type(s)
	$object_type = array('mc_post_type');
	
	//Set the labels
	$singular = 'Custom Category';
	$plural = 'Custom Categories';
	$plural_short = 'Categories';
	
	$labels = array(
		'name' => __( $plural ), /* name of the custom taxonomy */
		'singular_name' => __( $singular ), /* single taxonomy name */
		'search_items' =>  __( 'Search '.$plural ), /* search title for taxomony */
		'popular_items' => __( 'Popular '.$plural ),
		'all_items' => __( 'All '.$plural ), /* all title for taxonomies */
		'parent_item' => __( 'Parent '.$singular ), /* parent title for taxonomy */
		'parent_item_colon' => __( 'Parent '.$singular.':' ), /* parent taxonomy title */
		'edit_item' => __( 'Edit '.$singular ), /* edit custom taxonomy title */
		'update_item' => __( 'Update '.$singular ), /* update title for taxonomy */
		'add_new_item' => __( 'Add New '.$singular ), /* add new title for taxonomy */
		'new_item_name' => __( 'New '.$singular.' Name' ), /* name title for taxonomy */
		'separate_items_with_commas' => __( 'Separate '.strtolower($plural_short).' with commas' ),
		'add_or_remove_items' => __( 'Add or remove '.strtolower($plural_short) ),
		'choose_from_most_used' => __( 'Choose from the most used '.strtolower($plural_short) ),
		'menu_name' => __( $plural )
	);
	
	//Capabilities
	$capabilities = array(
		'manage_terms' => 'manage_categories',
		'edit_terms' => 'manage_categories',
		'delete_terms' => 'manage_categories',
		'assign_terms' => 'edit_posts'
	);

	//Arguments
	$args = array(
		'labels' => $labels,
		'public' => true,
		'show_in_nav_menus' => true,
		'show_ui' => true,
		'show_admin_column' => true,
		'show_tagcloud' => true,
		'hierarchical' => false,
		'update_count_callback' => null,
		'query_var' => true, /*Optionally set as string*/
		'rewrite' => true,
		'capabilities' => $capabilities,
	);
	
	register_taxonomy('mc_tax_taxname', $object_type, $args);
}

/**
* Filter the 'Enter Title Here' text for the post type
*
* @param string $title The enter title here text
*/
add_filter('enter_title_here', 'manifest_cptname_enter_title_here');
function manifest_cptname_enter_title_here($title) {
	global $post_type;
	if( $post_type == 'mc_post_type') {
		$title = 'Default Title Text';
	}
	return $title;
}

/**
* Filter the Featured Image Metabox
*/
add_action('do_meta_boxes', 'manifest_cptname_featured_image_box');
function manifest_cptname_featured_image_box()
{
    remove_meta_box( 'postimagediv', 'mc_post_type', 'side' );
    add_meta_box('postimagediv', __('Thumbnail Default'), 'post_thumbnail_meta_box', 'mc_post_type', 'side', 'default');
}

/**
* Filter the Featured Image link
*
* @param string $content The original link for featured image
* @return string The modified string
*/
add_filter('admin_post_thumbnail_html', 'manifest_cptname_featured_image_link');
function manifest_cptname_featured_image_link( $content ) {
	global $post_type;
  if ($post_type == 'mc_post_type') {
    $content = str_replace(__('featured image'), __('default link text'), $content);
  }
  return $content;
}
