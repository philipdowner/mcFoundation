			</section><!-- .container -->
			<?php do_action('mcFoundation_before_footer'); ?>
			
			<footer id="primary">
				<?php do_action('mcFoundation_footer'); ?>
			</footer>
		<?php
		do_action('mcFoundation_after_footer');
		do_action('mcFoundation_before_body_close');
		wp_footer();
		?>
	</body>
</html>
