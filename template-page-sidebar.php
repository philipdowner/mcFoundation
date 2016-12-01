<?php
/**
Template Name: With Sidebar
**/
get_header(); ?>
	<div class="row">
		<?php
		do_action('mcFoundation_before_content_container');
			do_action('mcFoundation_before_content');
				if( have_posts() ):
					while ( have_posts() ): the_post();
						get_template_part('templates/single/single', get_post_type() );
					endwhile;
				else:
					get_template_part('templates/content', 'none');
				endif;
				
			do_action('mcFoundation_after_content');
		do_action('mcFoundation_after_content_container');
		?>
	</div><!-- .row -->
<?php get_footer(); ?>