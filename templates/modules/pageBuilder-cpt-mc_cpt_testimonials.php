<?php
$t = new Testimonial($post);

echo '<div class="testimonial">';
	echo '<div class="content">';
		echo $t->getContent();
	echo '</div>';
	
	echo '<div class="meta">';
		echo '<span class="author">'.$t->getAuthor().'</span>';
		echo $t->hasDate() ? ' <span class="date">('.$t->getDate().')</span>' : '';
	echo '</div>';
echo '</div>';