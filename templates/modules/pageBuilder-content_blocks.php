<?php
$block_grid = (int)$column['block_grid'];
echo '<ul class="contentBlocks block-grid small-block-grid-1 medium-block-grid-'.$block_grid.'">';
foreach($column['blocks'] as $block) {
	
	$link = $block['link'] ? get_permalink($block['link']) : false;
	
	$headline = false;
	if( $block['block_headline'] ) {
		$headline = '<h4 class="title">';
		$headline .= $link ? '<a href="'.$link.'">' : '';
		$headline .= $block['block_headline'];
		$headline .= $link ? '</a>' : '';
		$headline .= '</h4>';
	}
	
	$image_display = $block['image_display'] === 'false' ? false : $block['image_display'];
	$image_placement = $image_display ? $block['image_placement'] : false;
	$image = $image_display == 'image' ? wp_get_attachment_image_src($block['image'], 'mc_300') : false;
	$icon = $image_display == 'icon' ? 'fa-'.$block['icon'] : false;
	
	$imgString = '';
	$imgString .= $link ? '<a href="'.$link.'">' : '';
	$imgString .= $image ? '<img src="'.$image[0].'" class="th" />' : '<i class="fa '.$icon.'"></i>';
	$imgString .= $link ? '</a>' : '';
	
	$classes = array('contentBlock');
	$classes[] = $image_display ? 'hasImg img-'.$image_display : 'noImg';
	$classes[] = $image_placement ? 'imgPlacement-'.$image_placement : '';
	$classes[] = $headline ? 'hasHeadline' : 'noHeadline';
	$classes[] = $link ? 'isLinked' : 'noLink';
	
	echo '<li class="'.implode(' ', $classes).'">';
		echo $image_placement == 'pre' ? $imgString : '';
		echo $headline ? $headline : '';
		echo $block['content'];
		echo $image_placement == 'post' ? $imgString : '';
	echo '</li>';
}
echo '</ul>';