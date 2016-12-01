<?php
/**
 * Top bar
 *
 * @param string $navMenu The theme location slug of the wp_nav_menu() to display.
 */
if ( !function_exists( 'mcFoundation_topBar' ) ) {
	function mcFoundation_topBar($navMenu) {
	    
	    $themeOptions = mcFoundation_getThemeOptions();
	    $navOptions = array_key_exists($navMenu, $themeOptions['nav']) ? $themeOptions['nav'][$navMenu] : array();
	    
	    $containerClasses = array('top-bar-container', 'show-for-medium-up');
	    $containerClasses[] = $navOptions['containToGrid'] ? 'contain-to-grid' : '';
		  $containerClasses[] = $navOptions['sticky'] ? 'sticky' : '';
		  
	    $containerClasses = apply_filters('mcFoundation_nav_container_class', $containerClasses, $navMenu);
	    
	    $menuArgs = array( 
	        'container' => false,
	        'container_class' => '',
	        'menu' => '',
	        'menu_class' => 'top-bar-menu',
	        'theme_location' => $navMenu,
	        'before' => '',
	        'after' => '',
	        'link_before' => '',
	        'link_after' => '',
	        'depth' => 5,
		      'dividers' => false,
	        'fallback_cb' => 'mcFoundation_menu_fallback',
	        'walker' => new Walker_Top_Bar()
	    );
	    
	    ob_start();
	    echo '<div id="menu-'.$navMenu.'Wrap" class="'.implode(' ', $containerClasses).'">';
		    
		    $topBarClasses = array('top-bar');
		    $topBarClasses = apply_filters('mcFoundation_topbar_class', $topBarClasses, $navMenu);
		    
		    echo '<nav class="'.implode(' ', $topBarClasses).'" data-topbar role="navigation">';
		    
			    if( $navOptions['showTitle'] ) {
				    echo '<ul class="title-area">';
					    echo '<li class="name">'.get_bloginfo('name').'</li>';
				    echo '</ul>';
			    }
			    
			    echo '<section class="top-bar-section">';
				    wp_nav_menu(apply_filters('mcFoundation_nav_menu_args', $menuArgs, $navMenu));
			    echo '</section>';
		    echo '</nav>';
	    echo '</div>';//.top-bar-container
	    return ob_get_clean();
	}//mcFoundation_navPrimary()
}

/**
 * Mobile off-canvas
 *
 * @param string $navMenu The theme location slug of the wp_nav_menu() to display.
 * @return string HTML content to create the off-canvas menu.
 */
if ( !function_exists( 'mcFoundation_offCanvas' ) ) {
	function mcFoundation_offCanvas($navMenu) {
	    
	    $themeOptions = mcFoundation_getThemeOptions();
	    $navOptions = array_key_exists($navMenu, $themeOptions['nav']) ? $themeOptions['nav'][$navMenu] : array();
	    
	    ob_start();
	    
	    $containerClasses = array();
	    $containerClasses[] = $navOptions['position'] == 'left' ? 'left-off-canvas-menu' : 'right-off-canvas-menu';
	    $containerClasses = apply_filters('mcFoundation_nav_container_class', $containerClasses, $navMenu);
	    
	    $menuArgs = array( 
        'container' => false,
        'container_class' => '',
        'menu' => '',
        'menu_class' => 'off-canvas-list',
        'theme_location' => $navMenu,
        'before' => '',
        'after' => '',
        'link_before' => '',
        'link_after' => '',
        'depth' => 2,
        'fallback_cb' => 'mcFoundation_menu_fallback',
        'walker' => new Walker_Off_Canvas()
	    );
	    
	    echo '<aside class="'.implode(' ', $containerClasses).'" aria-hidden="true">';
		    do_action('mcFoundation_before_off_canvas_menu');
		    wp_nav_menu(apply_filters('mcFoundation_nav_menu_args', $menuArgs, $navMenu));
		    do_action('mcFoundation_after_off_canvas_menu');
	    echo '</aside>';
	    
	    return ob_get_clean();
	}//mcFoundation_offCanvas()
}

/**
 * Customize the output of menus for Foundation top bar
 */
class Walker_Top_Bar extends Walker_Nav_Menu {

    function display_element( $element, &$children_elements, $max_depth, $depth=0, $args, &$output ) {
        $element->has_children = !empty( $children_elements[$element->ID] );
        $element->classes[] = ( $element->current || $element->current_item_ancestor ) ? 'active' : '';
        $element->classes[] = ( $element->has_children && $max_depth !== 1 ) ? 'has-dropdown' : '';
        
        $element->classes = apply_filters('mcNav_element_classes', $element->classes, $element, $args);
        
        parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
    }
    
    function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {
        $item_html = '';
        parent::start_el( $item_html, $object, $depth, $args ); 
        
        //do_dump($args);
        
        $output .= ( $depth == 0 && $args->dividers !== false ) ? '<li class="divider"></li>' : '';
        
        $classes = empty( $object->classes ) ? array() : (array) $object->classes;  
        
        if( in_array('label', $classes) ) {
            $output .= '<li class="divider"></li>';
            $item_html = preg_replace( '/<a[^>]*>(.*)<\/a>/iU', '<label>$1</label>', $item_html );
        }
        
		    if ( in_array('divider', $classes) ) {
		        $item_html = preg_replace( '/<a[^>]*>( .* )<\/a>/iU', '', $item_html );
		    }
		        
        $output .= $item_html;
    }
    
    function start_lvl( &$output, $depth = 0, $args = array() ) {
        $output .= "\n<ul class=\"sub-menu dropdown\">\n";
    }
    
}//Top_Bar_Walker

/**
 * Customize the output of menus for Foundation off canvas menu
 */
class Walker_Off_Canvas extends Walker_Nav_Menu {

    function display_element( $element, &$children_elements, $max_depth, $depth=0, $args, &$output ) {
        $element->has_children = !empty( $children_elements[$element->ID] );
        $element->classes[] = ( $element->current || $element->current_item_ancestor ) ? 'active' : '';
        //$element->classes[] = ( $element->has_children && $max_depth !== 1 && $depth == 0 ) ? 'has-children' : '';
        $element->classes[] = $depth === 0 ? 'top-level' : '';
        
        //$element->classes[] = ( $element->has_children && $depth == 0) ? 'label' : '';
        
        parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
    }
    
    function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {
        $item_html = '';
        
        parent::start_el( $item_html, $object, $depth, $args );
        
        //do_dump($object);
        
        $classes = empty( $object->classes ) ? array() : (array)$object->classes;  
        
        if( in_array('top-level', $classes) ) {
           $item_html = preg_replace( '/<a[^>]*>(.*)<\/a>/iU', '<label><a href="'.$object->url.'">$1</a></label>', $item_html );
        }
        
        $output .= $item_html;
    }
    
    function start_lvl( &$output, $depth = 0, $args = array() ) {
        //$output .= "\n<ul class=\"sub-menu dropdown\">\n";
        $output .= '';
    }
    
    function end_lvl( &$output, $depth = 0, $args = array() ) {
	    $output .= '';
    }
    
}//Top_Bar_Walker

/**
 * Menu Fallback 
 * A fallback when no navigation is selected by default.
 */
function mcFoundation_menu_fallback() {
	echo '<div class="alert-box secondary">';
	// Translators 1: Link to Menus, 2: Link to Customize
  	printf( __( 'Please assign a menu to the primary menu location under %1$s or %2$s the design.', TEXTDOMAIN ),
  		sprintf(  __( '<a href="%s">Menus</a>', TEXTDOMAIN ),
  			get_admin_url( get_current_blog_id(), 'nav-menus.php' )
  		),
  		sprintf(  __( '<a href="%s">Customize</a>', TEXTDOMAIN ),
  			get_admin_url( get_current_blog_id(), 'customize.php' )
  		)
  	);
  	echo '</div>';
}

/**
* Foundation Active Nav Class
* Add Foundation 'active' class for the current menu item
*
* @param array $classes Array of current classes
* @return array
*/
add_filter( 'nav_menu_css_class', 'mcFoundation_active_nav_class', 10, 2 );
function mcFoundation_active_nav_class( $classes, $item ) {
  if ( $item->current == 1 || $item->current_item_ancestor == true ) {
      $classes[] = 'active';
  }
  return $classes;
}

/**
* Foundation Menu Buttons
*
* Add support for buttons in the top-bar menu: 
* 1) In WordPress admin, go to Apperance -> Menus. 
* 2) Click 'Screen Options' from the top panel and enable 'CSS Classes' and 'Link Relationship (XFN)'
* 3) On your menu item, type 'has-form' in the CSS-classes field. Type 'button' in the XFN field
* 4) Save Menu. Your menu item will now appear as a button in your top-menu
*
* @param string $ulclass
* @return string
*/
add_filter('wp_nav_menu','mcFoundation_menuButtons');
function mcFoundation_menuButtons($ulclass) {
    $find = array('/<a rel="button"/', '/<a title=".*?" rel="button"/');
    $replace = array('<a rel="button" class="button"', '<a rel="button" class="button"');
    
    return preg_replace($find, $replace, $ulclass, 1);
}//mcFoundation_menuButtons()

/**
* mcFoundation Off Canvas Wrap Open
*/
function mcFoundation_offCanvasWrapOpen() {
	echo '<div class="off-canvas-wrap" data-offcanvas>';
		echo '<div class="inner-wrap">';
		
		echo '<nav class="tab-bar hide-for-medium-up">';
			echo '<section class="left-small">';
				echo '<a class="left-off-canvas-toggle menu-icon" href="#"><span></span></a>';
			echo '</section>';
			echo '<section class="middle tab-bar-section">';
				echo '<h5 class="title menuTitle">' . get_bloginfo( 'name' ) . '</h5>';
			echo '</section>';
		echo '</nav>';
		do_action('mcFoundation_off_canvas_menu');
}

/**
* McFoundation Off Canvas Wrap Close
*/
function mcFoundation_offCanvasWrapClose() {
			echo '<a class="exit-off-canvas"></a>';
		echo '</div>';//.inner-wrap
	echo '</div>';//.off-canvas-wrap
}
