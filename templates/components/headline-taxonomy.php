<?php
global $wp_query;
$tax = get_taxonomy($wp_query->query['taxonomy']);
$term = get_term_by('slug', $wp_query->query['term'], $tax->name);
echo '<header class="entryTitle">';
	echo '<h5 class="subheader">Posts in '.$tax->labels->singular_name.'</h5>';
	echo '<h1>'.$tax->labels->singular_name.': '.$term->name.'</h1>';
echo '</header>';