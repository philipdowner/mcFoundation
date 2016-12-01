<?php
/**
* Shown when no posts exist
*
*/
echo '<div class="content-none">';
	echo '<div class="entry-content">';
	
		echo '<h4>No items were found&hellip;</h4>';
	
		if( is_home() && current_user_can('publish_posts') ) {
			echo '<p>Ready to publish your first post? <a href="'.admin_url('post-new.php').'">Get started here&hellip;</a></p>';
		}
		elseif( is_search() ) {
			echo apply_filters('mcFoundation_no_posts_message', '<p>Sorry, but nothing matched your search terms. Please try again with some different keywords.</p>');
		}
		else {
			echo apply_filters('mcFoundation_no_posts_message', '<p>It seems we can\'t find what you\'re looking for.</p>');
		}
	
	echo '</div>';//.entry-content
echo '</div>';
?>