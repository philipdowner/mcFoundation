<div class="row">
	<div class="small-12 columns">
		<p>We make helping users become oriented with your site easy and attractive. Click the button below to start an example using this very page!</p>
		<a class="button radius" id="start-jr" name="start-jr">Start My Site Tour</a>
		
		<?php
			$stops = array(
				'section-colors' => array(
					'options' => array('prev_button' => false),
					'content' => '<h4>Welcome!</h4><p>The site tour begins with colors. Your site colors are displayed here.</p>'
				),
				'section-text' => '<h4>Typography</h4><p>What gorgeous fonts shall we see?</p>',
				'section-icons' => '<h4>Iconography</h4><p>Icons provide a visual reference.</p>',
				'section-labels' => '<h4>Labels</h4><p>Labels give context to information.</p>',
				'section-media' => '<h4>Images &amp; Videos</h4><p>\'Cuz plain text gets boring!</p>',
				'section-forms' => '<h4>Forms</h4><p>Let your visitors tell you what they need.</p>',
				'start-jr' => '<h4>The End!</h4><p>Thanks for coming on our joyride!</p>'
			);
		?>
		
		<ol class="joyride-list" data-joyride data-options="tip_location:top;">
			
			<?php
			foreach( $stops as $section => $content ) {
				echo '<li data-id="'.$section.'" data-text="Next" data-prev-text="Prev"';
				if( is_array($content) ) {
					echo ' data-options="';
					foreach( $content['options'] as $k => $v ) {
						echo "$k: $v;";
					}
					echo '"';
				}
				echo '>';
				
				echo is_array($content) ? $content['content'] : $content;
				
				echo '</li>';
			}
			?>
		</ol>
	</div>
</div>