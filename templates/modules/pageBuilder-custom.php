<div class="alert-box info radius" data-alert>
	<a href="#" class="close">&times;</a>
	<p><strong>The layout module could not be found.</strong></p>
		<?php
		if( current_user_can('delete_pages') ) {
			echo '<p>Place a custom module file in your theme at <code>'.$template_names[0].'</code> to display your custom module.</p>';
		}
		?>
</div>