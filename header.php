<!DOCTYPE html>
<html class="no-js" <?php language_attributes(); ?>>
	<head>

		<title><?php wp_title(); ?></title>
		
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
		
		<link rel="pingback" href="<?php bloginfo('pingback_url') ?>" />
		
		<?php wp_head(); ?>
		
	</head>
	
	<body <?php body_class(); ?>>
	
		<?php
		do_action('mcFoundation_after_body');
		?>
			
		<section class="container" role="document">
			<?php do_action('mcFoundation_after_header'); ?>