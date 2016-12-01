<?php
echo '<div class="quoteWrap quoteBlock">';
	echo '<p class="quotationMark fa fa-stack">';
		echo '<i class="fa fa-circle fa-stack-2x"></i>';
		echo '<i class="fa fa-quote-left fa-stack-1x fa-inverse"></i>';
	echo '</p>';
	
	echo '<blockquote>';
	echo $column['quotation'];
	
	if( $column['citation'] ) {
		echo '<cite>';
		
		echo $column['citation_link'] ? '<a href="'.$column['citation_link'].'" target="_blank">' : '';
		
		echo $column['citation'];
		
		echo $column['citation_link'] ? '</a>' : '';
		
		echo '</cite>';
	}
	echo '</blockquote>';
echo '</div>';