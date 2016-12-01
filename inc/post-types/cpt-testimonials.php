<?php
/**
* Testimonials
*
* @author Philip Downer <philip@manifestbozeman.com>
* @version v1.0
*/
add_action( 'init', 'mc_cpt_testimonials'); //Set the priority to 9 or lower to force lower submenu location.
function mc_cpt_testimonials() {

	$singular = 'Testimonial';
	$plural = 'Testimonials';
	$singular_short = $singular;
	$plural_short = $plural;

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
	
	$supports = array('title', 'editor', 'thumbnail', 'page-attributes');
	
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
		'description' => __( 'Testimonials custom post type' ),
		'public' => true,
		'publicly_queryable' => true,
		'exclude_from_search' => true,
		'show_ui' => true,
		'show_in_menu' => true, //Alternative: a string like 'tools.php' or 'edit.php?post_type=page'
		'show_in_nav_menus' => false,
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
		'has_archive' => true,
		'rewrite' => array('slug' => 'testimonials'), //Also array('slug' => 'string', 'with_front' => bool, 'feeds' => bool, 'pages' => bool, 'ep_mask' => const);
		'query_var' => false, //Alternative: 'string'
		'can_export' => true,
	 	);
	register_post_type( 'mc_cpt_testimonials', $args );
}

/**
* Filter the 'Enter Title Here' text for the post type
*
* @param string $title The enter title here text
*/
add_filter('enter_title_here', 'mc_cpt_testimonials_enter_title_here');
function mc_cpt_testimonials_enter_title_here($title) {
	global $post_type;
	if( $post_type == 'mc_cpt_testimonials') {
		$title = 'Testimonial Author Name';
	}
	return $title;
}

/**
* Filter the Featured Image Metabox
*/
add_action('do_meta_boxes', 'manifest_mc_cpt_testimonials_featured_image_box');
function manifest_mc_cpt_testimonials_featured_image_box()
{
    remove_meta_box( 'postimagediv', 'mc_cpt_testimonials', 'side' );
    add_meta_box('postimagediv', __('Company Logo'), 'post_thumbnail_meta_box', 'mc_cpt_testimonials', 'side', 'default');
}

/**
* Filter the Featured Image link
*
* @param string $content The original link for featured image
* @return string The modified string
*/
add_filter('admin_post_thumbnail_html', 'manifest_mc_cpt_testimonials_featured_image_link');
function manifest_mc_cpt_testimonials_featured_image_link( $content ) {
	global $post_type;
  if ($post_type == 'mc_cpt_testimonials') {
    $content = str_replace(__('featured image'), __('company logo'), $content);
  }
  return $content;
}
