<div id="footerLayoutWrap">
	<div id="footerLayout" class="row">
		
		<div id="footerBranding" class="small-12 medium-3 columns">
			<a href="<?php echo get_bloginfo('url'); ?>" class="footerLogo">
				<?php echo ManifestFramework::Svg('/template/logo.svg'); ?>
			</a>
		</div><!-- #footerBranding -->
		
		<div id="footerContent" class="small-12 medium-9 columns">
			<div id="footerNavRow" class="row">
				<nav id="footerNavWrap" class="small-12 columns">
					<?php get_template_part('templates/nav', 'footer'); ?>
				</nav>
			</div>
			
			<div id="footerContacts" class="row">
				<div class="contact telephone small-12 medium-6 columns">
					<p><span>Telephone</span>
					<?php echo get_theme_option('option_telephone'); ?></p>
				</div>
				
				<div class="contact email small-12 medium-6 columns">
					<p><span>Email</span>
					<?php echo '<a href="mailto:'.antispambot(get_theme_option('option_company_email'), 1).'">'.antispambot(get_theme_option('option_company_email'),0).'</a>'; ?></p>
				</div>
			</div><!-- #footerContacts -->
		</div><!-- #footerContent -->
	</div><!-- #footerLayout -->
	
	<div class="siteMeta row">
		<div class="copyright small-12 medium-4 columns">
			<p class="">&copy Copyright <?php echo ManifestFramework::getDuration(get_theme_option('config_launch_date')) ?> <?php echo get_bloginfo('name'); ?></p>
		</div>
		
		<div class="policies small-12 medium-4 columns">
			<ul class="policiesList no-bullet">
			<?php
				$policies = array(
					'privacy' => get_theme_option('config_privacy_policy'),
					'terms' => get_theme_option('config_terms')
				);
				foreach( $policies as $name => $policy ) {
					echo '<li><a href="'.get_permalink($policy).'" class="policy-'.$name.'">'.get_the_title($policy).'</a></li>';
				}
			?>
			</ul>
		</div>
			
		<div class="developer small-12 medium-4 columns">
			<p><a href="http://manifestbozeman.com" class="attribution">Bozeman, MT web design by <span class="company">Manifest Creative</span></a></p>
		</div>
	</div><!--.siteMeta -->
</div><!-- #footerLayoutWrap -->