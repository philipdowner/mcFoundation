<?php
echo '<ul class="block-grid small-block-grid-3 medium-block-grid-4 swatches">';
foreach( $block->items as $style => $item ) {
	$color = $this->Sass->getValue($style);
	echo '<li class="'.$style.'">';
		echo '<div class="color" style="color:'.$color.'">';
		echo '<div class="swatch color-'.$style.'" style="background-color:'.$color.';"></div>';
		echo '<p class="title">'.(is_object($item) ? $item->title : $item).'</p>';
		echo '<p class="value">'.$color.'</p>';
		echo '</div>';
		
		//Variations on the color
		if( is_object($item) && $item->variations ) {
			echo '<div class="variations">';
			//do_dump($item->variations);
			if( $item->variations->range ) {
				for( $i = $item->variations->range[0]; $i <= $item->variations->range[1]; $i += $item->variations->increment) {
					$color = $this->Sass->getValue($style.$i);
					echo '<div class="swatch color-'.$style.$i.'" title="$'.$style.$i.'" style="background-color:'.$color.';"></div>';
				}
			}
			else {
				foreach( $item->variations as $name => $variation ) {
					$color = $this->Sass->getValue($name);
					do_dump($color);
					echo '<div class="swatch color-'.$name.'" title="'.$color.'" style="background-color:'.$color.';"></div>';
				}
			}
			echo '</div>';
		}
	echo '</li>';
}
echo '</ul>';
