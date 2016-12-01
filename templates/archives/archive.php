<?php
echo '<div class="row">';
	echo '<div class="small-12 columns">';
	
	if( have_posts() ):
		echo '<ul class="postsList postsListType-'.$post->post_type.'">';
		while ( have_posts() ): the_post();
			echo '<li>';
			get_template_part('templates/loop', get_post_type() );
			echo '</li>';
		endwhile;
		
	else:
		get_template_part('templates/content', 'none');
	endif;
	
	echo '</div>';
echo '</div>';	
	
