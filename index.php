<?php get_header(); ?>
	<div class="row">
		
		<?php
		do_action('mcFoundation_before_content_container');
			do_action('mcFoundation_before_content');
			
			if( have_posts() ):
				echo '<ul class="postsList no-bullet">';
				while ( have_posts() ): the_post();
					echo '<li>';
					get_template_part('templates/loop', get_post_type() );
					echo '</li>';
				endwhile;
				
			else:
				get_template_part('templates/content', 'none');
			endif;
			
			do_action('mcFoundation_after_content');
		do_action('mcFoundation_after_content_container');
		?>
		
	</div><!-- .row -->
<?php get_footer(); ?>
