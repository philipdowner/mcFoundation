<?php
/**
* Shortcode functions
*
* @author Philip Downer <philip@manifestbozeman.com>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @version v1.0
*/

/**
* SHOW CHILDREN
* Lists children of the current page
*
* @param array $attr Shortcode attributes
* @param string $content Shortcode content (if any)
* @param string $name The name of the shortcode
* @return string
*/
add_shortcode('showchildren', 'mcFoundation_show_children_of_post');
function mcFoundation_show_children_of_post($attr = array(), $content = '', $name) {
	
	ob_start();
	global $post;
	$children = new WP_Query(array(
		'post_type' => $post->post_type,
		'post_parent' => $post->ID
	));
	
	//No posts, return an empty string
	if( !$children->have_posts() ) return '';
	
	//Output the posts
	echo '<ul class="postChildren">';
	while( $children->have_posts() ) {
		$children->the_post();
		echo '<li><a href="'.get_permalink($post->ID).'">'.get_the_title($post->ID).'</a></li>';
	}
	echo '</ul>';
		
	//Return the string of posts HTML
	return ob_get_clean();
}

/**
* SOCIAL LIST
* Lists social icons
*
* @param array $attr Array of shortcode attributes
* @param string $content Shortcode content. Will be ignored
* @param string $name
* @return string
*/
add_shortcode('social_list', 'mcFoundation_social_list_shortcode');
function mcFoundation_social_list_shortcode($attr = array(), $content = '', $name) {
	
	$atts = shortcode_atts(array(
		'colors' => true,
		'show_names' => false
	), $attr);
	
	return ManifestFramework::list_social_icons('mcSocialList', array('colors' => $atts['colors'] ? 'showColors' : 'noColors', 'viaShortcode'), !$atts['show_names']);
	
}//mcFoundation_social_list_shortcode