<div class="row">
	<div class="small-12 medium-6 columns">
		<h4 class="elementHeader">Image with Caption</h4>
		<figure>
		<img src="<?php echo ManifestFramework::placeholderImg(600, 400, true); ?>" class="th" />
		<figcaption><p><?php echo ManifestFramework::getLoremIpsum(1, array('short', 'plaintext')); ?></p></figcaption>
		</figure>
	</div>
	<div class="small-12 medium-6 columns">
		<h4 class="elementHeader">Enlargeable Thumbnails</h4>
		<ul class="block-grid small-block-grid-3 medium-block-grid-5 clearing-thumbs" data-clearing>
		<?php
			for($i = 0; $i < 13; $i++) {
				$thumb = ManifestFramework::placeholderImg(100, 100, true);
				$large = ManifestFramework::placeholderImg(1000, 700, true);
				$caption = "Thumbnails and images do not match due to the nature of our image placeholder service.";
				echo '<li><a href="'.$large.'"><img src="'.$thumb.'" class="th" data-caption="'.$caption.'" /></a></li>';
			}
		?>
		</ul>
	</div>
</div>

<div class="row">
	<div class="small-12 columns">
		<h4 class="elementHeader">Custom Image Thumbnail Sizes</h4>
		<p>Sizes are relative, reduced by a factor of 10.</p>
		<?php
			global $_wp_additional_image_sizes;
			echo '<ul class="thumbSizes block-grid small-block-grid-2 medium-block-grid-5">';
			foreach( $_wp_additional_image_sizes as $name => $size) {
				echo '<li>';
					$width = $size['width']/10;
					$height = $size['height']/10;
					echo '<div class="thumbSize" style="width:'.$width.'px;height:'.$height.'px;"></div>';
					echo '<p class="thumbSizeName label radius">'.$name.'</p>';
					echo '<p><span class="size">'.$size['width'].'px by '.$size['height'].'px</span>';
					echo $size['crop'] ? '<small class="crop">(Cropped)</small>' : '';
					echo '</p>';
				echo '</li>';
			}
			echo '</ul>';
		?>
	</div>
</div>
