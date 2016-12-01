<?php
$posts = new WP_Query(array(
	'posts_per_page' => 3
));
if( !$posts->have_posts() ) return '';
?>
<tr>
	<td id="postsWrap">
		<table id="posts">
			<tr>
				<td class="small-12 columns text-center" colspan="3">
					<h3>My Recent Blog Posts</h3>
				</td>
			</tr>
			<tr>
				<?php
				while( $posts->have_posts() ) {
					$posts->the_post();
					echo '<td class="post text-center small-4 columns">';
						echo '<a href="'.get_permalink($post->ID).'">';
							the_post_thumbnail('mc_crop_200');
						echo '</a>';
						echo '<h4><a href="'.get_permalink($post->ID).'">'.get_the_title($post).'</a></h4>';
						echo wpautop($post->excerpt);
					echo '</td>';
				}
				wp_reset_query();
				?>
			</tr>
		</table>
	</td>
</tr>