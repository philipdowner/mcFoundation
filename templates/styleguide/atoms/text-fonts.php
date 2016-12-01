<?php
foreach( $block->items as $font => $item ):
?>
	<div class="row fontFamily <?php echo $font; ?>" style='font-family:<?php echo $this->Sass->getValue($font); ?>;'>
	<div class="small-12 medium-5 columns samples">
		<div class="letter">Aa</div>
		<p class="source">
			<?php
				echo $item->font;
				echo $item->foundry ? ' | '.$item->foundry : '';
			?>
		</p>
		
		<p class="ranges">
		<?php
		$ranges = array(
			range('A', 'Z'),
			range('a', 'z'),
			range(0, 9),
			
		);
		foreach( $ranges as $range ) {
			echo '<span class="range">';
				foreach( $range as $char ) {
					echo $char;
				}
			echo '</span>';
		}
		echo '<span class="range">!@#$%^&*()-+=/|{}</span>';
		?>	
		
		</p>
		
		<p class="variants">
			<span class="bold"><strong>Bold Text is Bold</strong></span>
			<span class="em"><em>Italics Text is Emphasized</em></span>
			<span class="underline"><u>Underlined Text is Underlined</u></span>
			<span class="strike"><strike>This Text is Struck Through</strike></span>
			<span class="caps">All Capitalized Text</span>
			<span class="smallcaps">Text Set in Small Caps</span>
			<span class="link"><a href="#">Hyperlink Goes Nowhere</a></span>
			<span class="kbd">Press and hold <kbd>cmd + shift + w + p</kbd></span>
		</p>
	</div>
	
	<div class="small-12 medium-7 columns layouts">
		<h4 class="elementHeader">
			Paragraphs
		</h4>
		<div class="typeset">
			<?php echo ManifestFramework::getLoremIpsum(); ?>
		</div>
		
		<h4 class="elementHeader">
			Contextual
		</h4>
		<div class="typeset aside row">
			<div class="small-12 medium-6 columns"><?php echo ManifestFramework::getLoremIpsum(1); ?></div>
			<div class="small-12 medium-6 columns"><?php echo ManifestFramework::getLoremIpsum(2, array('short')); ?></div>
		</div>
	</div>
</div>
<hr />
<?php
endforeach;
