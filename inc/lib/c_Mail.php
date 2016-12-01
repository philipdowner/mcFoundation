<?php
/**
* MAIL
* Handles assembling a mail template and inlining CSS, returning HTML suitable for sending via e-mail.
*
* @author Philip Downer <philip@manifestbozeman.com>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @version v1.1
*/
class Mail {
	
	public $ID;
	public $message;
	public $headline;
	public $subject;
	public $templateDir;
	public $templateFiles;
	public $template;
	public $templateArgs;
	public $headers = '';
	public $attachments = '';
	
	const PREMAILER_ENDPOINT = 'http://premailer.dialect.ca/api/0.1/documents';
	
	/**
	* __construct
	*
	* @param string $id The unique message ID
	* @param string $message The HTML for the message body
	* @param string $headline The headline for the template
	* @param string $subject The message subject
	* @param string $template The name of the template to use for the body of the message.
	* @param array $templateArgs Additional args to pass to the template
	* @return void
	*/
	public function __construct($ID, $message = '', $headline = '', $subject = '', $template = '', $templateArgs = array()) {
		
		//Set the message ID
		$this->ID = $ID;
		
		//Set the basic defaults
		$this->setMessage($message);
		$this->setHeadline($headline);
		$this->setSubject($subject);
		$this->setTemplate($template);
		$this->setTemplateArgs($templateArgs);
		
		//Set some default message headers
		$this->setHeaders(
			apply_filters('mcMail_default_headers', array(
				'content-type' => 'Content-Type: text/html; charset=UTF-8',
				'from' => 'From: '.get_bloginfo('name').' <'.get_theme_option('option_company_email').'>'
			), $this->ID, $this)
		);
		
		//Set the default template directory
		$this->setTemplateDir(
			trailingslashit(apply_filters('mcMail_default_template_dir', 'templates/email/', $this->ID, $this))
		);
		
		/**
			Set the default template files and order
			Filterable. Array key order determines how the layout will be ordered
		**/
		$defaultTemplates = array(
			0 => 'header',
			50 => 'body',
			100 => 'footer'
		);
		$this->setTemplateFiles(apply_filters('mcMail_default_templates', $defaultTemplates, $this->ID, $this));
		
	}//__construct
	
	/***********************************************************************
	* !MESSAGE CONTENT
	/***********************************************************************/
	/**
	* SET HEADLINE
	*
	* @param string $headline
	*/
	public function setHeadline($headline) {
		$this->headline = $headline;
	}
	
	/**
	* GET HEADLINE
	*
	* @return string
	*/
	public function getHeadline() {
		return $this->headline;
	}
	
	/**
	* SET MESSAGE
	*
	* @param string $message
	*/
	public function setMessage($message) {
		$this->message = $message;
	}
	
	/**
	* GET MESSAGE
	*
	* @return string
	*/
	public function getMessage() {
		return $this->message;
	}
	
	/**
	* SET TEMPLATE
	*
	* @param string $template
	*/
	public function setTemplate($template) {
		$this->template = $template;
	}
	
	/**
	* GET TEMPLATE
	*
	* @return string
	*/
	public function getTemplate() {
		return $this->template;
	}
	
	/**
	* SET TEMPLATE ARGS
	*
	* @param array $args Associative array to be passed to @see extract()
	*/
	public function setTemplateArgs($args) {
		$this->templateArgs = $args;
	}
	
	/**
	* GET TEMPLATE ARGS
	*
	* @return array
	*/
	public function getTemplateArgs() {
		return $this->templateArgs;
	}
	
	/**
	* SET TEMPLATE ARG
	*
	* @param string $key Associative array key
	* @param mixed $value Value to assign to the element
	*/
	public function setTemplateArg($key, $value) {
		$args = $this->getTemplateArgs();
		$args[$key] = $value;
		$this->setTemplateArgs($args);
	}
	
	/**
	* GET TEMPLATE ARG
	*
	* @param string $key
	* @return mixed Null if the key does not exist
	*/
	public function getTemplateArg($key) {
		$args = $this->getTemplateArgs();
		return array_key_exists($key, $args) ? $args[$key] : null;
	}
	
	/**
	* SET TEMPLATE FILES
	*
	* @param array $files Array of template file names indexed according to the order they should be required in the layout.
	*/
	public function setTemplateFiles($files) {
		$this->templateFiles = $files;
	}
	
	/**
	* GET TEMPLATE FILES
	*
	* @return array
	*/
	public function getTemplateFiles() {
		ksort($this->templateFiles);
		return $this->templateFiles;
	}
	
	/**
	* SET TEMPLATE SLUG
	*
	* @param string $slug The template location relative to the theme root
	*/
	public function setTemplateDir($slug) {
		$this->templateDir = trailingslashit($slug);
	}
	
	/**
	* GET TEMPLATE SLUG
	*
	* @return string
	*/
	public function getTemplateDir() {
		return trailingslashit(apply_filters('mcMail_template_dir', $this->templateDir, $this->ID, $this));
	}
	
	/**
	* LOCATE TEMPLATE
	*
	* @param string $slug
	* @param string $name
	* @return string The template location for @see include()
	*/
	public function locateTemplate($slug, $name = '') {
		$slug = $this->getTemplateDir().$slug;
		$templates = array();
		$name = (string)$name;
		if( $name !== '' ) {
			$templates[] = $slug.'-'.$name.'.php';
		}
		$templates[] = $slug.'.php';
		
		return locate_template($templates, false, false);
	}
	
	/**
	* GET HTML
	*
	* @param bool $premailer Whether to inline styles or not
	* @return string
	*/
	public function getHTML($premailer = true) {
		
		$headline = $this->getHeadline();
		$message = $this->getMessage();

		//Make the mail object available in templates
		$mailObj = $this;
		
		if( !empty($this->templateArgs) ) {
			extract($this->templateArgs, EXTR_REFS);
		}
		
		ob_start();
		
		//GET THE HTML OPENER
		require($this->getHtmlWrapper('open'));
		
		foreach( $this->getTemplateFiles() as $templateFile ) {
			require($this->locateTemplate($templateFile, $this->getTemplate()));
		}
		
		//Get the HTML Closer
		require($this->getHtmlWrapper('close'));
		
		$html = ob_get_clean();
		
		return $premailer ? $this->premailer($html) : $html;
		
	}
	
	/**
	* GET HTML OPENER
	*
	* @param string Either 'open' or 'close'
	* @return string
	*/
	public function getHtmlWrapper($section) {
		$templates = array(
			$this->getTemplateDir().'wrapper-'.$section.'-'.$this->getTemplate().'.php',
			$this->getTemplateDir().'wrapper-'.$section.'.php'
		);
		return locate_template($templates, false, false);
	}
	
	/**
	* PREMAILER
	* Inlines styles via premailer api
	*
	* @param string $html The full HTML to send to the api
	* @return string
	*
	* @todo Consider whether the external API call is necessary or should be a fallback instead of the primary method of inlining.
	*/
	private function premailer($html) {		
		$response = wp_remote_post(self::PREMAILER_ENDPOINT, array(
			'body' => array(
				'html' => $html,
				'preserve_styles' => false,
				'remove_comments' => true
			)
		));
		
		$responseCode = wp_remote_retrieve_response_code($response);
		
		if( $responseCode == 201) {
			$response = json_decode(wp_remote_retrieve_body($response));
			$html = wp_remote_get($response->documents->html);
			return wp_remote_retrieve_body($html);
		}
		
		//Premailer didn't respond, try an internal setup
		else {
			$inliner = new TijsVerkoyen\CssToInlineStyles\CssToInlineStyles($html);
			$inliner->setUseInlineStylesBlock(true);
			$inliner->setStripOriginalStyleTags(false);
			$inliner->setExcludeMediaQueries(false);
			$html = $inliner->convert(true);
			return $html;
		}
		return '';
	}
	
	/**
	* SET SUBJECT
	*
	* @param string $subject Email subject line
	* @return
	*/
	public function setSubject($subject) {
		$this->subject = $subject;
	}
	
	/**
	* GET SUBJECT
	*
	* @return string
	*/
	public function getSubject() {
		return $this->subject;
	}
	
	/**
	* GET HEADERS
	*
	* @return array
	*/
	public function getHeaders() {
		return $this->headers;
	}
	
	/**
	* SET HEADERS
	*
	* @param array $headers Array of headers suitable for @see wp_mail
	*/
	public function setHeaders($headers) {
		$this->headers = $headers;
	}
	
	/**
	* SET HEADER
	*
	* @param string $header The header to set like "From: Philip Downer <philip@manifestbozeman.com>
	*/
	public function setHeader($header) {
		$this->headers[] = $header;
	}
	
	/**
	* SET ATTACHMENTS
	*
	* @param array $attachments
	*/
	public function setAttachments($attachments) {
		$this->attachments = $attachments;
	}
	
	/**
	* GET ATTACHMENTS
	*
	* @return array|string
	*/
	public function getAttachments() {
		return $this->attachments;
	}
	
	/**
	* ADD ATTACHMENT
	*
	* @param string $key An array key to reference the file with
	* @param string $file Absolute file URL
	*/
	public function addAttachment($key, $file) {
		$attachments = $this->getAttachments();

		//No attachments exist
		if( empty($attachments) ) {
			return $this->setAttachments(array($key, $file));
		}
		
		//Attachments exist as a string, possibly delimited with newlines
		if( !is_array($attachments) ) {
			$attachments = explode("\n", $attachments);
		}
		
		$attachments[$key] = $file;
		return $this->setAttachments($attachments);
	}
	
	/**
	* SEND
	* Sends the message using the wp_mail function
	*
	* @param string|array $to String or array of recipient email address
	* @return bool Whether the email contents were sent successfully.
	*/
	public function send($to) {
		return wp_mail($to, $this->getSubject(), $this->getHTML(), $this->getHeaders(), $this->getAttachments());
	}
	
}//Mail
?>