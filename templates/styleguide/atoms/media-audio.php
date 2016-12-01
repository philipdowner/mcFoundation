<div class="row">
	<?php
	foreach ($block->items as $service => $url ) {
		echo '<div class="small-12 medium-6 columns">';
			echo apply_filters('the_content', $url);
		echo '</div>';
	}
	?>
</div>