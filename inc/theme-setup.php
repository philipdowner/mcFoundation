<?php
/**
* THEME SETUP
* Handles registration of navigation menus and other theme setup tasks
*
* @author Philip Downer <philip@manifestbozeman.com>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @version v1.0
*/

/**
* GET THEME OPTIONS
*
* @todo Extrapolate into ACF or a JSON file
*/
function mcFoundation_getThemeOptions() {
	$theme_options = array(
		'setup' => array(
			'useMobileOffCanvas' => true
		),
		'nav' => array(
			'primary' => array(
				'description' => 'Primary site menu',
				'showTitle' => false,
				'containToGrid' => true,
				'sticky' => true,
			),
			'mobile' => array(
				'description' => 'Mobile off-canvas menu',
				'multilevel' => true,
				'position' => 'left'
			),
			'footer' => array(
				'description' => 'Footer menu'
			)
		),
	);
	return apply_filters('mcFoundation_get_theme_options', $theme_options);
}//mc_getThemeOptions()

/**
* GET SITE CONFIG
* Fetches a named value from the ACF Site Config
*
* @param string $key The key of the field you wish to fetch
* @return mixed
*/
function mcFoundation_getSiteConfig($key) {
	
	if( !function_exists('get_field') ) return null;
	
	return get_field($key, 'option');
	
}
	
/**
* mcFoundation Theme Setup
*
* Aways attached to after_setup_theme hook
*/
add_action('after_setup_theme', 'mcFoundation_themeSetup');
function mcFoundation_themeSetup() {
	
	$themeOptions = mcFoundation_getThemeOptions();
	
	//Register the nav menus
	$navMenus = array();
	foreach( $themeOptions['nav'] as $menuSlug => $menu ) {
		$navMenus[$menuSlug] = array_key_exists('description', $menu) ? $menu['description'] : ucfirst($menuSlug) . ' menu';
	}
	register_nav_menus($navMenus);
	
	//Using off-canvas mobile menu?
	if( $themeOptions['setup']['useMobileOffCanvas'] === false ) {
		remove_action('mcFoundation_after_body', 'mcFoundation_offCanvasWrapOpen');
		remove_action('mcFoundation_before_body_close', 'mcFoundation_offCanvasWrapClose');
	} else {
		add_action('mcFoundation_after_body', 'mcFoundation_offCanvasWrapOpen', 20);
		add_action('mcFoundation_before_body_close', 'mcFoundation_offCanvasWrapClose', 20);
	}
	
	//Conditionally register theme support
	if( $typekitID = mcFoundation_getSiteConfig('config_typekit_id') ) {
		add_theme_support('typekit');
	}
	
	/**
	* Theme Support
	**/
	//add_theme_support( 'post-formats', array( 'aside', 'link', 'gallery', 'status', 'quote', 'video', 'audio', 'image', 'chat' ) );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption' ) );
	
}//mcFoundation_themeSetup();

/**
* THEME IMAGE SIZES
*/
add_action('init', 'mcFoundation_themeImageSizes');
function mcFoundation_themeImageSizes() {

	//Hero image size
	$dimensions = ManifestFramework::dimensionsByRatio(2000);
	add_image_size('mc_crop_2000', $dimensions['width'], $dimensions['height'], true);
	add_image_size('mc_wide_2000', 2000, 750, true);

	//Set some filterable values for looping thru images	
	$max = apply_filters('mcFoundation_image_size_max', 1200);
	$min = apply_filters('mcFoundation_image_size_min', 200);
	$step = apply_filters('mcFoundation_image_size_step', 200);
	
	//Make the largest image wider so it includes column gutters
	$gutter = 30;
	$dimensions = ManifestFramework::dimensionsByRatio($max + $gutter);
	add_image_size('mc_'.$max, $dimensions['width'], $dimensions['width'], false);
	add_image_size('mc_crop_'.$max, $dimensions['width'], $dimensions['height'], true);
	$dimensions = ManifestFramework::dimensionsByRatio($max + $gutter, 123, 55);
	add_image_size('mc_wide_'.$max, $dimensions['width'], $dimensions['height'], true);
	
	//Exclude this newly created size from the next loop
	$max = $max - $step;
	
	while( $max >= $min ) {
		$dimensions = ManifestFramework::dimensionsByRatio($max);
		add_image_size('mc_'.$max, $dimensions['width'], $dimensions['width'], false);
		add_image_size('mc_square_'.$max, $dimensions['width'], $dimensions['width'], true);
		add_image_size('mc_crop_'.$max, $dimensions['width'], $dimensions['height'], true);
		if( $max >= 600 ) {
			$dimensions = ManifestFramework::dimensionsByRatio($max, 16, 7);
			add_image_size('mc_wide_'.$max, $dimensions['width'], $dimensions['height'], true);
		}
		$max -= $step;
	}
}

/***********************************************************************
* !GRAVITY FORMS
/***********************************************************************/

/**
* GFORM ENQUEUE STYLES
* Remove the default styles and conditionally load the date picker stylesheet
*
* @param obj $form
* @param bool $ajax
*/
add_action('gform_enqueue_scripts', 'mcFoundation_gform_enqueue_styles', 10, 2);
function mcFoundation_gform_enqueue_styles($form, $ajax) {
	if( is_admin() ) return;

	global $wp_styles;
	$styles = array();
	foreach( $wp_styles->queue as $style ) {
		if( strpos($style, 'gform') !== false ) {
			$styles[] = $style;
		}
	}
	
	//Check if the datepicker style is needed, and enqueue our own version
	$datepickerRequired = wp_style_is('gforms_datepicker_css');

	//Dequeue the primary styles
	foreach( $styles as $style ) {
		wp_dequeue_style($style);
	}
	
	if( $datepickerRequired ) {
		wp_enqueue_style('mc_gforms_datepicker_css', CSS_DIR.'/gforms-datepicker.min.css', array('foundation'), false, 'all');
	}
	
}

/**
* GFORM ARGUMENTS
* Filter the arguments passed to GF by the shortcode. Removes the display title to allow it to be displayed inside a containing div.
*
* @param array $args
* @return array
*/
add_filter('gform_form_args', 'mcFoundation_gform_args');
function mcFoundation_gform_args($args) {
	if( $args['display_title'] ) {
		$args['display_title'] = false;
	}
	return $args;
}

/**
* GFORM GET FORM FILTER
* Replaces the ul.gfields element with a div
*
* @param string $string The HTML for the form
* @param obj $form
* @return $string
*/
//add_filter('gform_get_form_filter', 'mcFoundation_gform_get_form_filter', 10, 2);
function mcFoundation_gform_get_form_filter($string, $form) {
	return $string;
}

/**
* GFORM PRE-RENDER
* Adds p tags to the description and moves the form title inside a containing div.
*
* @param obj $form
* @return obj
*/
add_filter('gform_pre_render', 'mcFoundation_gform_pre_render');
function mcFoundation_gform_pre_render($form) {
	if( is_admin() ) return $form;

	$description = '';
	
	if( $form['title'] ) {
		$description .= '<h3 class="gform_title">'.$form['title'].'</h3>';
		$form['title'] = '';
	}
	
	if( $form['description'] ) {
		$description .= wpautop($form['description']);
	}
	
	$form['description'] = $description;
	
	return $form;
}

/**
* GFORM FIELD CONTAINER
* Wraps fields in a div.row element.
*
* @param $field_container $string A string with '{FIELD_CONTENT}' representing the actual input or field.
* @param obj $field
* @param obj $form
* @param string $css_class
* @param string $style Deprecated. See gforms API for details
* @param string $field_content
* @return string
*/
add_filter('gform_field_container', 'mcFoundation_gform_field_container', 10, 6);
function mcFoundation_gform_field_container($field_container, $field, $form, $css_class, $style, $field_content) {
	if( is_admin() ) return $field_container;
	
	$id = 'field_'.$field->formId.'_'.$field->id;
	$classes = 'row '.$css_class;
	$field_container = '<li id="'.$id.'" class="'.$classes.'">{FIELD_CONTENT}</li>';
	
	return $field_container;
}

/**
* GFORM FIELD CONTENT
* Replaces li elements with Foundation compliant div elements. Adds additional utility classes. Filters field content with properly structured HTML.
*
* @param string $field_content
* @param obj $field
* @param string|mixed The current value of the field
* @param int $entry_id
* @param int $form_id
* @return string
*/
add_filter('gform_field_content', 'mcFoundation_gform_field_content', 10, 5);
function mcFoundation_gform_field_content($field_content, $field, $value, $entry_id, $form_id) {
	
	//Don't do anything in the admin!
	if( is_admin() ) {
		return $field_content;
	}
	
	$form = GFAPI::get_form( $form_id );
	$fieldId = 'input_'.$form_id.'_'.$field->id;
	$label = $field->isRequired ? $field->label.' <span class="required gfield_required">*</span>' : $field->label;
	$containerClasses = array(
		'small' => 'small-12',
		'columns' => 'columns',
		'type' => 'fieldType-'.$field->type,
		'description' => $field->description ? 'hasDescription' : 'noDescription',
		'fieldWrap' => 'fieldWrap',
		'fieldSize' => 'fieldSize-'.$field->size
	);
	
	//Chosen Enhanced UI
	if( $field->enableEnhancedUI ) {
		$containerClasses['chosen'] = 'isEnhancedUi';
	}
	
	ob_start();
	switch( $field->type ) {
		
		//Section Breaks
		case 'section':
			
			echo '<div class="gsection_container">';
				echo '<h4 class="gsection_title">'.$field->label.'</h4>';
	
				if( $field->description ) {
					echo '<div class="gsection_description">';
					echo wpautop($field->description);
					echo '</div>';
				}
			echo '</div>';
		break;
		
		//Checkboxes and radios
		case 'checkbox':
		case 'radio':
			$description = $field->get_description($field->description, 'gfield_description');
			
			/**
				* If we don't remove the "Other" choice, gForms automatically adds it back
				* to the $field object when @see get_radio_choices, is called.
				* The result is 2 duplicate fields.
			**/
			if( $field->enableOtherChoice ) {
				array_pop($field->choices);
			}
			
			echo '<label class="gfield_label">'.$label.'</label>';
			echo $field->is_description_above($form) ? $description : '';
			echo '<ul id="'.$fieldId.'" class="ginput_container ginput_container_'.$field->type.' inputGroup '.$field->type.'Group">';
				if( 'checkbox' == $field->type ) {
					echo $field->get_checkbox_choices('', '', $form_id );
				}
				else {
					echo $field->get_radio_choices('', '', $form_id);
				}
			echo '</ul>';
			echo $field->is_description_above($form) ? '' : $description;
		break;
		
		//Name fields
		case 'name':
			$containerClasses['nameFormat'] = 'nameFormat-'.$field->nameFormat;
			echo $field_content;
		break;
			
		case 'date':
			$containerClasses['dateType'] = 'dateType-'.$field->dateType;
			$containerClasses['medium'] = 'medium-7';
			echo $field_content;
		break;
		
		case 'time':
			$containerClasses['medium'] = 'medium-7';
			echo $field_content;
		break;
		
		case 'address':
			$containerClasses['addressType'] = 'addressType-'.$field->addressType;
			//do_dump($field);
			echo $field_content;
		break;
		
		case 'list':
			$containerClasses['enableColumns'] = $field->enableColumns ? 'hasColumns' : 'noColumns';
			echo $field_content;
			//do_dump($field);
		break;
		
		case 'post_image':
			$containerClasses['imageMeta'] = $field->displayTitle || $field->displayCaption || $field->displayDescription ? 'hasMeta' : 'noMeta';
			echo $field_content;
		break;
		
		case 'option':
			//do_dump($field);
			//do_dump($form);
			$containerClasses['inputType'] = 'inputType-'.$field->inputType;
			$description = $field->get_description($field->description, 'gfield_description');
			
			if( 'checkbox' == $field->inputType || 'radio' == $field->inputType ) {
				echo '<label class="gfield_label">'.$label.'</label>';
				echo $field->is_description_above($form) ? $description : '';
				echo '<ul id="'.$fieldId.'" class="ginput_container ginput_container_'.$field->inputType.' inputGroup '.$field->inputType.'Group">';
					if( 'checkbox' == $field->inputType ) {
						echo $field->get_checkbox_choices('', '', $form_id );
					}
					else {
						echo $field->get_radio_choices('', '', $form_id);
					}
				echo '</ul>';
			}
			
			echo $field->is_description_above($form) ? '' : $description;
			
		break;
		
		//All other fields
		default:
			echo $field_content;
		break;
	}
	
	$field_content = ob_get_clean();
	
	return '<div class="'.implode(' ', $containerClasses).'">'.$field_content.'</div>';
}
