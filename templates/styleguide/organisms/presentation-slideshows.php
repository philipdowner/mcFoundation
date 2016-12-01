<div class="row">
	<?php
	$slideshows = array(
		array(
			'name' => 'Simple Slideshow',
			'options' => array(),
			'columns' => 6,
			'numImg' => 5,
			'img' => ManifestFramework::placeholderImg(600,400,true,array('category' => 'animals'))
		),
		array(
			'name' => 'Slideshow with Dot Navigation',
			'options' => array(
				'dots' => true
			),
			'columns' => 6,
			'numImg' => 4,
			'img' => ManifestFramework::placeholderImg(600,400,true,array('category' => 'architecture'))
		),
		array(
			'name' => 'Slideshow with Thumbnails',
			'options' => array(
				'slidesToShow' => 1,
			  'slidesToScroll' => 1,
			  'asNavFor' => '#slideThumbNav',
			  'dots' => false,
			  'arrows' => false,
			  'centerMode' => false
			),
			'columns' => 6,
			'numImg' => 5,
			'img' => ManifestFramework::placeholderImg(600,400,true,array('category' => 'nature', 'filter' => 'grayscale'))
		),
		array(
			'name' => 'Grid Formatted Slideshow',
			'options' => array(
				'rows' => 2,
				'slidesPerRow' => 2,
				'dots' => true
			),
			'columns' => 6,
			'numImg' => 12,
			'img' => ManifestFramework::placeholderImg(300,200,true,array('category' => 'tech'))
		),
		array(
			'name' => 'Multiple Images Per Slide',
			'options' => array(
				'dots' => true,
				'slidesToShow' => 3,
				'slidesToScroll' => 3
			),
			'columns' => 12,
			'numImg' => 12,
			'img' => ManifestFramework::placeholderImg(400,500,true,array('category' => 'people'))
		)
	);
	foreach($slideshows as $id => $slideshow) {
		echo '<div class="small-12 medium-'.$slideshow['columns'].' columns">';
			echo '<h4 class="elementHeader">'.$slideshow['name'].'</h4>';
			echo '<div id="slickSlides-'.$id.'" class="slickSlides" data-slick=\''.json_encode($slideshow['options']).'\'>';
			for( $i = 0; $i < $slideshow['numImg']; $i++ ) {
				echo '<img src="'.$slideshow['img'].'" />';
			}
			echo '</div>';
			
			if( $slideshow['options']['asNavFor'] ) {
				$slideNavOptions = array(
					'slidesToShow' => 3,
				  'slidesToScroll' => 1,
				  'asNavFor' => '#slickSlides-'.$id,
				  'dots' => false,
				  'centerMode' => false,
				  'focusOnSelect' => false,
				  'arrows' => true
				);
				echo '<div id="'.str_replace('#', '', $slideshow['asNavFor']).'" class="slickSlides" data-slick=\''.json_encode($slideNavOptions).'\'>';
				for( $i = 0; $i < $slideshow['numImg']; $i++ ) {
					echo '<img src="'.ManifestFramework::placeholderImg(100,80,true,array('category' => 'nature')).'" />';
				}
				echo '</div>';
				
				echo '<div class="panel callout radius"><p>Due to the way our image placeholder service functions, the thumbnails in this slideshow are different than the main images. In it\'s final form the thumbnails will be the same as the main image.</p></div>';
			}
			
		echo '</div>';
	}
	?>
</div>
