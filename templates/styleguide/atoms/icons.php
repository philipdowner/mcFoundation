<?php
echo '<ul class="iconList block-grid small-block-grid-12">';

$iconSass = new Sass(STYLESHEETPATH.'/bower_components/fontawesome/scss/_variables.scss');
//do_dump($iconSass);
foreach( $iconSass->sass as $class => $var ) {
	if( strpos($class, 'fa-var-') === false ) continue;
	$class = str_replace('fa-var-', 'fa-', $class);
	echo '<li class="icon"><i class="fa fa-2x '.$class.'" title="'.$class.'"></i></li>';
}

echo '</ul>';