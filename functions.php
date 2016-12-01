<?php
/************************************************************************
PROJECT NAME: Manifest Foundation
START DATE: 2015/01/02
Version: 2.1.6
GENERAL CONTACT: Manifest Creative
DESIGNER: Philip Downer
FRONT-END PROGRAMMER: Philip Downer
BACK-END PROGRAMMER:Philip Downer
************************************************************************/

/***********************************************************************
* !CONSTANTS
/***********************************************************************/
define('INCLUDES_DIR', get_template_directory().'/inc');
define('ASSETS_DIR', get_template_directory_uri().'/assets');
define('IMG_DIR', get_template_directory_uri().'/assets/images');
define('IMG_PATH', get_template_directory().'/assets/images');
define('CSS_DIR', get_template_directory_uri().'/css');
define('JS_DIR', get_template_directory_uri().'/js');
define('BOWER_DIR', get_template_directory_uri().'/bower_components');
define('BOWER_PATH', get_template_directory().'/bower_components');
define('TEXTDOMAIN', 'manifestFoundation');

/***********************************************************************
* !REQUIRED PHP FILES
/***********************************************************************/
/** VENDOR FILES - COMPOSER AUTOLOAD **/
require_once(INCLUDES_DIR.'/vendor/autoload.php');

/** GLOBAL FRAMEWORK **/
require_once(INCLUDES_DIR.'/global.php');
require_once(INCLUDES_DIR.'/theme-setup.php');
require_once(INCLUDES_DIR.'/sidebars.php');
require_once(INCLUDES_DIR.'/shortcodes.php');
require_once(INCLUDES_DIR.'/admin.php');
require_once(INCLUDES_DIR.'/security.php');
require_once(INCLUDES_DIR.'/debug/dump.php');

/** FOUNDATION FRAMEWORK **/
require_once(INCLUDES_DIR.'/foundation.php');
require_once(INCLUDES_DIR.'/navigation.php');

/** SCRIPTS **/
require_once(INCLUDES_DIR.'/enqueue-scripts.php');

/** MANIFEST FRAMEWORK **/
require_once(INCLUDES_DIR.'/lib/c_ManifestFramework.php');
require_once(INCLUDES_DIR.'/lib/c_ManifestComments.php');
require_once(INCLUDES_DIR.'/lib/c_PageBuilder.php');
require_once(INCLUDES_DIR.'/lib/c_Downloads.php');
require_once(INCLUDES_DIR.'/lib/c_Form.php');
require_once(INCLUDES_DIR.'/lib/c_Mail.php');
require_once(INCLUDES_DIR.'/lib/c_Testimonial.php');

/** CUSTOM POST TYPES & TAXONOMIES **/
//require_once(INCLUDES_DIR.'/post-types/cpt-template.php'); //A quick setup template for custom post types & taxonomies
require_once(INCLUDES_DIR.'/post-types/cpt-projects.php');
require_once(INCLUDES_DIR.'/post-types/cpt-testimonials.php');
require_once(INCLUDES_DIR.'/post-types/cpt-downloads.php');

/** WIDGETS **/
require_once(INCLUDES_DIR.'/widgets/widget-about.php');

/**
CLIENT LEVEL CUSTOMIZATIONS
~ Includes menu positioning and default sidebar widgets
**/
require_once(INCLUDES_DIR.'/customizations.php');
