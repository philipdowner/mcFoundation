<?php
/**
* Project Type taxonomy
* Custom taxonomy to categorize projects
*
* @author Philip Downer <philip@manifestbozeman.com>
* @version v1.0
*/
add_action('init', 'mc_tax_project_type');
function mc_tax_project_type() {
	
	//Set the post type(s)
	$object_type = array('mc_cpt_projects');
	
	//Set the labels
	$singular = 'Project Type';
	$plural = 'Project Types';
	$plural_short = $plural;
	
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
		'show_tagcloud' => false,
		'hierarchical' => true,
		'update_count_callback' => null,
		'query_var' => false, /*Optionally set as string*/
		'rewrite' => array('slug' => 'projects/project-type', 'with_front' => true, 'hierarchical' => true, 'ep_mask' => EP_PERMALINK),
		'capabilities' => $capabilities,
	);
	register_taxonomy('mc_tax_project_type', $object_type, $args);
}

add_filter('mcNav_element_classes', 'mc_tax_project_type_menu_class', 10, 3);
function mc_tax_project_type_menu_class($classes, $element, $args) {
	global $wp_query;
	
	if( $element->title == 'Projects' ) {
		 //do_dump($element);
		 if(
		 (array_key_exists('taxonomy', $wp_query->query_vars) && $wp_query->query_vars['taxonomy'] === 'mc_tax_project_type') ||
		 (array_key_exists('post_type', $wp_query->query_vars) && $wp_query->query_vars['post_type'] === 'mc_cpt_projects')
		 ) {
	    $classes[] = 'active';
	  }
  }
  
  if( $element->title == 'Blog' ) {
//	  do_action( 'add_debug_info', $wp_query, 'Blog query vars' );
	  if( $wp_query->queried_object->post_type === 'post' ) {
		  $classes[] = 'active';
	  }
  }
  return $classes;
}

/**
* Projects post type
* Used for portfolio type items
*
* @author Philip Downer <philip@manifestbozeman.com>
* @version v1.0
*/
add_action( 'init', 'mc_cpt_projects'); //Set the priority to 9 or lower to force lower submenu location.
function mc_cpt_projects() {

	$singular = 'Project';
	$plural = 'Projects';
	$singular_short = $singular;
	$plural_short = $plural;

	$labels = array(
		'name' => __($plural, 'post type general name'),
		'singular_name' => __($singular, 'post type singular name'),
		'add_new' => __('Add '.$singular_short, 'custom post type item'),
		'all_items' => __('All '.$plural_short, 'all custom post type items'),
		'add_new_item' => __('Add New '.$singular),
		'edit' => __( 'Edit' ),
		'edit_item' => __('Edit '.$singular_short),
		'new_item' => __('New '.$singular_short),
		'view_item' => __('View '.$singular_short),
		'search_items' => __('Search '.$plural),
		'not_found' =>  __('Nothing found in the Database.'),
		'not_found_in_trash' => __('Nothing found in Trash'),
		'parent_item_colon' => __('Parent '.$singular_short.':'),
	);
	
	$supports = array('title', 'editor', 'thumbnail', 'excerpt');
	
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
		'description' => __( 'Projects custom post type' ),
		'public' => true,
		'publicly_queryable' => true,
		'exclude_from_search' => false,
		'show_ui' => true,
		'show_in_menu' => true, //Alternative: a string like 'tools.php' or 'edit.php?post_type=page'
		'show_in_nav_menus' => true,
		'show_in_admin_bar' => true, //Whether to make this post type available in the WordPress admin bar.
		'menu_position' => 30, //Below 5=Posts, 10=Media, 15=Links, 20=Pages, 25=Comments, 60=First Separator, 65=Plugins, 70=Users, 75=Tools, 80=Settings, 100=Second Separator
		'menu_icon' => null,
//		'capabilities' => $capabilities,
//		'capability_type' => 'post', //Alternative: array('story','stories')
//		'map_meta_cap' => false, //Required if specifying capability types
		'hierarchical' => false,
		'supports' => $supports,
		'register_meta_box_cb' => null,
		'taxonomies' => array(), //Empty array if no built_in taxonomies desired.
		'has_archive' => false,
		'rewrite' => array('slug' => 'projects', 'with_front' => true), //Also array('slug' => 'string', 'with_front' => bool, 'feeds' => bool, 'pages' => bool, 'ep_mask' => const);
		'query_var' => 'project', //Alternative: 'string'
		'can_export' => true,
	 	);
	register_post_type( 'mc_cpt_projects', $args );
}

/**
* Filter the 'Enter Title Here' text for the post type
*
* @param string $title The enter title here text
*/
add_filter('enter_title_here', 'mc_cpt_projects_enter_title_here');
function mc_cpt_projects_enter_title_here($title) {
	global $post_type;
	if( $post_type == 'mc_cpt_projects') {
		$title = 'Project Title';
	}
	return $title;
}

/**
* Filter the Featured Image Metabox
*/
add_action('do_meta_boxes', 'mc_cpt_projects_featured_image_box');
function mc_cpt_projects_featured_image_box()
{
    remove_meta_box( 'postimagediv', 'mc_cpt_projects', 'side' );
    add_meta_box('postimagediv', __('Project Thumbnail'), 'post_thumbnail_meta_box', 'mc_cpt_projects', 'side', 'default');
}

/**
* Filter the Featured Image link
*
* @param string $content The original link for featured image
* @return string The modified string
*/
add_filter('admin_post_thumbnail_html', 'mc_cpt_projects_featured_image_link');
function mc_cpt_projects_featured_image_link( $content ) {
	global $post_type;
  if ($post_type == 'mc_cpt_projects') {
    $content = str_replace(__('featured image'), __('project thumbnail'), $content);
  }
  return $content;
}
