<?php get_header(); ?>
	<?php do_action('mcFoundation_before_content_container'); ?>

			<?php
			do_action('mcFoundation_before_content');
			if( have_posts() ):
				
				ManifestFramework::kitchenSink();
				
			else:
				get_template_part('templates/content', 'none');
			endif;
			
			do_action('mcFoundation_after_content');
			?>
			

	<?php do_action('mcFoundation_after_content_container'); ?>
<?php get_footer(); ?>
