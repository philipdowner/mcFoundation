<?php
/**
* mcScripts
* Enqueue front-end scripts
*/
add_action( 'wp_enqueue_scripts', 'mcFoundation_scripts' );
function mcFoundation_scripts() {
  
  $mcFoundationVersion = ManifestFramework::getJsonValue(get_stylesheet_directory().'/package.json', 'version', '2.1.4');
  
  if( current_theme_supports('typekit') ) {
	 $typekitID = mcFoundation_getSiteConfig('config_typekit_id');
	 wp_enqueue_script( 'typekit', "//use.typekit.net/$typekitID.js", array(), $mcFoundationVersion, false);
  }

  // register scripts
  wp_register_script( 'modernizr', BOWER_DIR . '/modernizr/modernizr.js', array(), ManifestFramework::getBowerValue('modernizr', 'version', $mcFoundationVersion), false );
  
  wp_register_script('fastclick', BOWER_DIR . '/fastclick/lib/fastclick.js', ManifestFramework::getBowerValue('fastclick', 'version', $mcFoundationVersion), true);
  
  wp_register_script( 'foundation', BOWER_DIR . '/foundation/js/foundation.min.js', array('jquery', 'fastclick'), ManifestFramework::getBowerValue('foundation', 'version', $mcFoundationVersion), true );
  
  wp_register_script( 'slick-carousel', BOWER_DIR . '/slick-carousel/slick/slick.min.js', array('foundation'), ManifestFramework::getBowerValue('slick-carousel', 'version', $mcFoundationVersion), true);
  
  //Initialize Foundation and MCFoundation
  wp_register_script( 'mcFoundation-init', JS_DIR . '/dist/mcFoundation-init.jquery.min.js', array('foundation', 'slick-carousel'), $mcFoundationVersion, true);

  //Enqueue scripts
  wp_enqueue_script('modernizr');
  wp_enqueue_script('foundation');
  wp_enqueue_script('mcFoundation-init');
  
  //Localize scripts
  wp_localize_script('foundation', 'mcAjax', array(
	  'ajaxUrl' => admin_url('admin-ajax.php'),
	  'siteUrl' => get_bloginfo('url'),
	  'themeUrl' => get_template_directory_uri()
  ));

}//mcScripts()

/**
* mcStyles
* Enqueue front-end styles
*/
add_action('wp_enqueue_scripts', 'mcStyles');
function mcStyles() {
	$mcFoundationVersion = ManifestFramework::getJsonValue(get_stylesheet_directory().'/package.json', 'version', '2.1.0');
	wp_register_style('foundation', CSS_DIR . '/foundation.min.css', array(), $mcFoundationVersion, 'all');
	wp_enqueue_style('foundation');
	
}//mcStyles()

/**
* Inline Header Scripts
* Keep in mind this function is attached to both the front-end (wp_head) and admin (admin_head) hooks. If you desire just a single location, you'll need to conditionally output the content using is_admin()
*/
add_action('wp_head', 'mcFoundation_headerScripts');
add_action('admin_head', 'mcFoundation_headerScripts');
function mcFoundation_headerScripts() {
	if( current_theme_supports('typekit') ) {
		echo '<script>try{Typekit.load();}catch(e){}</script>';
	}
}
