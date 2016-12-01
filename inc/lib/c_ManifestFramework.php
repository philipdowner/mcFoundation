<?php
class ManifestFramework {

	/***********************************************************************
	* !TAXONOMIES & TERMS
	/***********************************************************************/
	/**
	* Gets a list of terms from a specific taxonomy, each linked to their respective term pages.
	*
	* @param mixed $taxonomy Name of the taxonomy (string) or array of taxonomy names
	* @param array $args Array of arguments @see get_terms()
	* @param string $format 'list' for a string of <li> elements, or 'array' for associative array containing term names and links. If the taxonomy doesn't exist, returns a WP_Error object.
	* @return mixed Array or string according to $format
	* @todo Handle hierarchical terms
	*/
	public function getTaxTermsList($taxonomy, $args = array(), $format = 'array') {
		$terms = get_terms($taxonomy, $args);
		if( is_wp_error($terms) ) return $terms;

		$termsList = array();
		foreach( $terms as $term ) {
			$termsList[] = array(
				'name' => $term->name,
				'link' => get_term_link($term),
				'obj' => $term
			);
		}

		//Return the array
		if( $format == 'array' ) return $termsList;

		//Create a list
		$content = '';
		foreach( $termsList as $term ) {
			$content .= '<li class="term-id='.$term['obj']->term_id.'"><a href="'.$term['link'].'">'.$term['name'].'</a></li>';
		}
		return $content;

	}//getTaxTermsList

	/***********************************************************************
	* !PAGINATION
	/***********************************************************************/
	/**
	* PAGINATION
	*
	* @param obj $query Current $wp_query object
	* @param array $args Arguments to pass to paginate_links()
	* @return
	*/
	static function pagination($query = null, $args = array()) {
		if( !$query ) {
			global $wp_query;
			$query = $wp_query;
		}

		$wrapClass = apply_filters('mcFoundation_pagination_wrap_class', array('paginationWrap', 'row'), $query);
		$paginationClass = apply_filters('mcFoundation_pagination_class', array('pagination', 'small' => 'small-12', 'columns'), $query);

		//Handle single posts pagination
		if( is_single() ) {
			global $post, $page, $numpages, $multipage, $more;
			if( !$multipage ) return;

			$paginate_links = '<ul class="pagination">';
				$prevClasses = array('arrow');
				$prevClasses[] = $page == 1 ? 'unavailable' : '';

				$nextClasses = array('arrow');
				$nextClasses[] = $page == $numpages ? 'unavailable' : '';

				//Previous Link
				$paginate_links .= '<li class="'.implode(' ', $prevClasses).'">';
				$paginate_links .= _wp_link_page($page - 1);
				$paginate_links .= '&laquo; Previous</a></li>';

				for( $i = 1; $i <= $numpages; $i++ ) {
					$classes = array();
					$classes[] = $i == $page ? 'current' : '';
					$paginate_links .= '<li class="'.implode(' ', $classes).'">';
					$paginate_links .= _wp_link_page($i).$i.'</a>';
					$paginate_links .= '</li>';
				}

				//Next Link
				$paginate_links .= '<li class="'.implode(' ', $nextClasses).'">';
				$paginate_links .= $page == $numpages ? _wp_link_page($numpages) : _wp_link_page($page + 1);
				$paginate_links .= 'Next &raquo;</a></li>';

			$paginate_links .= '</ul>';
		}

		//Handle archives
		else {
			//No need to paginate
			if( !$query->max_num_pages ) return '';

			$big = 999999999; // This needs to be an unlikely integer
			$pagination_args = array(
				'base' => str_replace($big, '%#%', get_pagenum_link($big) ),
				'current' => max(1, $query->query_vars['paged'] ),
				'total' => $query->max_num_pages,
				'mid_size' => 6,
				'end_size' => 3,
				'add_args' => false,
				'prev_next' => true,
		    'prev_text' => 'Previous',
		    'next_text' => 'Next',
				'type' => 'list'
			);
			$args = wp_parse_args($args, $pagination_args);
			$paginate_links = paginate_links($args);
			$paginate_links = str_replace( "<ul class='page-numbers'>", "<ul class='pagination'>", $paginate_links );
			$paginate_links = str_replace( '<li><span class="page-numbers dots">', "<li><a href='#'>", $paginate_links );
			$paginate_links = str_replace( "<li><span class='page-numbers current'>", "<li class='current'><a href='#'>", $paginate_links );
			$paginate_links = str_replace( "</span>", "</a>", $paginate_links );
			$paginate_links = str_replace( "<li><a href='#'>&hellip;</a></li>", "<li  class='unavailable'><a href=''><span class='dots'>&hellip;</span></a></li>", $paginate_links );
			$paginate_links = preg_replace( "/\s*page-numbers/", "", $paginate_links );
		}

		//Show the wrapper
		echo '<div class="'.implode(' ', $wrapClass).'">';
			echo '<div class="'.implode(' ', $paginationClass).'">';

				echo $paginate_links;

			//Close the wrapper
			echo '</div>';
		echo '</div>';
	}//pagination()

	/***********************************************************************
	* !AJAX
	/***********************************************************************/
	public function isAjax() {
		if( empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest' ) {
			return false;
		}
		return true;
	}

	public function getAjaxAction() {
		if( !$this->isAjax() ) return false;
		return $_REQUEST['action'];
	}

	public function isAjaxAction($action) {
		if( !$this->isAjax() ) return false;
		return $this->getAjaxAction() == $action ? true : false;
	}

	/***********************************************************************
	* !STRINGS & TEXT MANIPULATION
	/***********************************************************************/
	/**
	* GET LOREM IPSUM
	*
	* @param int $p Number of paragraphs
	* @param array $params Parameters to pass to the loripsum.net API
	* @return string
	*/
	static function getLoremIpsum($p = 3, $params = array()) {
		$defaults = array('medium');
		$args = implode('/', wp_parse_args($params, $defaults));
		$response = wp_remote_get('http://loripsum.net/api/'.$p.'/'.$args);
		if( is_array($response) ) return $response['body'];
	}

	/**
	* Shorten text
	*
	* @author Philip Downer <philip@manifestbozeman.com>
	* @license http://opensource.org/licenses/gpl-license.php GNU Public License
	* @version v1.0
	*
	* @param mixed $text The text to shorten. Defaults to post content
	* @param int $length Number of WORDS to shorten text to.
	* @param string $more Characters to append to shortened text.
	* @return string Shortened text with horizontal ellipsis added
	*/
	static function trim_excerpt($text,$length=55,$more='&hellip;',$striptags = true) {

		if ( $striptags === true ) {
			$text = strip_tags($text);
			$text = strip_shortcodes($text);
		}

		return wp_trim_words($text, $length, $more);
	}

	/**
	* GET DURATION
	* Formats two dates to show a duration period, eg. '2010 - 2015'
	*
	* @param int $start Starting time. A strtotime formattable value.
	* @param int $end End time. A strtotime formattable value, otherwise uses the current time.
	* @param array $params Additional defaults for the function:
	*		'separator' => ' - ' string | Text to place between the values
	*		'format' => 'Y' - string | A date formatted value to return duration as
	*		'same_return' => null - mixed | If the values end up being the same (there is no duration), return something specific. When null defaults to returning the formatted $end value.
	*
	* @return string
	*/
	static function getDuration($start, $end = null, $params = array()) {

		$defaults = wp_parse_args($params, array(
			'separator' => ' - ',
			'format' => 'Y',
			'same_return' => null
		));

		//Format the start date
		$start = !is_int($start) ? date($defaults['format'], strtotime($start)) : date($defaults['format'], $start);

		//Default to current time, if $end is not set
		if( !$end ) {
			$end = date($defaults['format'], current_time('timestamp'));
		}

		//Format the end date
		else {
			$end = !is_int($end) ? date($defaults['format'], strtotime($end)) : date($defaults['format'], $end);
		}

		//End date is no greater than start date
		if( strtotime($end) <= strtotime($end) ) {
			return $defaults['same_return'] === null ? $end : $defaults['same_return'];
		}

		return $start.$defaults['separator'].$end;
	}//getDuration()

	/**
	* UnAutop
	* Reverses the effects of the wpautop() function for use in plain ol' textareas
	*
	* @param string $s The content to format
	* @return string The formatted string
	*/
	static function unautop($s) {
	    //remove any new lines already in there
	    $s = str_replace("\n", "", $s);

	    //remove all <p>
	    $s = str_replace("<p>", "", $s);

	    //replace <br /> with \n
	    $s = str_replace(array("<br />", "<br>", "<br/>"), "\n", $s);

	    //replace </p> with \n\n
	    $s = str_replace("</p>", "\n\n", $s);

	    return $s;
	}

	/***********************************************************************
	* !CALCULATIONS
	/***********************************************************************/
	/**
	* HUMAN FILESIZE
	* Given a number in bytes, calculates a human readable size
	*
	* @param int $bytes Filesize in bytes
	* @param int $decimals Number of decimal points to round to
	* @return float
	*/
	static function humanFilesize($bytes, $decimals = 2) {
	  $sz = 'BKMGTP';
	  $factor = floor((strlen($bytes) - 1) / 3);
	  return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
	}

	/**
	* Dimensions by ratio
	* Given a desired width calculates the height based on an aspect ratio
	*
	* @param int $newWidth The desired width for the resulting dimension.
	* @param int $aspectWidth The width of the aspect ratio
	* @param int $aspectHeight The height of the aspect ratio
	* @param int $round Whether to round the dimensions to the nearest whole int
	* @return An array containing the new width and height
	*/
	static function dimensionsByRatio($newWidth, $aspectWidth = 4, $aspectHeight = 3, $round = true) {

		if( !is_integer($newWidth) || $newWidth <= 0 ) return;

		$newHeight = ($aspectHeight / $aspectWidth) * $newWidth;

		if( $round ) {
			$newWidth = round($newWidth);
			$newHeight = round($newHeight);
		}

		$dimensions = array('width' => $newWidth, 'height' => $newHeight);
		return $dimensions;
	}

	/***********************************************************************
	* !IMAGES
	/***********************************************************************/
	/**
	* PLACEHOLDER IMAGE
	*
	* @param int $width
	* @param height $height
	* @param bool $cachebuster Appends a query string to ensure a new, unique image
	* @param array $params
	* 			~ 'category' => 'string'
	* 			~ 'filter' => 'string'
	* @return string
	*/
	static function placeholderImg($width=100, $height=100, $cachebuster=true, $params = array()) {

		/**
			* Additional:
			* https://gist.github.com/s-stude/9941332
		**/

		//return 'http://unsplash.it/'$width.'/'.$height;
		//return 'http://p-hold.com/'.$width.'/'.$height;
		//return 'https://placehold.it/'.$width.'x'.$height.';
		//return 'http://dummyimage.com/'.$width.'x'.$height.'/000/fff.gif';
		//return 'http://lorempixel.com/'.$width.'/'.$height;

		$params = wp_parse_args($params, array(
			'category' => 'all'
		));

		$url = "https://placeimg.com/$width/$height/{$params['category']}";

		if( $params['filter'] ) {
			$url .= "/{$params['filter']}";
		}

		if( $cachebuster ) {
			$url .= "?v=".microtime();
		}

		return $url;
	}

	/**
	* SVG
	* Uses file_get_contents() to output an inline SVG
	*
	* @param string $file The file name to get relative $path
	* @param string $path A resource path if different than the IMG_DIR
	* @return string
	*/
	static function Svg($file, $path = '') {
		$path = !$path ? IMG_PATH : $path;
		if( !file_exists($path.$file) ) return '';
		return file_get_contents($path.$file);
	}

	/**
	* GET RESPONSIVE IMAGE SIZES
	*
	* @param int $imgID Optional. Allows for filtering by imageID.
	* @param bool $crop Whether to use a fixed size crop or not
	* @return array
	*/
	static function getResponsiveImageSizes($imgID = null, $crop = false) {
		return apply_filters('mcFoundation_responsive_thumb_sizes', array(
			'mc_400' => $crop ? 'mc_crop_400' : 'mc_400',
			'mc_600' => $crop ? 'mc_crop_600' : 'mc_600',
			'small' => $crop ? 'mc_crop_800' : 'mc_800',
			'medium' => $crop ? 'mc_crop_1000' : 'mc_1000'
		), $imgID);
	}

	/**
	* RESPONSIVE SIZES
	* Generates an interchange.js array suitable for insert as a data attribute.
	*
	* @param int $imgID
	* @return string
	*/
	static function responsiveImgSrc($imgID, $thumbSizes = null, $crop = false) {

		$thumbSizes = is_array($thumbSizes) ? wp_parse_args($thumbSizes, ManifestFramework::getResponsiveImageSizes($imgID, $crop)) : ManifestFramework::getResponsiveImageSizes($imgID);

		//Set an array of image sizes.
		$imgs = array();
		foreach( $thumbSizes as $size => $thumb ) {
			$imgs[$size] = wp_get_attachment_image_src($imgID, $thumb);
		}
		$imgs = apply_filters('mcFoundation_responsive_image_src', $imgs, $imgID);

		//Create a JS array with Foundation Interchange info
		$interchange = array();
		foreach( $imgs as $size => $img) {
			$interchange['interchange'][$size] = '['.$img[0].', ('.$size.')]';
			$interchange['images'][$size] = $img;
		}

		return $interchange;
	}

	/**
	* RESPONSIVE INTERCHANGE DATA
	* Fetches an interchange.js data element for a specific image
	*
	* @param int $imgID
	* @param array Array of thumbnail sizes to fetch. Null if default sizes desired.
	* @return string
	*/
	static function responsiveInterchangeData($imgID, $thumbSizes = null, $crop = false) {
		$interchange = ManifestFramework::responsiveImgSrc($imgID, $thumbSizes, $crop);
		return 'data-interchange="'.implode(', ', $interchange['interchange']).'"';
	}

	/**
	* RESPONSIVE IMG
	* Generates a responsive image tag for interchange.js
	*
	* @param int $imgID
	* @param array $thumbSizes An array of thumbSizes to override standard sizes
	* @return string
	*/
	static function responsiveImg($imgID, $thumbSizes = null, $class = array()) {

		$interchange = ManifestFramework::responsiveImgSrc($imgID, $thumbSizes);
		$imgs = $interchange['images'];

		$img = '<img data-interchange="'.implode(', ', $interchange['interchange']).'" class="'.implode(' ', $class).'" />';
		$img .= '<noscript><img src="'.$imgs['default'][0].'" class="'.implode(' ', $class).'" /></noscript>';

		return $img;
	}

	/**
	* GET IMAGE SIZES
	* Fetches all of the image sizes, both standard and custom
	*
	* @param string $size An image thumbnail size
	* @return array Array containing 'width' => int, 'height' => int, 'crop' => bool
	*/
	static function getImageSizes($size = null) {
		global $_wp_additional_image_sizes;
    $sizes = array();
    $get_intermediate_image_sizes = get_intermediate_image_sizes();

    // Create the full array with sizes and crop info
    foreach( $get_intermediate_image_sizes as $_size ) {
      if ( in_array($_size, array('thumbnail', 'medium', 'large'))) {
        $sizes[ $_size ]['width'] = get_option( $_size . '_size_w' );
        $sizes[ $_size ]['height'] = get_option( $_size . '_size_h' );
        $sizes[ $_size ]['crop'] = (bool) get_option( $_size . '_crop' );
      } elseif ( isset($_wp_additional_image_sizes[$_size ]) ) {
        $sizes[$_size] = array(
          'width' => $_wp_additional_image_sizes[$_size]['width'],
          'height' => $_wp_additional_image_sizes[$_size]['height'],
          'crop' =>  $_wp_additional_image_sizes[$_size]['crop']
        );
      }
    }

    //Fetch the needed size
    if ( $size ) {
      return isset($sizes[$size]) ? $sizes[$size] : false;
    }
    return $sizes;
	}

	/**
	* HERO IMAGE
	*
	* @return string
	*/
	static function heroImg($attr = array()) {
			global $post;
			$thumbID = get_post_thumbnail_id($post->ID);

			//Mimic the pageBuilder hero image
			$defaults = array(
				'hero_type' => 'single',
				'hero_headline' => get_the_title(),
				'introduction' => wpautop($post->excerpt),
				'button_cta' => false,
				'background' => acf_get_attachment($thumbID),
				'background_images' => false,
				'overlay_location' => 'lowerleft',
				'header_adjust' => true,
				'full_screen' => false,
			);
			$column = wp_parse_args($attr, $defaults);

			$template = locate_template(array('templates/modules/pageBuilder-column_hero_image.php'));
			if($template) {
				echo '<div id="pageBuilder" class="propertyHeroContainer">';
					echo '<div class="rowWrap full_width has-column_hero_image">';
						echo '<div class="row">';
							echo '<div class="module module-column_hero_image small-12 columns">';
								require_once($template);
							echo '</div>';
						echo '</div>';
					echo '</div>';
				echo '</div>';
			}
	}//heroImg()

	/***********************************************************************
	* !SOCIAL
	/***********************************************************************/

	//Deprecated - Use self::listSociaIcons() instead.
	static function list_social_icons($listID = 'mcSocialList', $listClass = array(), $iconsOnly = true) {
		return self::listSocialIcons($listID, $listClass);
	}

	/**
	* List Social Icons
	*
	* @author Philip Downer <philip@manifestbozeman.com>
	* @version v1.2
	*
	* @param string $listID The CSS ID to apply
	* @param array $listClass CSS class(es) to apply
	* @param bool $options Additional options, usually from a shortcode
	* @return string
	*/
	static function listSocialIcons($listID = 'mcSocialList', $options = array()) {

		//GET THE USER DEFINED SOCIAL NETWORKS
		$networks = get_theme_option('option_social_urls');
		if( !$networks || empty($networks) ) return '';

		//Allow the icon class to be filtered
		$network_names = array_flip(array_column($networks, 'network'));
		foreach( $network_names as $k => $network ) {
			$network_names[$k] = array('fa-'.$k);
		}
		$networkIconClasses = apply_filters('mcFoundation_social_list_icon_class', $network_names, $listID);

		$defaults = array(
			'iconsOnly' => true,
			'stack' => true,
			'shortcode' => false,
			'stackClass' => array(
				'fa' => 'fa',
				'shape' => 'fa-circle',
				'stack' => 'fa-stack-2x'
			),
			'networkIcons' => $networkIconClasses,
			'iconClasses' => array(
				'fa' => 'fa',
				'inverse' => 'fa-inverse',
				'stack' => 'fa-stack-1x'
			),
			'linkClass' => array(
				'stack' => 'fa-stack'
			),
			'listClass' => array(
				'mcSocialIcons',
				'no-bullet',
				'colors' => 'showColors',
				'location' => $options['location'] ? 'location-'.$options['location'] : '',
				'display' => ''//or 'inline-list'
			)
		);

		/**
		* Filter: mcFoundation_social_list_options
		*
		* @param array $options
		* @param string $listID
		* @return array
		*/
		$options = apply_filters('mcFoundation_social_list_options', wp_parse_args($options, $defaults), $listID);

		/**
		* Filter: mcFoundation_social_list_class
		*
		* @param array $classes
		* @param string $listID
		* @param bool $shortcode Whether the filter was called from a shortcode or not.
		* @return array
		*/
		$listClasses = apply_filters('mcFoundation_social_list_class', $options['listClass'], $listID, $options);

		//If the icon is not to be stacked, remove the appropriate classes
		if( !$options['stack'] ) {
			$options['iconClasses']['stack'] = '';
			$options['linkClass']['stack'] = '';
		}

		$items = array();

		foreach( $networks as $network ) {
			ob_start();
			$networkName = $network['other_network'] ? $network['other_network'] : $network['network'];

			//Show the icon
			echo '<li class="'.strtolower($networkName).'">';
			echo '<a href="'.$network['url'].'" target="_blank" class="'.implode(' ', $options['linkClass']).'">';

				//Is the item stacked?
				if( $options['stack'] ) {
					echo '<i class="'.implode(' ', $options['stackClass']).'"></i>';
				}

				$iconClasses = array_merge($options['networkIcons'][$networkName], $options['iconClasses']);
				echo '<i class="'.implode(' ', $iconClasses).'"></i>';
				echo !$options['iconsOnly'] ? '<span class="networkName">'.ucwords($networkName).'</span>' : '';
			echo '</a>';
			echo '</li>';
			$items[] = ob_get_clean();
		}

		$items = apply_filters('mcFoundation_social_list_items', $items, $listID, $options);

		$list = '<ul id="'.$listID.'" class="'.implode(' ', $listClasses).'">';
		foreach( $items as $item ) {
			$list .= $item;
		}
		$list .= '</ul>';

		return $list;
	}//list_social_icons()

	/***********************************************************************
	* !FORM VALIDATION & SANITIZATION UTILITIES
	/***********************************************************************/
	/**
	* ACCEPTABLE VALUES
	* Sanitizes a value against a whitelist of acceptable values. Returns a
	* default value if nothing matches.
	*
	* @param array $acceptable Array of acceptable values
	* @param mixed $default The default value to return if nothing is acceptable
	* @param mixed $current The current value to check
	* @return mixed
	*/
	static function acceptableValues($acceptable, $default, $current) {
		return in_array($current, $acceptable) ? $current : $default;
	}

	/***********************************************************************
	* !FILE HANDLING
	/***********************************************************************/
	/**
	* Checks a string to ensure it's valid JSON
	*
	* @param string $string Suspected value
	* @return bool|mixed If JSON, return the appropriate type, otherwise false. Use string equality when checking against this value.
	*/
	static function isJson($string) {
	 $json = json_decode($string);
	 return json_last_error() == JSON_ERROR_NONE ? $json : false;
	}

	/**
	* GET JSON FILE
	* Uses file_get_contents and json_decode to get a JSON file
	*
	* @param string $file Absolute file path
	* @return string|mixed Blank string if file does not exist, or is not valid JSON. Otherwise the appropriate JSON decoded PHP type.
	*/
	static function getJsonFile($file) {

		if( !file_exists($file) ) return '';
		$json = ManifestFramework::isJson(file_get_contents($file));

		return $json === false ? '' : $json;
	}

	/**
	* GET JSON VALUE
	*
	* @param string $file An absolute file path
	* @param string $key The named key to check
	* @param mixed $default A default to return if the key doesn't exist or is null
	* @return mixed The requested key
	*/
	static function getJsonValue($file, $key, $default = '') {
		$json = ManifestFramework::getJsonFile($file);

		if( !$json || !isset($json->$key) ) return $default;
		return $json->$key;
	}

	/**
	* GET BOWER VALUE
	* Fetches a named key value from a .bower.json file
	*
	* @param string $component bower_components path like 'fontawesome'
	* @param string $key The named key to check
	* @param mixed $default A default to return if the key doesn't exist or is null
	* @return mixed The requested key
	*/
	static function getBowerValue($component, $key, $default = '') {
		$file = file_exists(BOWER_PATH.'/'.$component.'/.bower.json') ? BOWER_PATH.'/'.$component.'/.bower.json' : BOWER_PATH.'/'.$component.'/bower.json';
		return ManifestFramework::getJsonValue($file, $key, $default);
	}

	/***********************************************************************
	* !GLOBAL VARIABLE ACCESS
	/***********************************************************************/

	/**
	* GET GLOBAL KEYS
	* Makes inspecting globals a bit easier
	*
	* @return array Array containing the global key names.
	*/
	static function getGlobalKeys() {
		return array_keys($GLOBALS);
	}

	/**
	* GET GLOBAL
	* Essentially the same as using 'global $key'
	*
	* @param string $key The keyed global
	* @return mixed
	*/
	static function getGlobal($key) {
		if( !array_key_exists($key, $GLOBALS) ) return '';
		return $GLOBALS[$key];
	}

	/***********************************************************************
	* !TEMPLATE PARTS
	/***********************************************************************/
	/**
	* ARCHIVE TITLES
	* Show different headings based on what archive page is being displayed.
	*/
	static function get_archive_titles() {
		if( !is_home() && !is_post_type_archive('post') && !is_search() ) return;
		global $wp_query;
		echo '<div class="entryTitle blogArchiveTitle">';

			if( !is_search() ) {
				echo '<h1>'.apply_filters('mcFoundation_blog_title', get_bloginfo('name').' Blog').'</h1>';
			}
			else {
				echo '<h1>'.apply_filters('mcFoundation_search_results_title', 'Search Results').'</h1>';
			}

			if( is_paged() ) {
				echo '<p class="paged">Page <span class="current">'.$wp_query->query['paged'].'</span> of <span class="total">'.$wp_query->max_num_pages.'</span></p>';
			}

			if( is_category() || is_tag() ) {
				$term = get_term_by('slug', $wp_query->queried_object->slug, $wp_query->queried_object->taxonomy);
				$string = 'Posts '.($term->taxonomy == 'category' ? 'categorized as' : 'tagged with').' "'.$term->name.'"';
				echo '<h5 class="subheader">'.$string.'</h5>';
			}

			if( is_date() ) {
				$time = strtotime($wp_query->query['year'].'-'.$wp_query->query['monthnum'].'-01');
				echo '<h5 class="subheader">Posted during '.date('F Y', $time).'</h5>';
			}

			if( is_search() ) {
				echo '<h5 class="subheader">Found '._n('1 result', $wp_query->post_count.' results', $wp_query->post_count)  .' while searching for "'.get_search_query().'"</h5>';
			}
		echo '</div>';
	}//get_archive_titles

	/**
	* FOUNDATION SEARCH FORM
	*
	* @return string
	*/
	static function get_search_form() {
		ob_start();

		echo '<form role="search" method="get" id="searchform" class="searchform" action="' . home_url( '/' ) . '" >';
		echo '<div class="row collapse postfix-radius">';
			echo '<div class="small-8 columns">';
				echo '<input type="text" name="s" value="'.get_search_query().'" id="s" />';
			echo '</div>';
			echo '<div class="small-4 columns"><input type="submit" name="searchSubmit" value="Search" class="button postfix radius" /></div>';
		echo '</div>';
		echo '</form>';

		return ob_get_clean();
	}

	/***********************************************************************
	* !FOUNDATION KITCHEN SINK
	/***********************************************************************/
	static function kitchenSink() {
		get_template_part('templates/kitchensink', ENVIRONMENT);
	}//kitchenSink()

	/***********************************************************************
	* !UTILITIES
	/***********************************************************************/

	/**
	* GET ARCHIVE TYPE
	* Goes through the standard WP archive types and returns an array containing the archive type as the first element and the unique identifier/slug as the second element. Mostly useful in conjunction with @see get_template_part
	* @todo Consider adding date based archives
	*
	* @return array
	*/
	static function getArchiveType() {

		if( !is_archive() ) return null;

		$type = array();

		//Post Type Archives
		if ( is_post_type_archive() ) {
			$type[0] = 'post-type';
			$post_type = get_query_var( 'post_type' );
			$type[1] = is_array($post_type) ? sanitize_html_class(array_shift($post_type)) : sanitize_html_class($post_type);
			return $type;
		}

		//Category Archives
		if ( is_category() ) {
			$cat = get_queried_object();
			$type[0] = 'category';
			if ( isset( $cat->term_id ) ) {
				$type[1] = sanitize_html_class($cat->slug);
			}
			return $type;
		}

		//Tag Archives
		if ( is_tag() ) {
			$tag = get_queried_object();
			$type[0] = 'tag';
			if ( isset( $tag->term_id ) ) {
				$type[1] = sanitize_html_class($tag->slug);
			}
			return $type;
		}

		//Taxonomy
		if ( is_tax() ) {
			$term = get_queried_object();
			$type[0] = 'tax';
			$type[1] = sanitize_html_class($term->taxonomy);
			return $type;
		}

		//Author Archives
		if ( is_author() ) {
			$author = get_queried_object();
			$type[0] = 'author';
			$type[1] = sanitize_html_class( $author->user_nicename);
			return $type;
		}

	}//getArchiveType()

}//ManifestFramework
