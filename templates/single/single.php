<?php
/**
* The default template for displaying page content
*
*/
echo '<article id="post-'.get_the_ID().'" class="'.implode(' ', get_post_class()).'">';

	get_template_part('templates/components/headline', 'single');
	
	echo '<div class="entry-content">';
	the_content();
	echo '</div>';//.entry-content
	
echo '</article>';
?>