<?php
foreach( $block->items as $tag => $text ) {
	echo '<'.$tag.'>'.$text.'</'.$tag.'>';
}