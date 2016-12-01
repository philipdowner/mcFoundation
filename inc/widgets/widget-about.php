<?php
class About_Widget extends WP_Widget {
	
	function __construct() {
		parent::__construct(
			'about',
			'About Me',
			array(
				'description' => 'Displays About Me information'
			)
		);
	}
	
	public function widget($args, $instance) {
		echo $args['before_widget'];
		if( !empty($instance['title']) ) {
			echo $args['before_title'].apply_filters('widget_title', $instance['title']).$args['after_title'];
		}
		echo '<div id="aboutWidgetWrap" class="row">';
			echo '<div class="small-12 columns">';
				$aboutImg = get_field('option_about_me_img', 'option');
				
				if( $aboutImg ) {
//					do_dump($aboutImg);
					echo '<img src="'.$aboutImg['sizes']['mc_square_400'].'" class="aboutImg th" />';
				}
				
				echo wpautop(nl2br($instance['about_content']), false);
				
				echo '<div class="social">
					<h5>You can find me on these sites&hellip;</h5>';
					echo ManifestFramework::list_social_icons('mcSocialList', array('widgetSocial'));
				echo '</div>';
			echo '</div>';
		echo '</div>';
		echo $args['after_widget'];
	}
	
	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'About Me', 'text_domain' );
		$about_content = !empty($instance['about_content']) ? $instance['about_content'] : '';
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>"></p>

		<p>		
		<label for="<?php echo $this->get_field_id( 'about_content' ); ?>"><?php _e( 'Content:' ); ?></label><br /> 
		<textarea class="widefat" rows="10" id="<?php echo $this->get_field_id( 'about_content' ); ?>" name="<?php echo $this->get_field_name( 'about_content' ); ?>"><?php echo esc_textarea($about_content) ?></textarea>
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['about_content'] = !empty($new_instance['about_content']) ? strip_tags($new_instance['about_content']) : '';

		return $instance;
	}
}//About_Widget
add_action('widgets_init', function() {
	register_widget('About_Widget');
});