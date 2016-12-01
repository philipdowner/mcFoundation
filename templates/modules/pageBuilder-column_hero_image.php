<?php
	//do_dump($column);
	
	//Background image sizes
	$imgSizes = wp_parse_args(array(
		'small' => 'mc_wide_800',
		'medium' => 'mc_wide_1000',
		'large' => 'mc_wide_1200',
		'xlarge' => 'mc_wide_2000',
		'default' => 'mc_wide_2000'
	), ManifestFramework::getResponsiveImageSizes(null, false));
	
	$heroImgs = $column['background_images'];

	//Module options
	$options = json_encode(array(
		'type' => count($heroImgs) > 1 ? 'slideshow' : 'single',
		'location' => $column['overlay_location'],
		'header_adjust' => $column['header_adjust']
	));
	
	//do_dump($heroImgs);
	//do_dump(json_decode($options));
	
	echo '<div class="heroWrap" data-herobg=\''.$options.'\'>';
		echo '<div class="heroSlideshow">';
			foreach( $heroImgs as $k => $image ) {
				
				echo '<div class="slide" data-slideindex="'.$k.'">';
					//do_dump($image);
					//echo '<img src="'.$image['sizes']['mc_wide_2000'].'" alt="" />';
					echo ManifestFramework::responsiveImg($image['ID'], $imgSizes);
				echo '</div>';
			}
		echo '</div>';//.heroSlideshow
		
		$bg = $column['transparency_overlay'] ? $column['transparency_overlay'] : 'transparent';
		echo '<div class="overlay" style="background-color:'.$bg.';"></div>';
		
		echo '<div class="contentWrap location-'.$column['overlay_location'].'">';
			
			$contentClasses = apply_filters('mcFoundation_builder_hero_content_class', array('default' => 'heroContent'), $column, $this);
			
			echo '<div class="'.implode(' ', $contentClasses).'">';
				$content = '';
			
				if( $column['hero_headline'] ) {
					$content .= '<h1 class="heroHeadline">'.$column['hero_headline'].'</h1>';
				}
				
				$content .= apply_filters('the_content', ManifestFramework::unautop($column['introduction']));
				
				//Show the call to action
				if( $column['button_cta'] ) {
					$content .= '<p><a href="'.$column['button_cta_link'].'" class="button cta radius">'.$column['button_cta_text'].'</a></p>';
				}
				
				echo apply_filters('mcFoundation_builder_hero_content', $content, $column, $this);
			echo '</div>';
		echo '</div>';//.contentWrap
	echo '</div>';//.heroWrap