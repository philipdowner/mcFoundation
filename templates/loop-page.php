<?php
/**
* The default template for displaying content in a loop. Use loop-$posttype.php for fine-grained control.
*
*/
echo '<article id="post-'.get_the_ID().'" class="'.implode(' ', get_post_class()).'">';
	get_template_part('templates/components/headline', 'single');
	
	echo '<div class="entry-content">';
	the_content();
	echo '</div>';//.entry-content
echo '</article>';
?>