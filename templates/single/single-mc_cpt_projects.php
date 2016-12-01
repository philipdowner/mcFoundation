<?php
/**
* The default template for displaying page content
*/

echo ManifestFramework::heroImg(array(
	'introduction' => wpautop($post->post_excerpt)
));

echo '<article id="post-'.get_the_ID().'" class="'.implode(' ', get_post_class()).'">';

	$gallery = get_field('gallery');
	$testimonial = get_field('testimonial');

	$layoutClass = array('row', 'projectLayout');
	$layoutClass[] = $post->post_content ? 'hasPostContent' : 'noPostContent';
	$layoutClass[] = !empty($gallery) ? 'hasGallery' : 'noGallery';
	$layoutClass[] = $testimonial ? 'hasTestimonial' : 'noTestimonial';

	echo '<div class="'.implode(' ', $layoutClass).'">';
	
		if( !empty($gallery) ) {
			echo '<div class="row">';
				echo '<div class="gallery small-12 columns">';
					echo '<h4 class="sectionTitle">Project Gallery</h4>';
					
					$blockGrid = count($gallery) < 6 ? max(array(4, count($gallery))) : 6;
					echo '<ul class="small-block-grid-3 medium-block-grid-'.$blockGrid.'" data-clearing>';
						foreach( $gallery as $img ) {
							echo '<li><a href="'.$img['sizes']['mc_1200'].'"><img src="'.$img['sizes']['mc_square_200'].'" '.($img['caption'] ? 'data-caption="'.$img['caption'].'"' : '').' /></a></li>';
						}
					echo '</ul>';
					//do_dump($gallery);
				echo '</div>';
			echo '</div>';
		}
		
		if( $testimonial ) {
			echo '<div class="row">';
				echo '<div class="testimonialWrap small-12 medium-9 small-centered columns">';
					$testimonial = new Testimonial($testimonial);
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
				echo '</div>';
			echo '</div>';
		}
		
		if( $post->post_content ) {
			echo '<div class="row postContentWrap">';
				echo '<div class="postContent small-12 medium-10 medium-centered columns">';
					echo '<h4 class="sectionTitle">Project Details</h4>';
					the_content();
				echo '</div>';
			echo '</div>';
		}
	
	echo '</div>';
	
echo '</article>';
?>