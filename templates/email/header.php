<tr>
	<td id="headerWrap" width="100%">
		<table id="header">
			<tr id="branding" class="row">
				<td class="small-12 columns">
					<a href="<?php echo get_bloginfo('url'); ?>" class="globalLogo">
						<img src="<?php echo IMG_DIR; ?>/template/logo.png" alt="<?php echo get_bloginfo('name'); ?> logo" height="130" />
					</a>
				</td>
			</tr>
			
			<?php if( $headline ): ?>
			<tr id="headline" class="row">
				<td class="small-12 columns text-center">
					<h1><?php echo $headline; ?></h1>
				</td>
			</tr>
			<?php endif; ?>
		</table><!-- #header -->
	</td><!-- #headerWrap -->
</tr>