<?php
	echo '<article id="post-'.get_the_ID().'" class="'.implode(' ', get_post_class()).'">';
	
		get_template_part('templates/components/headline', 'single');
		
		echo '<div class="postInfo">';
			echo '<p>Published on '.get_the_date().' at '.get_the_time().'</p>';
		echo '</div>';
		
		echo '<div class="entry-content">';
			the_content();
			
			//wp_link_pages();
			ManifestFramework::pagination();
		
		echo '</div>';//.entry-content
		
		echo '<div class="postMeta"><p>';
			the_tags();
		echo '</p></div>';
		
		comments_template();
		
	echo '</article>';