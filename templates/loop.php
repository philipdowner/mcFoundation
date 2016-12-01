<?php
/**
* The default template for displaying content in a loop. Use loop-$posttype.php for fine-grained control.
*
*/
echo '<article id="post-'.get_the_ID().'" class="'.implode(' ', get_post_class()).'">';
	
	if( has_post_thumbnail() ) {
		echo '<div class="post-thumbnail">';
			echo '<a href="'.get_permalink().'" class="thumb">';
			echo get_the_post_thumbnail($post->ID, 'mc_crop_400', array('class' => 'th'));
			echo '</a>';
		echo '</div>';
	}
	
	get_template_part('templates/components/headline', get_post_type());
	
	echo '<div class="entry-content">';
	the_excerpt();
	
	echo '<p class="more"><a href="'.get_permalink().'" class="button radius small secondary">Read More &raquo;</a></p>';
	echo '</div>';//.entry-content
echo '</article>';
?>
