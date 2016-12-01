<header id="primary" class="show-for-medium-up">
	<div class="row headerWrap">
		<div id="headerBranding" class="small-6 medium-3 large-4 columns">
			<a href="<?php echo get_bloginfo('url'); ?>" class="globalLogo">
				<?php echo ManifestFramework::Svg('/template/logo.svg'); ?>
			</a>
		</div>
		<div id="headerContacts" class="small-6 medium-9 large-8 columns">
				<p class="telephone">
					<span>Telephone:</span>
					<?php echo get_theme_option('option_telephone'); ?>
				</p>
				<?php
					echo ManifestFramework::listSocialIcons('mcSocialList', array('location' => 'header'));
				?>
		</div>
	</div><!-- .headerWrap -->
				
	<div class="headerNavWrap row">
		<div class="headerNav small-12 columns">
		<?php
			mc_primary_nav();
		?>			
		</div>
	</div>
</header>