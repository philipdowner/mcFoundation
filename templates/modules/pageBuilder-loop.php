<?php
/**
* The default template for displaying content in a loop. Use loop-$posttype.php for fine-grained control.
*
*/
echo '<article id="post-'.get_the_ID().'" class="'.implode(' ', get_post_class()).'">';
	if( has_post_thumbnail() ) {
		echo '<div class="postThumb">';
		echo '<a href="'.get_permalink().'">';
		the_post_thumbnail('post-thumbnail', array('class' => 'th'));
		echo '</a>';
		echo '</div>';
	}
	
	get_template_part('templates/components/headline', 'pageBuilder');
	
	echo '<div class="entry-content">';
	the_excerpt();
	echo '</div>';//.entry-content
echo '</article>';
?>