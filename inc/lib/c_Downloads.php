<?php
class Downloads {
	
	private $ID;
	private $accessKey;
	private $accessAllowed = false;
	private $action = 'signup';
	private $joinUrl;
	private $userType;
	private $user;
	private $metaKeys = array(
		'user' => 'mc_downloads_users',
		'anonymous' => 'mc_downloads_anonymous'
	);
	private $query;
	private $downloaded = false;
	
	/**
	* CONSTRUCTOR
	*
	* @param int $ID Post ID of the download
	* @return void
	*/
	function __construct($ID = null) {
		
		global $wp_query;
		$this->query = $wp_query->query;
		
		//Set a default action
		$this->action = isset($this->query['action']) ? $this->query['action'] : 'signup';

		//Set the download post ID
		if( !$ID ) {
			$this->ID = $wp_query->queried_object_id;
		}
		else {
			$this->ID = $ID;
		}
		
		//Set any hidden form meta
		add_filter('mcFoundation_Form_hidden_meta', array($this, 'setFormMeta'), 10, 2);
		
		//Redirect to file download
		add_action('get_header', array($this,'maybeDownload'), 1);
		
	}//__construct()
	
	/***********************************************************************
	* !MODELS
	/***********************************************************************/
	/**
	* SET ACCESS KEY
	*
	* @param string $key
	*/
	private function setAccessKey($key) {
		$this->accessKey = $key;
	}
	
	/**
	* GET ACCESS KEY
	*
	* @return string
	*/
	private function getAccessKey() {
		return $this->accessKey;
	}
	
	/**
	* Get Direct Access Key
	* Gets the md5 value of the post ID
	*
	* @return string
	*/
	private function getDirectAccessKey() {
		return md5($this->ID);
	}
	
	/**
	* SET USER TYPE
	*
	* @param string $type 'user' or 'anonymous'
	*/
	private function setUserType($type) {
		$this->userType = $type;
	}
	
	/**
	* GET USER TYPE
	*
	* @return string 'user' or 'anonymous'
	*/
	private function getUserType() {
		return $this->userType;
	}
	
	/**
	* VALIDATE ACCESS KEY
	*
	* @return bool True if the access key is valid
	*/
	private function validateAccessKey() {
		
		$accessKey = $this->getAccessKey();
		
		//Check if it's a direct access key
		$directAccessKey = $this->getDirectAccessKey();
		if( $accessKey === $directAccessKey ) {
			$this->accessAllowed = true;
			$this->setUserType('anonymous');
			return $this->accessAllowed;
		}
		
		//Check all signups for matching access keys
		$meta = get_post_meta($this->ID, $this->getUserPostMetaKey(), true);
		if( array_key_exists($this->accessKey, $meta) ) {
			$this->setUserType('user');
			$this->accessAllowed = true;	
		}
		return $this->accessAllowed;
	}
	
	/**
	* Can Download
	* Determines whether the current user can dowload the file
	*
	* @return bool True if the user has a valid access key
	*/
	private function canDownload() {
		return $this->accessAllowed;
	}
	
	/**
	* IS DOWNLOADED
	* Whether the file has been downloaded or not
	*
	* @return bool
	*/
	public function isDownloaded() {
		return $this->downloaded;
	}
	
	/**
	* SET FORM META
	* Sets the hidden form meta
	*
	* @param   
	* @return 
	*/
	public function setFormMeta($hidden, $form) {
		
		//Add "default" referrer fields
		if( isset($this->query['referrer']) ) {
			unset($_GET['referrer']);
			$hidden['referral_post_id'] = $this->query['referrer'];
			
			$referrer = get_post($this->query['referrer']);
			$hidden['referral_post_type'] = $referrer->post_type;
			$hidden['referral_post_name'] = $referrer->post_title;
			$hidden['referral_post_link'] = get_permalink($referrer->ID);
		}
		
		//Set any remaining vars
		if( isset($_GET) ) {
			foreach( $_GET as $key => $value ) {
				$hidden[$key] = $value;
			}
		}
		
		return $hidden;
	}
	
	/**
	* GET DOWNLOAD LINK
	*
	* @param int $ID The post ID of the download page
	* @param bool $append Whether to append $_GET query arguments
	* @param array $arg Additional $_GET query arguments to append to URL
	* @return string
	*/
	static function getDownloadLink($ID, $append = true, $args = array()) {
		global $wp_rewrite, $wp_query;
		$download = get_post($ID);
		
		$permalinkStruct = $wp_rewrite->extra_permastructs[get_post_type($download)]['struct'];
		
		$permalinkBase = preg_match('/^\/([^\/]+)\//', $permalinkStruct, $matches);
		if( $permalinkBase !== 1 ) return '';
		$permalinkBase = '/'.$matches[1].'/'.$download->ID;

		if( !$append ) return $permalinkBase;

		$defaultArgs = array(
			'referrer' => $wp_query->queried_object_id
		);
		
		$permalink = add_query_arg(wp_parse_args($args, $defaultArgs), $permalinkBase);
		
		return $permalink;
	}
	
	/**
	* GET USER DOWNLOAD LINK
	*
	* @param string $email Email address of user
	* @return string
	*/
	public function getUserDownloadLink($email) {
		$hash = md5($email);
		return $this->getDownloadLink($this->ID, false).'/get/'.$hash;
	}//getUserDownloadLink()
	
	/**
	* GET USER POST META KEY
	* Retrieves the post meta key used for storing user data
	*
	* @return string
	*/
	public function getUserPostMetaKey() {
		return $this->metaKeys['user'];
	}
	
	/**
	* GET ANONYMOUS POST META KEY
	* Anonymous downloads are tracked with separate post meta
	*
	* @return string
	*/
	public function getAnonymousPostMetaKey() {
		return $this->metaKeys['anonymous'];
	}
	
	/**
	* DELETE DOWNLOADERS META
	* Caution: Deletes all meta information related to downloaders for a specific post
	*/
	private function deleteDownloadersMeta() {
		return delete_post_meta($this->ID, $this->getUserPostMetaKey()) && delete_post_meta($this->ID, $this->getAnonymousPostMetaKey());
	}
	
	/**
	* GET POST META KEY BY USER TYPE
	*
	* @return string Return null if user type is not set @see self::validateAccessKey
	*/
	private function getPostMetaKeyByUserType() {
		if( !($this->userType) ) return null;
		return $this->metaKeys[$this->userType];
	}
	
	/**
	* GET USER META BY USER TYPE
	* Fetches the post meta based on whether it a user or anonymous downloader
	*
	* @return array @see get_post_meta
	*/
	private function getUserMetaByUserType() {
		return get_post_meta($this->ID, $this->getPostMetaKeyByUserType(), true);
	}
	
	/**
	* GET USER
	*
	* @return array
	*/
	public function getUser() {
		if( !$this->user ) return false;
		return $this->user;
	}
	
	/**
	* SET USER
	*
	* @param array $user User array
	* @return void
	*/
	private function setUser($user) {
		$this->user = $user;
	}
	
	/**
	* SAVE USER
	* Saves the user as post meta
	*
	* @param array $submission Sanitized, user submitted form fields
	* @return bool
	*/
	private function saveUser($submission) {
		$defaults = array(
			'signup_time' => current_time('timestamp'),
			'access_key' => md5($submission['email'])
		);
		
		//Get the current meta
		$current = maybe_unserialize(get_post_meta($this->ID, $this->getUserPostMetaKey(), true));
		
		//Create the post meta
		if( empty($current) ) {
			$new = array(
				$defaults['access_key'] => wp_parse_args($submission, $defaults)
			);
			return add_post_meta($this->ID, $this->getUserPostMetaKey(), $new, true);
		}
		
		//Create a new array
		$new = $current;
		
		//Update the existing user's post meta
		if( array_key_exists($defaults['access_key'], $current) ) {

			//If the user only subscribed, update the meta
			if( (!array_key_exists('newsletter', $current) || !$current['newsletter']) && $submission['newsletter'] ) {
				$new[$defaults['access_key']]['newsletter'] = $submission['newsletter'];
				update_post_meta($this->ID, $this->getUserPostMetaKey(), $new);
				$this->setUser($new);
				return true;
			}
			else {
				//User already exists. Leave the meta untouched
				$this->setUser($new[$defaults['access_key']]);
				return true;
			}
		}
		
		//New user - Add an array element
		$new[$defaults['access_key']] = wp_parse_args($submission, $defaults);
		$this->setUser($new[$defaults['access_key']]);
		return update_post_meta($this->ID, $this->getUserPostMetaKey(), $new);
		
		//delete_post_meta($this->ID, $this->getUserPostMetaKey());
		
	}//saveUser()
	
	/**
	* SUBSCRIBE USER
	*
	* @param array $submission Sanitized, user submitted form fields
	* @return bool
	*/
	private function subscribeUser($submission) {
		
//		require_once(INCLUDES_DIR.'/vendor/mailchimp/mailchimp/src/Mailchimp.php');
		
		$mcApiKey = 'd241cb759bc621da059854e298662fc6-us10';
		$mcListId = '47de04df47';
		
		$mc = new Mailchimp($mcApiKey);
		
		try {
			$response = $mc->lists->subscribe($mcListId, array('email' => $submission['email']), array(
				'FIRSTNAME' => $submission['first_name'],
				'LASTNAME' => $submission['last_name'],
				'EBOOKDL' => 'True',
				'LASTEBOOK' => get_the_title($this->ID)
			));
		}
		catch( Mailchimp_Error $e ) {
			return false;
		}
		return true;
	}
	
	/**
	* SEND USER EMAIL
	*
	* @param array $submission Sanitized, user submitted form fields
	* @return bool Result of wp_mail()
	*/
	private function sendUserEmail($submission) {
		
		$downloadLink = get_bloginfo('url').$this->getUserDownloadLink($submission['email']);
		
		//Compose the message
		ob_start();
		
		if( has_post_thumbnail($this->ID) ) {
			echo '<tr class="row"><td class="small-12 columns text-center">';
			 echo '<a href="'.$downloadLink.'">';
			 the_post_thumbnail('mc_400');
			 echo '</a>';
			echo '</td></tr>';
		}
		
		echo '<tr class="row"><td class="small-12 columns">';
			echo "<p>Hi {$submission['first_name']},</p>";
			echo '<p>Thanks for choosing to download my eBook <em>'.get_the_title($this->ID).'</em>. To download your copy right away, just click the link below!</p>';
			echo '<div class="panel radius text-center"><p><strong>Your download link is:</strong><br /><a href="'.$downloadLink.'">'.$downloadLink.'</a></p></div>';
			echo '<p class="text-center"><a href="'.$downloadLink.'" class="button large radius">Click to Start Your Download</a></p>';
		echo '</td></tr>';
		
		$message = ob_get_clean();
		
		$mail = new Mail('mc_Downloads', $message);
		$mail->setHeadline('Your eBook Download is Here!');
		$mail->setSubject('Download '.get_the_title($this->ID));
		$sent = $mail->send($submission['email']);
		return $sent;
	}
	
	/**
	* INCREMENT USER DOWNLOADS
	*
	* @return bool
	*/
	private function incrementUserDownloads() {
		$meta = $this->getUserMetaByUserType();
		
		if( $this->getUserType() == 'user' ) {
			$current = $meta[$this->getAccessKey()];
		}
		elseif( !is_array($current) ) {
			$current = array();
		}
		
		if( !array_key_exists('download_count', $current) ) {
			$current['download_count'] = 0;
		}
		
		$new = array_merge($current, array(
			'last_download_time' => current_time('timestamp'),
			'download_count' => $current['download_count'] + 1
		));
		
		//Combine the user meta
		if( $this->getUserType() == 'user' ) {
			$meta[$this->getAccessKey()] = $new;
			$new = $meta;
		}
		
		return update_post_meta($this->ID, $this->getPostMetaKeyByUserType(), $new);
	}
	
	/**
	* GET DOWNLOAD FILE ID
	*
	* @return int
	*/
	private function getDownloadFileID() {
		return get_post_meta($this->ID, 'download_file', true);
	}
	
	/**
	* GET DOWNLOAD FILE
	* Gets the URL of the download file
	*
	* @return string
	*/
	private function getDownloadFile() {
		return get_post($this->getDownloadFileID());
	}
	
	/**
	* GET FORCE DOWNLOAD LINK
	*
	* @return string
	*/
	public function getForceDownloadLink() {
		return $this->getDownloadLink($this->ID, false).'/get/'.$this->getAccessKey().'/?force=1';
	}
	
	/**
	* DOWNLOAD FILE
	*
	* @return bool
	*/
	private function downloadFile() {
		$filePost = $this->getDownloadFile();
		$file = get_attached_file($filePost->ID);
		$url = $post->guid;
		header('Pragma: public'); 	// required
		header('Expires: 0');		// no cache
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($file)).' GMT');
		header('Cache-Control: private',false);
		header('Content-Type: '.$filePost->post_mime_type);
		header('Content-Disposition: attachment; filename="'.basename($file).'"');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: '.filesize($file));	// provide file size
		header('Connection: close');
		readfile($file); // push it out
		exit();
	}
	
	/***********************************************************************
	* !VIEWS
	/***********************************************************************/
	/**
	* Download Form
	* Show the download form.
	*
	* @return obj Form object
	*/
	public function getSignupForm() {
		
		$form = new Form('', array(
			'formID' => 'ebook-'.$this->ID,
			'action' => $this->joinUrl,
			'submit_text' => 'Get Your Download Link',
//			'handler' => array(
//				array($this, 'submissionHandler')
//			),
			'required' => array(
				'first_name',
				'last_name',
				'email'
			),
			'fieldValidation' => array(
				'email' => array(
					'callbacks' => array('is_email'),
					'error' => 'That is not a valid email address.'
				)
			),
			'fieldSanitization' => array(
				'first_name' => array('sanitize_text_field'),
				'last_name' => array('sanitize_text_field'),
				'email' => array('sanitize_email'),
				'newsletter' => array(
					'boolval',
					array(
						array('ManifestFramework', 'acceptableValues'),
						'options' => array(
							'accepted' => array(0,1),
							'default' => 1
						)
					)
				)
			)
		));
		
		ob_start();
		
		echo '<div class="row">';
			echo '<label for="first_name">Your Name <span class="required">*</span></label>';
			echo '<div class="small-6 columns">';
				echo '<input type="text" name="first_name" value="'.$form->getSanitizedValue('first_name').'" />';
				echo '<small class="description">First</small>';
			echo '</div>';
			
			echo '<div class="small-6 columns">';
				echo '<input type="text" name="last_name" value="'.$form->getSanitizedValue('last_name').'" />';
				echo '<small class="description">Last</small>';
			echo '</div>';
		echo '</div>';
		
		echo '<div class="row">';
			echo '<div class="small-12 columns">';
				echo '<label for="email">Your Email Address <span class="required">*</span></label>';
				echo '<input type="email" name="email" value="'.$form->getSanitizedValue('email').'" />';
				echo '<small class="description">We will send your e-book download link to this address.</small>';
			echo '</div>';
		echo '</div>';
		
		echo '<div class="row">';
			echo '<div class="small-12 columns">';
				echo '<input type="checkbox" id="newsletter" name="newsletter" '.checked(boolval($form->getSanitizedValue('newsletter')), $form->isSubmitted() ? true : false, false).' />';
				echo '<label for="newsletter"><strong>Yes</strong>, please add me to your next email newsletter</label>';
			echo '</div>';
		echo '</div>';
		
		$form->setContent(ob_get_clean(), false);
		
		return $form;
		
	}//downloadForm()
	
	private function signupSuccessMessage($submission) {
		echo '<div class="alert-box success radius">';
		echo "<p><strong>Thank you for your submission, {$submission['first_name']}!</strong></p><p>Your download link has been e-mailed to: {$submission['email']}.</p>";
		echo '</div>';
	}
	
	/***********************************************************************
	* !CONTROLLERS
	/***********************************************************************/
	public function getAction() {
		return $this->action;
	}
	
	public function doAction() {
		//Switch thru the supported actions
		switch( $this->getAction() ) {
			case 'join':
				$this->doJoin();
				break;
				
			case 'get':
				$this->doGet();
				break;
				
			default:
				$this->doSignup();
				break;
		}
	}//doAction
	
	/**
	* Do Signup
	* Displays the signup form
	*
	* @return void
	*/
	private function doSignup() {
		
		//Set the action link
		$this->joinUrl = self::getDownloadLink($this->ID, false).'/join/';
		
		echo '<h4>Synopsis</h4>';
		echo wpautop(get_field('download_synopsis', $this->ID));
		
		//Get the download form
		$form = $this->getSignupForm();
		
		//Show the signup form
		if( !$form->isSubmitted() || $form->formHasErrors() ) {
			echo $form->show();
		}
		
		return true;
	}//doSignup()
	
	/**
	* Do Join
	* Handles the form submission
	*
	* @param obj $form Form object
	* @return bool True if the form validated and all logic completed
	*/
	private function doJoin() {

		//Page was accessed directly
		if( !$_POST ) {
			$this->doSignup();
			return false;
		}
		
		$this->query['referrer'] = isset($_POST['referral_post_id']) && is_numeric($_POST['referral_post_id']) ? sanitize_text_field($_POST['referral_post_id']) : null;
		
		$form = $this->getSignupForm();
		
		if( $form->formHasErrors() ) {
			$this->doSignup();
			return false;
		}
		
		$submission = $form->getSubmission();
		//do_dump($submission);
		
		//Save the user as post_meta
		if( $this->saveUser($submission) ) {

			//Subscribe the user
			if( $submission['newsletter'] ) {
				$this->subscribeUser($submission);
			}
			
			//Send the user an email
			if( $this->sendUserEmail($submission) ) {
				//Show a success message
				$this->signupSuccessMessage($submission);
				return true;	
			}
		}

		return false;
	}//doJoin()
	
	private function doGet($force = false) {
		
		if( !array_key_exists('accesskey', $this->query) || empty($this->query['accesskey']) ) {
				return false;
		}
		
		//User is authorized to download
		if( $this->canDownload() ) {
			if( !$force ) {
				echo '<div class="alert-box info radius"><h4>I\'ve queued up your download!</h4><p>If your download doesn\'t start automatically, you can <a href="'.$this->getForceDownloadLink().'">click here to force it to download.</a></p></div>';
			}
			else {
				$this->incrementUserDownloads();
				$this->downloadFile();
			}
			return true;
		}
		
		//User is not authorized
		else {
			
			//Return a false response
			if( $force ) return false;
			
			echo '<div class="alert-box warning radius">';
				echo '<p><strong>It doesn\'t look like you have access to this file.</strong> <a href="'.$this->getDownloadLink($this->ID).'">Sign up and receive a download link &raquo;</a></p>';
			echo '</div>';
		}
		
		return false;
	}//doGet()
	
	/**
	* MAYBE DOWNLOAD
	* Called via the wp_enqueue_scripts hook, performs all validation of access keys. If keys are valid, redirects to download file.
	*/
	public function maybeDownload() {
		if( $this->action !== 'get' ) return;
		
		//Set the access key
		$this->setAccessKey($this->query['accesskey']);
		
		//Validate the Access Key
		$this->validateAccessKey();
		
		if( $this->canDownload() ) {
			if( isset($this->query['force']) && $this->query['force'] == 1 ) {
				$this->downloaded = $this->doGet(true);
			}
			else {
				add_action('wp_footer', array($this, 'redirectToDownload'));
			}
			return true;
		}	
		return false;
	}//maybeDownload()
	
	/**
	* Redirect To Download
	* Called via the wp_footer hook, inserts a short JS that redirects to the downloadable file.
	*/
	public function redirectToDownload() {
		?>
		<script type="text/javascript">
			jQuery(window).load(function($) {
				location.href = '<?php echo $this->getForceDownloadLink(); ?>';
			});
		</script>
		<?php
	}//redirectToDownload()
	
}//Downloads