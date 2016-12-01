<?php
	echo '<div class="row">
		<div class="small-12 columns">';
	get_template_part('templates/components/headline', 'single');
	echo '</div>
	</div>';
?>

<?php
require_once(INCLUDES_DIR.'/lib/c_Sass.php');
require_once(INCLUDES_DIR.'/lib/c_StyleGuide.php');

$sg = new StyleGuide();
?>

<div id="breakpoints">
	<h5>Breakpoint ranges</h5>
	<div class="small-breakpoint breakpoint">Small - <?php echo $sg->Sass->getValue('small-breakpoint'); ?><span class="min"></span></div>
	<div class="medium-breakpoint breakpoint">Medium - <?php echo $sg->Sass->getValue('medium-breakpoint'); ?><span class="min"></span></div>
	<div class="large-breakpoint breakpoint">Large - <?php echo $sg->Sass->getValue('large-breakpoint'); ?><span class="min"></span></div>
	<div class="xlarge-breakpoint breakpoint">Xlarge - <?php echo $sg->Sass->getValue('xlarge-breakpoint'); ?><span class="min"></span></div>
	<div class="xxlarge-breakpoint breakpoint">XXlarge - <?php echo $sg->Sass->getValue('xxlarge-breakpoint'); ?><span class="min"></span></div>
</div>
