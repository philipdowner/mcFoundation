<?php
global $wp_query;
$currentTerm = $wp_query->query['term'];
$terms = get_terms($wp_query->query['taxonomy']);

if( !empty($terms) ) {
	echo '<dl class="sub-nav taxNav">
		<dt>Project Types:</dt>';
		echo '<dd><a href="'.get_permalink(1004).'">All Projects</a></dd>';
	foreach( $terms as $term ) {
		$classes = array();
		$classes[] = $term->slug == $currentTerm ? 'active' : '';
		echo '<dd class="'.implode(' ', $classes).'"><a href="'.get_term_link($term, $term->taxonomy).'">'.$term->name.'</a></dd>';
	}
	echo '</dl>';
}