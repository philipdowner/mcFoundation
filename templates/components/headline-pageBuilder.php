<?php
	echo '<header class="entryTitle">';
		echo '<h4>';
		echo '<a href="'.get_permalink().'" title="'.the_title_attribute(array(
			'before' => 'Permalink to: ',
			'after' => '',
			'echo' => false
		)).'">';
		echo get_the_title();
		echo '</a></h4>';
	echo '</header>';
?>
