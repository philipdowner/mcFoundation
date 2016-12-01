<?php
$tax = explode('|', $column['term']);
$term = get_term_by('id', (int)$tax[1], $tax[0]);

$link = '<a href="'.get_term_link($term).'">'.$term->name.'</a>';
echo $this->getElement('subheadline', $link);

if( $column['show_image'] && $term_thumb = get_field('featured_image', $tax[0].'_'.$tax[1]) ) {
	
	echo '<a href="'.get_term_link($term).'">';
	echo ManifestFramework::responsiveImg($term_thumb, $this->getDefaultThumbnailSizes(), array('taxThumb'));
	echo '</a>';
}