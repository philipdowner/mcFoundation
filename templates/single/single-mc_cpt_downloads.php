<?php
$download = new Downloads;
get_template_part('templates/components/headline', 'single');
echo '<div class="row">';

	if( has_post_thumbnail($post->ID) ) {
		echo '<div class="small-12 medium-5 thumb columns">'.get_the_post_thumbnail($post->ID, 'mc_600').'</div>';
		echo '<div class="small-12 medium-7 columns downloadForm">';
	}
	else {
		echo '<div class="small-12 columns downloadForm">';
	}
	
	$download->doAction();
	
	echo '</div>';//.downloadForm

echo '</div>';