<?php
echo '<div class="row">';
foreach( $block->items as $themeName => $theme ) {
	echo '<div class="small-6 medium-3 columns">';
	echo '<h4 class="elementHeader">'.$theme->title.'</h4>';
	echo '<ul id="'.$themeName.'" class="iconList block-grid small-block-grid-5 showColors">';
		foreach( $theme->icons as $class => $icon ) {
			echo '<li class="icon '.$class.'"><i class="fa fa-2x fa-'.$icon.'"></i></li>';
		} 
	echo '</ul>';
	echo '</div>';
}
echo '</div>';
echo '<div class="panel"><p class="text-center"><i class="fa fa-external-link"></i> To view the full icon library visit <a href="http://fontawesome.io/icons/" target="_blank">fontawesome.io</a>.</p></div>';