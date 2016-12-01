<?php
/**
* Downloads custom post type
* Used to create eBook Downloads
*
* @author Philip Downer <philip@manifestbozeman.com>
* @version v1.0
*/
add_action( 'init', 'mc_cpt_downloads'); //Set the priority to 9 or lower to force lower submenu location.
function mc_cpt_downloads() {

	$singular = 'Download';
	$plural = 'Downloads';
	$singular_short = 'Download';
	$plural_short = 'Downloads';

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
	
	$supports = array('title', 'thumbnail');
	
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
		'description' => __( 'eBook Downloads custom post type' ),
		'public' => false,
		'publicly_queryable' => true,
		'exclude_from_search' => true,
		'show_ui' => true,
		'show_in_menu' => true, //Alternative: a string like 'tools.php' or 'edit.php?post_type=page'
		'show_in_nav_menus' => true,
		'show_in_admin_bar' => false, //Whether to make this post type available in the WordPress admin bar.
		'menu_position' => 30, //Below 5=Posts, 10=Media, 15=Links, 20=Pages, 25=Comments, 60=First Separator, 65=Plugins, 70=Users, 75=Tools, 80=Settings, 100=Second Separator
		'menu_icon' => null,
//		'capabilities' => $capabilities,
//		'capability_type' => 'post', //Alternative: array('story','stories')
//		'map_meta_cap' => false, //Required if specifying capability types
		'hierarchical' => false,
		'supports' => $supports,
		'register_meta_box_cb' => 'mc_cpt_downloads_meta_boxes',
		'taxonomies' => array(), //Empty array if no built_in taxonomies desired.
		'has_archive' => false,
		'rewrite' => array(
			'slug' => 'downloads',
			'with_front' => true,
			'feeds' => false,
			'pages' => false
		), //Also array('slug' => 'string', 'with_front' => bool, 'feeds' => bool, 'pages' => bool, 'ep_mask' => const);
//		'rewrite' => true,
		'query_var' => false, //Alternative: 'string'
		'can_export' => true,
	 	);
	register_post_type( 'mc_cpt_downloads', $args );
}

add_action('init', 'mc_cpt_downloads_rewrites');
function mc_cpt_downloads_rewrites() {
	$post_type = 'mc_cpt_downloads';
	
	add_rewrite_tag('%accesskey%', '([^&]+)');
	add_rewrite_tag('%email%', '([^&]+)');
	add_rewrite_tag('%action%', '([^&]+)');
	add_rewrite_tag('%referrer%', '(^&0-9]+)');
	add_rewrite_tag('%force%', '([^&]+)');
	
	add_rewrite_rule('^downloads/([0-9]+)/get/([^/]*)/?', 'index.php?post_type='.$post_type.'&p=$matches[1]&accesskey=$matches[2]&action=get', 'top');
	add_rewrite_rule('^downloads/([0-9]+)/(join)/?', 'index.php?post_type='.$post_type.'&p=$matches[1]&action=join', 'top');
	add_rewrite_rule('^downloads/([0-9]+)/?', 'index.php?post_type='.$post_type.'&p=$matches[1]', 'top');
}

/**
* Filter the 'Enter Title Here' text for the post type
*
* @param string $title The enter title here text
*/
add_filter('enter_title_here', 'mc_cpt_downloads_enter_title_here');
function mc_cpt_downloads_enter_title_here($title) {
	global $post_type;
	if( $post_type == 'mc_cpt_downloads') {
		$title = 'eBook Title';
	}
	return $title;
}

/**
* Filter the Featured Image Metabox
*/
add_action('do_meta_boxes', 'mc_cpt_downloads_featured_image_box');
function mc_cpt_downloads_featured_image_box() {
  remove_meta_box( 'postimagediv', 'mc_cpt_downloads', 'side' );
  add_meta_box('postimagediv', __('Cover Image'), 'post_thumbnail_meta_box', 'mc_cpt_downloads', 'side', 'default');
}

/**
* Filter the Featured Image link
*
* @param string $content The original link for featured image
* @return string The modified string
*/
add_filter('admin_post_thumbnail_html', 'mc_cpt_downloads_featured_image_link');
function mc_cpt_downloads_featured_image_link( $content ) {
	global $post_type;
  if ($post_type == 'mc_cpt_downloads') {
    $content = str_replace(__('featured image'), __('cover image'), $content);
  }
  return $content;
}

/**
* Add Meta Boxes for Downloads CPT
*
* @param obj $post WP_Post object
* @return void
*/
function mc_cpt_downloads_meta_boxes($post) {
	add_meta_box('mc_cpt_downloads_config', 'eBook Download Stats', 'mc_cpt_downloads_stats_metabox', 'mc_cpt_downloads', 'normal', 'default');
}

/**
* STATS & OPTIONS META BOX
*
* @param obj $post WP_Post object
* @param array $metabox
* @return void
*/
function mc_cpt_downloads_stats_metabox($post, $metabox) {
	$downloads = new Downloads($post->ID);
	$totals = array(
		'downloaders' => 0,
		'subscribers' => 0,
		'downloads' => 0
	);
	$meta = get_post_meta($post->ID, $downloads->getUserPostMetaKey(), true);
	
	echo '<div class="acf-field">';
		echo '<div class="acf-label">';
			echo '<label for="download_url">Direct Download URL</label>';
			echo '<p class="description">You can use this link to allow someone to download your file without needing to first provide their contact details.</p>';
		echo '</div>';
		echo '<div class="acf-input">';
			echo '<input type="text" readonly value="'.get_bloginfo('url').'/downloads/'.$post->ID.'/get/'.md5($post->ID).'" />';
		echo '</div>';
	echo '</div>';
	
	echo '<table width="100%">';
		echo '<thead>';
			echo '<tr>';
				echo '<th align="left" class="email">E-mail</th>';
				echo '<th align="left" class="name">Name</th>';
				echo '<th align="left" class="accessKey">Access Key</th>';
				echo '<th align="center" class="date signupDate">Signup Date</th>';
				echo '<th align="center" class="date downloadDate">Latest Download</th>';
				echo '<th align="center" class="subscribed">Subscribed?</th>';
				echo '<th align="right" class="downloads"># Downloads</th>';
			echo '</tr>';
		echo '</thead>';
		
		echo '<tbody>';
			
			//No signups
			if( empty($meta) ) {
				echo '<tr>';
					echo '<td colspan="7"><p>No one has downloaded this item yet.</p></td>';
				echo '</tr>';
			}
			
			//Show signups
			else {
				$totals['downloaders'] = $totals['downloaders'] + count($meta);
				
				foreach( $meta as $accessKey => $user ) {
					
					if( $user['newsletter'] ) {
						$totals['subscribers'] = $totals['subscribers'] + 1;
					}
					
					//do_dump($user);
					$downloadCount = array_key_exists('download_count', $user) ? $user['download_count'] : 0;
					$totals['downloads'] = $totals['downloads'] + $downloadCount;
					
					echo '<tr>';
						echo '<td align="left" class="email"><a href="mailto:'.$user['email'].'">'.$user['email'].'</a></td>';
						echo '<td align="left" class="name">'.$user['last_name'].', '.$user['first_name'].'</td>';
						echo '<td align="left" class="accessKey">'.$accessKey.'</td>';
						echo '<td align="center" class="date signupDate">'.date('m/d/Y', $user['signup_time']).'</td>';
						echo '<td align="center" class="date downloadDate">'.(array_key_exists('last_download_time', $user) ? date('m/d/Y', $user['last_download_time']) : '--').'</td>';
						echo '<td align="center" class="subscribed"><span class="true">'.($user['newsletter'] ? 'Yes' : 'No').'</span></td>';
						echo '<td align="right" class="downloads"><strong>'.$downloadCount.'</strong></td>';
					echo '</tr>';
				}
			}
			
			//Anonymous downloads
			$meta = get_post_meta($post->ID, $downloads->getAnonymousPostMetaKey(), true);
			if( !empty($meta) ) {
				
				$downloadCount = array_key_exists('download_count', $meta) ? $meta['download_count'] : 0;
				$totals['downloads'] += $downloadCount;
				
				echo '<tr>';
					echo '<td align="left">Anonymous (Direct Download)</td>';
					echo '<td align="left">--</td>';
					echo '<td align="left">'.md5($post->ID).'</td>';
					echo '<td align="center">--</td>';
					echo '<td align="center">'.date('m/d/Y', $meta['last_download_time']).'</td>';
					echo '<td align="center">--</td>';
					echo '<td align="right"><strong>'.$downloadCount.'</strong></td>';
				echo '</tr>';
			}
		echo '</tbody>';
		
		echo '<tfoot>';
			echo '<tr><td align="right" colspan="6"><strong>Total Downloaders:</strong></td><td align="right">'.$totals['downloaders'].'</td></tr>';
				echo '<tr><td align="right" colspan="6"><strong>Total Newsletter Subscribers:</strong></td><td align="right">'.$totals['subscribers'].' ('.($totals['downloaders'] ? number_format(($totals['subscribers']/$totals['downloaders'])*100, 1) : 0).'%)</td></tr>';
				echo '<tr><td align="right" colspan="6"><strong>Total Downloads:</strong></td><td align="right">'.$totals['downloads'].'</td></tr>';
		echo '</tfoot>';
	echo '</table>';
}