<?php get_header(); ?>
	<?php do_action('mcFoundation_before_content_container'); ?>

			<?php
			do_action('mcFoundation_before_content');
			
			$archiveType = ManifestFramework::getArchiveType();
			
			$template = locate_template(array(
				'templates/archives/'.$archiveType[0].'-'.$archiveType[1].'.php',
				'templates/archives/'.$archiveType[0].'.php',
				'templates/archives/archive.php',
			), true, false);
			
			do_action('mcFoundation_after_content');
			?>
			
	<?php do_action('mcFoundation_after_content_container'); ?>
<?php get_footer(); ?>