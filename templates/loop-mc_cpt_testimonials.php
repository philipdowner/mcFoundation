<?php
$testimonial = new Testimonial($post->ID);
$testimonialClass = array('testimonial');
$testimonialClass[] = has_post_thumbnail($testimonial->post->ID) ? 'has-logo' : '';

echo '<div class="'.implode(' ', $testimonialClass).'">';
	if( has_post_thumbnail($testimonial->post->ID) ) {
		echo '<div class="thumb">';
			echo get_the_post_thumbnail($testimonial->post->ID, 'mc_200');
		echo '</div>';
	}
	echo '<div class="contentWrap">';
		echo $testimonial->getContent();
		echo '<p class="author">'.$testimonial->getAuthor().'</p>';
	echo '</div>';
echo '</div>';