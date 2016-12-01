<?php
/**
* The default template for displaying content in a loop. Use loop-$posttype.php for fine-grained control.
*
*/
echo '<article id="post-'.get_the_ID().'" class="'.implode(' ', get_post_class()).'">';

	if( has_post_thumbnail() ) {
	echo '<div class="post-thumbnail">';
		echo '<a href="'.get_permalink().'" class="thumb">';
		echo ManifestFramework::responsiveImg(get_post_thumbnail_id(), array(
			'small' => 'mc_crop_400',
			'medium' => 'mc_crop_600',
			'large' => 'mc_crop_600',
			'default' => 'mc_crop_600'
		), array('th'));
		echo '</a>';
	echo '</div>';
	}

	echo '<div class="entry-content">';
		get_template_part('templates/components/headline', get_post_type());
		the_excerpt();
		echo '<p class="more"><a href="'.get_permalink().'" class="button small">Read More &raquo;</a></p>';
	echo '</div>';//.entry-content
echo '</article>';
?>