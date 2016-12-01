<?php
	echo '<header class="entryTitle">';
		echo '<h3>';
		echo '<a href="'.get_permalink().'" title="'.the_title_attribute(array(
			'before' => 'Permalink to: ',
			'after' => '',
			'echo' => false
		)).'">';
		echo get_the_title();
		echo '</a></h3>';
	echo '</header>';
?>
