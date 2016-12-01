<?php
/**
	Template Name: Page Builder
**/
	get_header();
	do_action('mcFoundation_before_content_container');
		if( have_posts() ): the_post();
			do_action('mcFoundation_before_content');
				
				global $pageBuilder;
				echo $pageBuilder->getContent();
								
			do_action('mcFoundation_after_content');
				
		else:
			do_action('mcFoundation_before_content');
			get_template_part('templates/content', 'none');
			do_action('mcFoundation_after_content');
		endif;
	do_action('mcFoundation_after_content_container');
get_footer(); ?>
