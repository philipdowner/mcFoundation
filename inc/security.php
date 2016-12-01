<?php
/**
* Security.php
* Functions related to hardening WordPress security
*
* @author Philip Downer <philip@manifestbozeman.com>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @version v1.0
*/

/**
* Remove version string from header
*/
remove_action('wp_head', 'wp_generator');

/**
* HIDE LOGIN ERROR MESSAGES (Wrong Password, No Such User etc.)
*/
add_filter('login_errors', create_function('$a', "return null;"));

/**
* Remove admin name in comments class
*
* @link http://www.wprecipes.com/wordpress-hack-remove-admin-name-in-comments-class Documentation
*/
add_filter( 'comment_class' , 'mcFoundation_remove_comment_author_class' );
function mcFoundation_remove_comment_author_class( $classes ) {
	foreach( $classes as $key => $class ) {
		if(strstr($class, "comment-author-")) {
			unset( $classes[$key] );
		}
	}
	return $classes;
}