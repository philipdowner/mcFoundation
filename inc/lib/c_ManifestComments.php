<?php
class ManifestComments {
	
	public $post;
	public $comments;
	public $commentsCount;
	public $commenter;
	protected $avatarSize = 128;
	protected $commentHeadline;
	
	/**
	* CONSTRUCT
	*/
	function __construct() {
		global $post, $wp_query;
		
		$this->post = $post;
		$this->comments = $wp_query->comments;
		$this->commentsCount = wp_count_comments($this->post->ID);
		$this->commenter = wp_get_current_commenter();
		
		if( !$this->commentsCount->approved ) {
			$this->setCommentHeadline('<div id="commentHeadline"><h2>No comments on this post just yet.</h2><p>You can be the first voice&hellip; Simply use the form below!</p></div>');
		}
		else {
			$this->setCommentHeadline('<div id="commentHeadline"><h2>There '._n('is', 'are', $this->commentsCount->approved).' '.$this->commentsCount->approved.' '._n('comment', 'comments', $this->commentsCount->approved).' on this post.</h2><p>You can <a href="#respond">leave your own reply</a> using the form below&hellip;</p></div>');
		}
		
		add_filter('comment_class', array($this, 'comment_class'), 10, 5);
	}
	
	public function comment_class($classes, $class, $comment_id, $comment, $post_id) {
		$classes[] = 'row';
		return $classes;
	}
	
	/**
	* COMMENTS TEMPLATE
	* Sets the path for the comments template
	*
	* @param string $file
	* @return string
	*/
	static function comments_template($file) {
		return STYLESHEETPATH . '/templates/comments.php';
	}
	
	/**
	* GET AVATAR SIZE
	*
	* @return int
	*/
	public function getAvatarSize() {
		return $this->avatarSize;
	}
	
	/**
	* SET AVATAR SIZE
	*
	* @param int $size
	*/
	public function setAvatarSize($size = 128) {
		$this->avatarSize = $size;
	}
	
	/**
	* SET COMMENT HEADLINE
	*
	* @param string $headline
	*/
	public function setCommentHeadline($headline) {
		$this->commentHeadline = $headline;
	}
	
	/**
	* GET COMMENT HEADLINE
	*
	* @return string
	*/
	public function getCommentHeadline() {
		return $this->commentHeadline;
	}
	
	/**
	* GET LIST COMMENTS ARGS
	* Passes appropriate args to wp_list_comments()
	*
	* @param array $args
	* @return array
	*/
	public function getListCommentsArgs($args = array()) {
		$defaults = array(
			'type' => 'comment',
			'per_page' => -1,
			'avatar_size' => $this->getAvatarSize(),
			'format' => 'html5',
			'style' => 'ol',
			'walker' => new Manifest_Walker_Comment
		);
		return wp_parse_args($args, $defaults);
	}
	
	/**
	* GET COMMENT FORM ARGS
	*
	* @return array
	*/
	public function getCommentFormArgs($args = array()) {
		$commenter = $this->commenter;
		$req = get_option('require_name_email');
		$aria_req = ( $req ? " aria-required='true'" : '' );
		$html_req = ( $req ? " required='required'" : '' );
		$user = wp_get_current_user();
		$user_identity = $user->exists() ? $user->display_name : '';
		
		
		$defaults = array(
			'fields' => array(
				'author' => '<p class="comment-form-author"><label for="author">Your Name'.($req ? ' <span class="required">*</span>' : '' ).'</label><input id="author" name="author" type="text" value="'.esc_attr($commenter['comment_author']) . '" size="30"'.$aria_req.$html_req.' /></p>',
				'email' => '<p class="comment-form-email"><label for="email">Email Address'.($req ? ' <span class="required">*</span>' : '' ).'</label><input id="email" name="email" type="email" value="'.esc_attr($commenter['comment_author_email']).'" size="30" aria-describedby="email-notes"' . $aria_req . $html_req  . ' /></p>',
				'url' => '<p class="comment-form-url"><label for="url">Website Address</label><input id="url" name="url" type="url" value="'.esc_attr($commenter['comment_author_url']).'" size="30" /></p>'
			),
			'comment_field' => '<p class="comment-form-comment"><label for="comment">What\'s Your Comment?</label><textarea id="comment" name="comment" cols="45" rows="8" aria-describedby="form-allowed-tags" aria-required="true" required="required"></textarea></p>',
			'class_submit' => 'button',
			'label_submit' => 'Post Your Comment',
			'comment_notes_after' => '',
			'format' => 'html5',
			'logged_in_as' => '<div class="logged-in-as alert-box info radius"><p>'.sprintf( 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>', get_edit_user_link(), $user_identity, wp_logout_url(apply_filters('the_permalink', get_permalink($post_id))) ).'</p></div>',
			'must_log_in' => '<div class="must-log-in alert-box info radius"><p>'.sprintf('You must be <a href="%s">logged in</a> to post a comment.', wp_login_url(apply_filters('the_permalink', get_permalink($post_id))) ).'</p></div>',
			'title_reply' => 'Post Your Comment'
		);
		
		return wp_parse_args($args, $defaults);
	}
	
	/**
	* SHOW COMMENTS
	*
	* @param array $args Args to pass to @see wp_list_comments
	* @param bool $showCommentForm Whether to automatically show the comment form
	* @param array $commentFormArgs Args to pass to @see comment_form
	*/
	public function showComments($args = array(), $showCommentForm = true, $commentFormArgs = array()) {
		
		if( !$this->commentsCount->approved && $this->post->comment_status != 'open' ) return;
		
		echo '<div id="comments">';
			
			//Check if a password is required
			if( post_password_required( $this->post) ) {
				echo '<div class="alert-box info radius"><p>Comments are not shown on password protected posts.</p></div>';
				echo '</div>';//#comments
				return;
			}
			
			echo $this->getCommentHeadline();
			echo '<ol class="commentList no-bullet">';
				wp_list_comments($this->getListCommentsArgs($args));
			echo '</ol>';//.commentList
		echo '</div>';//#comments
		
		if( $showCommentForm ) $this->showCommentForm($commentFormArgs);
	}
	
	/**
	* SHOW COMMENT FORM
	*/
	public function showCommentForm($args = array()) {
		
		if( !$this->commentsCount->approved && $this->post->comment_status != 'open' ) return;
		
		//Show the comment form
		echo '<div id="commentRespondWrap">';
			if( $this->post->comment_status != 'open' ) {
				echo '<div class="alert-box info radius"><p>Comments on this post are closed.</p></div>';
				echo '</div>';//#commentRespondWrap
				return;
			}
			
			comment_form($this->getCommentFormArgs($args));
		echo '</div>';//#commentRespondWrap
	}
	
}//ManifestComments

class Manifest_Walker_Comment extends Walker_Comment {
	
	/**
	 * Output a comment in the HTML5 format.
	 *
	 * @access protected
	 * @since 3.6.0
	 *
	 * @see wp_list_comments()
	 *
	 * @param object $comment Comment to display.
	 * @param int    $depth   Depth of comment.
	 * @param array  $args    An array of arguments.
	 */
	protected function html5_comment( $comment, $depth, $args ) {
		$tag = ( 'div' === $args['style'] ) ? 'div' : 'li';
		echo '<'.$tag.' id="comment-'.$comment->comment_ID.'" '.comment_class($this->has_children ? 'parent' : '', null, null, false).'>';
		
		$authorClasses = apply_filters('mcFoundation_comment_author_classes', array(
			'default' => 'commentAuthor columns',
			'small' => 'small-2'
		), $comment, $depth, $args);
		
		$contentClasses = apply_filters('mcFoundation_comment_content_classes', array(
			'default' => 'commentContent columns',
			'small' => 'small-10'
		), $comment, $depth, $args);
		
		//Show the comment author
		echo '<div class="'.implode(' ', $authorClasses).'">';
			
			//Show the avatar
			if( $args['avatar_size'] != 0 ) {
				echo $comment->comment_author_url ? '<a class="avatarLink" href="'.$comment->comment_author_url.'" target="_blank">' : '';
				echo get_avatar( $comment, $args['avatar_size'], '', 'Avatar for '.$comment->comment_author, apply_filters('mcFoundation_comment_avatar_args', array()) );
				echo $comment->comment_author_url ? '</a>' : '';
			}
			
		echo '</div>';//.commentAuthor
		
		//Show the comment content
		echo '<div class="'.implode(' ', $contentClasses).'">';
			
			echo '<div class="commentMeta">';
				edit_comment_link('Edit Comment', '<p class="editComment">', '</p>');
				echo '<h6 class="commenterName">'.get_comment_author_link().'</h6>';
				echo '<p class="commentTime"><a href="'.esc_url(get_comment_link($comment->comment_ID, $args)).'" title="Permanent link to this comment">';
					echo '<time datetime="'.get_comment_time('c').'">'.sprintf('%1$s at %2$s', get_comment_date(), get_comment_time()).'</time>';
				echo '</a></p>';
			echo '</div>';
			
			//Comment Moderation
			if( !$comment->comment_approved ) {
				echo '<div class="commentModeration alert-box info radius">';
					echo apply_filters('mcFoundation_comment_moderation_message', '<p>Your comment is awaiting moderation.</p>', $comment);
				echo '</div>';
			}
			
			echo '<div class="entry-content">';
				comment_text();
			echo '</div>';
			
			echo '<div class="reply"><p>';
				comment_reply_link(array(
					'depth' => $depth,
					'max_depth' => $args['max_depth'],
					'respond_id' => 'commentRespondWrap'
				));
			echo '</p></div>';
			
		echo '</div>';
		
//		do_dump($comment);
	}//html5_comment
	
	
}//Manifest_Walker_Comment