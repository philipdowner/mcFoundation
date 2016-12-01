<?php
foreach( $block->items as $tag => $text ) {
	echo '<'.$tag.' class="subheader">'.$text.'</'.$tag.'>';
}