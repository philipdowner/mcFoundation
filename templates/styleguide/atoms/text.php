<?php
foreach( $block->items as $tag => $content) {
	if( is_object($content) ) {
		echo "<$tag";
		
		//Tag attributes
		if (isset($content->attr)) {
			foreach( $content->attr as $k => $v ) {
				echo " $k=\"$v\"";
			}
		}
		
		//Tag classes
		echo isset($content->classes) ? 'class='.implode(' ', $content->classes) : '';
		
		//Close the tag
		echo empty($content) ? " />" : ">$content</$tag>";
	}
	else {
		echo "<$tag";
		echo empty($content) ? "/>" : "</$tag>";
	}
}