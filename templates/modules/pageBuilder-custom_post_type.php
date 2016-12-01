<?php
$post = $column['post'];
$options = $column['options'];
if( is_array($column['options']) ) {
	extract($column['options']);
}

$templateSlug = PageBuilder::getBuilderTemplateSlug().'-cpt';
$template = locate_template(array(
	$templateSlug.'-'.$post->post_type.'.php',
	$templateSlug.'.php'
), false);

echo '<div class="customPostTypeWrap postType-'.$post->post_type.'">';

if( isset($show_title) ) {
	echo '<h4 class="entryTitle"><a href="'.get_permalink($post->ID).'">'.apply_filters('the_title', $post->post_title).'</a></h4>';
}

if($template) {
	require($template);
}
echo '</div>';