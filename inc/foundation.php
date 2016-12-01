<?php
/**
* Functions related to customizing WordPress to work with Zurb Foundation 5
*
* @author Philip Downer <philip@manifestbozeman.com>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @version v1.0
*/	
	
/**
* wp_list_pages Active Class
* Add .active class to wp_list_pages output.
*
* @param string $input The class for the page item
* @return string The new classes
*/
add_filter( 'wp_list_pages', 'mcFoundation_active_list_pages_class', 10, 2 );
function mcFoundation_active_list_pages_class( $input ) {

	$pattern = '/current_page_item/';
  $replace = 'current_page_item active';

  $output = preg_replace( $pattern, $replace, $input );

  return $output;
}

/**
* Oembed flex video
*
* @param string $html The generated HTML for the embed
* @param string $url The oEmbed URL
* @param array $attr
* @param int $postID
* @return string
*/
add_filter('embed_oembed_html', 'mcFoundation_flex_video', 10, 4);
function mcFoundation_flex_video($html, $url, $attr, $postID) {
	$noFlex = apply_filters('mcFoundation_oembed_noflex_providers', array('kickstarter','issuu','kickstarter','meetup','polldaddy','scribd','tumblr','twitter'), $html, $url, $attr, $postID);

	$flex = true;
	foreach($noFlex as $provider) {
		if( strpos($url, $provider) !== false ) {
			$flex = apply_filters('mcFoundation_oembed_noflex', false, $provider, $url, $postID);
			break;
		}
	}
	
	return $flex ? '<div class="embedWrap flex-video">'.$html.'</div>' : '<div class="embedWrap noFlex">'.$html.'</div>';
}

/**
* POST STICKY CLASS
* Removes the conflict between WP's .sticky class and Zurb Foundation's .sticky class
*
* @param array $classes
* @param string $class
* @param int $postID
* @return array
*/
add_filter('post_class', 'mcFoundation_post_class', 10, 3);
function mcFoundation_post_class($classes, $class, $postID) {
	if( !in_array('sticky', $classes) ) return $classes;
	$stickyKey = array_search('sticky', $classes);
	$classes[$stickyKey] = 'post-sticky';
	return $classes;
}

/**
* THEME COMMENTS
* Use a custom comments template to allow display of Foundation friendly layout
*/
add_filter('comments_template', array('ManifestComments', 'comments_template'));
function mcFoundation_shortcode_atts_gallery($output, $pairs, $atts) {
	$output['itemtag'] = 'li';
	$output['icontag'] = 'figure';
	$output['captiontag'] = 'figcaption';
	return $output;
}

/**
* POST PAGINATION
*
* @return void
*/
add_action('mcFoundation_after_content', 'mcFoundation_posts_pagination');
function mcFoundation_posts_pagination() {
	if( !(is_home() || is_archive() || is_search() ) ) return;
	ManifestFramework::pagination();	
}

/**
* POST GALLERY
* Filter the [gallery] shortcode to format with block-grid. Documented in wp-includes/media.php @see gallery_shortcode()
*
* @param array $attr Attributes of the gallery shortcode
* @param int $instance Unique numeric ID of this gallery shorcode instance
* @return string
*/
add_filter('post_gallery', 'mcFoundation_post_gallery', 10, 3);
function mcFoundation_post_gallery($output, $attr, $instance) {
	
	//Let the default function handle feeds
	if( is_feed() ) return '';
	
	$post = get_post();
	$html5 = true;
	$atts = shortcode_atts( array(
		'order'      => 'ASC',
		'orderby'    => 'menu_order ID',
		'id'         => $post ? $post->ID : 0,
		'itemtag'    => 'li',
		'icontag'    => 'figure',
		'captiontag' => 'figcaption',
		'columns'    => 3,
		'size'       => 'thumbnail',
		'include'    => '',
		'exclude'    => '',
		'link'       => ''
	), $attr, 'gallery' );
	
	$id = intval( $atts['id'] );

	//Get the attachments
	if ( ! empty( $atts['include'] ) ) {
		$_attachments = get_posts( array( 'include' => $atts['include'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );

		$attachments = array();
		foreach ( $_attachments as $key => $val ) {
			$attachments[$val->ID] = $_attachments[$key];
		}
	} elseif ( ! empty( $atts['exclude'] ) ) {
		$attachments = get_children( array( 'post_parent' => $id, 'exclude' => $atts['exclude'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );
	} else {
		$attachments = get_children( array( 'post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );
	}
	
	if ( empty( $attachments ) ) {
		return '';
	}

	$itemtag = tag_escape( $atts['itemtag'] );
	$captiontag = tag_escape( $atts['captiontag'] );
	$icontag = tag_escape( $atts['icontag'] );
	$valid_tags = wp_kses_allowed_html( 'post' );
	if ( ! isset( $valid_tags[ $itemtag ] ) ) {
		$itemtag = 'li';
	}
	if ( ! isset( $valid_tags[ $captiontag ] ) ) {
		$captiontag = 'figure';
	}
	if ( ! isset( $valid_tags[ $icontag ] ) ) {
		$icontag = 'figcaption';
	}

	$columns = intval( $atts['columns'] );
	$itemwidth = $columns > 0 ? floor(100/$columns) : 100;
	$float = is_rtl() ? 'right' : 'left';

	$selector = "gallery-{$instance}";
	
	$galleryClasses = apply_filters('mcFoundation_post_gallery_class', array(
		'gallery',
		'galleryid-'.$id,
		'gallery-columns-'.$columns,
		'gallery-size-'.sanitize_html_class($atts['size']),
		'large' => 'large-block-grid-'.$columns,
		'medium' => $columns > 1 ? 'medium-block-grid-'.$columns : '',
		'small' => $columns > 3 ? 'small-block-grid-3' : ''
	), $attr, $instance);
	

	$gallery_ul = "<ul id='$selector' class='".implode(' ', $galleryClasses)."'>";
	$output = apply_filters( 'gallery_style', $gallery_ul );

	$i = 0;
	foreach ( $attachments as $id => $attachment ) {

		$attr = (trim( $attachment->post_excerpt )) ? array( 'aria-describedby' => "$selector-$id") : array();
		$attr['class'] = 'th';
		$attr = apply_filters('mcFoundation_post_gallery_img_attr', $attr, $id);
		
		$imgLink = false;
		if( empty($atts['link']) || $atts['link'] != 'none' ) {
			$imgLink = wp_get_attachment_url($id);//Only link to image file, not attachment for SEO purposes
		}
		
		$image_output = $imgLink ? apply_filters('mcFoundation_post_gallery_img_link', '<a href="'.$imgLink.'">', $id) : '';
		$image_output .= wp_get_attachment_image($id, $atts['size'], false, $attr);
		$image_output .= $imgLink ? '</a>' : '';
		$image_meta  = wp_get_attachment_metadata( $id );

		$orientation = '';
		if ( isset( $image_meta['height'], $image_meta['width'] ) ) {
			$orientation = ( $image_meta['height'] > $image_meta['width'] ) ? 'portrait' : 'landscape';
		}
		$output .= "<{$itemtag} class='gallery-item'>";
		$output .= "
			<{$icontag} class='gallery-icon {$orientation}'>
				$image_output";
				
		//SHOW THE CAPTION
		if ( $captiontag && trim($attachment->post_excerpt) ) {
			$output .= "
				<{$captiontag} class='wp-caption-text gallery-caption' id='$selector-$id'>
				" . wptexturize($attachment->post_excerpt) . "
				</{$captiontag}>";
		}
				
		$output .= "</{$icontag}>";
		$output .= "</{$itemtag}>";
	}

	$output .= "
		</ul>\n";

	return $output;
}//mcFoundation_post_gallery()