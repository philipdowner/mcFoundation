<?php
/***********************************************************************
* !ENVIRONMENT
/***********************************************************************/
/**
* GET ENVIRONMENT
* Returns a string (eg 'dev', or 'live') depending on the current development environment
*
* @return string
*/
function manifest_get_environment()
{
	return ENVIRONMENT; //set in wp-config.php
}

/***********************************************************************
* !THEME OPTIONS
/***********************************************************************/
/**
* GET THEME OPTION
* Retrieves the appropriate field from ACF options
*
* @param string $name The key name for the field
* @return mixed
*/
function get_theme_option($name) {
	return get_field($name, 'option');
}

/***********************************************************************
* !GLOBAL FILTERS
/***********************************************************************/
add_filter('body_class', 'manifest_body_class');
function manifest_body_class( $classes ) {
	global $wp_query;
	//do_dump($wp_query);
	
	if( is_page() ) {
		$classes[] = 'page-'.$wp_query->post->post_name;
	}
	
	
	return $classes;
}

/**
 * Allows for excerpt generation outside the loop.
 * 
 * @param string $text  The text to be trimmed
 * @return string       The trimmed text
 */
add_filter('wp_trim_excerpt', 'manifest_trim_excerpt');
function manifest_trim_excerpt( $text = '' ) {
    $text = strip_shortcodes( $text );
    $text = apply_filters('the_content', $text);
    $text = str_replace(']]>', ']]&gt;', $text);
    $excerpt_length = apply_filters('excerpt_length', 55);
    $excerpt_more = apply_filters('excerpt_more', ' ' . '[...]');
    return wp_trim_words( $text, $excerpt_length, $excerpt_more );
}
/***********************************************************************
* !Text and String functions
* Functions relating to headlines, excerpts, content, etc.
/***********************************************************************/
/**
* Manifest Alternate Title
*
* This function filters the title of pages and outputs whatever title
* the user has entered on the front-end. This is useful for naming
* a page one thing in the CMS, yet displaying another.
* 
* @author Philip Downer <philip@manifestbozeman.com>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @version v1.0
*
* @param int $postID The ID of the post to retrieve
* @return string $title The alternate title of the post/page.
* @link http://plugins.elliotcondon.com/advanced-custom-fields/ Advanced Custom Fields plugin
*/
function manifest_alternate_title($postID=null) {
	global $post;
	
	if( !$postID ) {
		$postID = $post->ID;
	}
	
	if( function_exists('get_field') && $alternate = get_field('alternate_title', $postID) ) {
		return apply_filters('manifest_alternate_title', $alternate);
	}
	
	return get_the_title($postID);
}

/**
* MAYBE POSSESSIVE
* Checks if the string ends in "S" and adds an apostrophe in the correct location
*
* @param string $string The string to format
*/
function manifest_maybe_possessive($string) {
	if( strripos($string, 's') !== strlen($string) - 1 ) {
		$possessive = $string.'\'s';
	} else {
		$possessive = $string.'\'';
	}
	return $possessive;
}

/**
* GET THE TITLE ATTRIBUTE
* If passed a post ID or post object, will fetch the escaped title for use in an HTML tag attribute. This function eliminates the need to pass array('echo' => false) to @see the_title_attribute, while also allowing access outside the WP loop. This function is not namespaced because IMHO it should belong in WP core.
*
* @param int|obj $post A post object or post ID. If none is given, uses the global $post obj
* @return string
*/
if( !function_exists('get_the_title_attribute' ) ) {
	function get_the_title_attribute($post = null) {
		
		//No post obj exists or can be fetched.
		if( !$post ) {
			global $post;
		}
		
		//Fetch the post object
		elseif( !is_object($post) ) {
		 $post = get_post((int)$post);
		}
		
		return esc_attr($post->post_title);
	}
}

/***********************************************************************
* !EMBEDS
/***********************************************************************/
/**
* Remove YouTube Related Videos
*
* @param mixed $data
* @param string $url
* @param array $args
* @return string
*/
add_filter('embed_oembed_html', 'manifest_youtube_remove_related_videos', 10, 3);
function manifest_youtube_remove_related_videos($data, $url, $args = array()) {
	return preg_replace('/(youtube\.com.*)(\?feature=oembed)(.*)/', '$1?' . 'wmode=transparent&amp;modestbranding=1&amp;' . 'rel=0$3', $data);
}