<div class="row">
	<div class="small-6 columns">
		<h4 class="elementHeader">Simple Address</h4>
		<address>
		<?php
		echo get_theme_option('option_physical_address');
		?>
		</address>
		
	</div>
	
	<div class="small-6 columns">
		<h4 class="elementHeader">V-Card Address Block</h4>
		<ul class="vcard">
			<li class="fn org"><?php echo get_bloginfo('name'); ?></li>
			<li class="adr"><?php echo get_theme_option('option_physical_address'); ?></li>
			<li class="tel"><?php echo get_theme_option('option_telephone'); ?></li>
			<li class="email"><a href="mailto:<?php echo antispambot(get_theme_option('option_company_email'), 1); ?>"><?php echo antispambot(get_theme_option('option_company_email'), 0); ?></a></li>
		</ul>
	</div>
</div>