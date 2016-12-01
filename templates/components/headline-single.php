<?php
	global $post;
	echo '<header class="entryTitle singleTitle '.$post->post_type.'Title">';
		echo '<h1>';
		echo manifest_alternate_title();
		echo '</h1>';
	echo '</header>';
?>
