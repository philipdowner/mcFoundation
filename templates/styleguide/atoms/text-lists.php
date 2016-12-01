<?php
echo '<div class="row">';
foreach( $block->items as $tag => $content) {
	echo '<div class="small-12 medium-4 columns">';
	echo '<h4 class="elementHeader">'.$content.'</h4>';
	echo "<$tag>";
	if( $tag != 'dl') {
		for($i = 0; $i < 2; $i++) {
			echo '<li>'.ManifestFramework::getLoremIpsum(1, array('short', 'plaintext')).'</li>';
		}
		echo '<li>'.ManifestFramework::getLoremIpsum(1, array('short', 'plaintext'));
			echo "<$tag>";
			for( $x = 0; $x < 3; $x++) {
				echo "<li>Nested List Item ".($x+1)."</li>";
			}
			echo "</$tag>";
		echo '</li>';
	}
	else {
		for($i = 0; $i < 3; $i++) {
			echo "<dt>Definition Title</dt>";
			echo "<dd>".ManifestFramework::getLoremIpsum(1, array('short', 'plaintext'))."</dd>";
		}
	}
	echo "</$tag>";
	echo "</div>";
}
echo '</div>';