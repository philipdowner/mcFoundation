<?php
/**
* FORM HANDLER
* Assists with building the form HTML so that validation, messaging and handlers can be attached.
*
* @author Philip Downer <philip@manifestbozeman.com>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @version v1.1
*
* @todo Better documentation of the array structure for defaults
* @todo Consider benefits vs. complexity of dynamic construction of form fields
* @todo Improve submission handlers to include bool result from each in obj
*/

class Form {
	
	private $settings;
	private $content;
	private $submitted = false;
	private $validates = false;
	private $submission = array(); //Submitted fields
	private $errors = array();//Validation errors
	public $post;
	public $requiredMarker = '<span class="required">*</span>';
	
	/**
	* __construct
	*
	* @param array $args Form arguments
	*/
	public function __construct($content = '', $args = array()) {
		
		global $post;
		$this->post = $post;
		
		$defaults = array(
			'formID' => 'form-'.$post->post_slug,
			'method' => 'POST',
			'action' => get_permalink($post->ID),
			'include_hidden_meta' => true,
			'submit_text' => 'Submit',
			'handler' => array(),
			'required' => array(),
			'fieldSanitization' => array(),
			'fieldValidation' => array()
		);
		$this->settings = wp_parse_args($args, $defaults);
		$this->setContent($content, false);
		
		//Determine if the form has been submitted
		if( array_key_exists('submit_'.$this->getSetting('formID'), $_REQUEST) ) {
			
			$this->submitted = true;
			
			//Add any validation/sanitization for hidden fields
			if( $this->getSetting('include_hidden_meta') ) {
				$this->addHiddenMetaValidation();
			}
			
			//Get sanitized submission
			$this->submission = $this->getSanitizedSubmission();
			
			//Validate the submission
			$this->validateSubmission();
			
			//Get the form handlers for further processing
			if( !$this->formHasErrors() ) {
				$handlers = $this->getSetting('handler');
				
				foreach( $handlers as $handler ) {
					call_user_func($handler, $this);
				}
			}
		}//submitted
	}//__construct()
	
	/*************************
	* GETTERS & SETTERS
	**************************/
	/**
	* Get Required Marker
	*
	* @return string
	*/
	public function getRequiredMarker() {
		return $this->requiredMarker;
	}
	
	/**
	* SET REQUIRED MARKER
	*
	* @param string $marker
	* @return void
	*/
	public function setRequiredMarker($marker) {
		$this->requiredMarker = $marker;
	}
	
	/**
	* GET SETTINGS
	*
	* @return array
	*/
	public function getSettings() {
		return $this->settings;
	}
	
	/**
	* GET SETTING
	*
	* @param string $setting The setting key
	* @return mixed Null if the setting does not exist
	*/
	public function getSetting($setting) {
		$settings = $this->getSettings();
		if( !array_key_exists($setting, $settings) ) return null;
		return $settings[$setting];
	}
	
	/**
	* SET SETTING
	*
	* @param string $setting The setting key
	* @param mixed $value The setting value
	* @return void
	*/
	public function setSetting($setting, $value) {
		$this->settings[$setting] = $value;
	}
	
	/**
	* GET HIDDEN META
	* Additional hidden inputs, mostly for form tracking
	*
	* @return string
	*/
	private function getHiddenMeta() {
		if( !$this->getSetting('include_hidden_meta') ) return '';
		
		$hiddenDefaults = array(
			'referral_post_id' => $this->post->ID,
			'referral_post_type' => get_post_type($this->post),
			'referral_post_name' => $this->post->post_title,
			'referral_post_link' => get_permalink($this->post->ID)
		);
		
		if( is_user_logged_in() ) {
			$hidden['submission_user_id'] = get_current_user_id();
		}
		
		return apply_filters('mcFoundation_Form_hidden_meta', $hiddenDefaults, $this);
	}//getHiddenMeta()
	
	/**
	* ADD HIDDEN META VALIDATION
	* Adds appropriate sanitization and validation for hidden inputs
	*
	* @return void
	*/
	private function addHiddenMetaValidation() {
		$hidden = $this->getHiddenMeta();
		
		//Add sanitization and validation for each known field
		foreach( $hidden as $key => $value ) {
			
			//Each hidden field is always required
			$this->addRequiredField($key);
			
			//Handle special types. All others automatically handled by @see $this->getSanitizedValue()	
			if( strpos($key, 'link') !== false ) {
				$this->addFieldSanitization($key, 'esc_url');
			}
			
			if( strpos($key, 'id') !== false ) {
				$this->addFieldValidation($key, array(
					'callbacks' => array('is_numeric'),
					'error' => "Malicious submission detected."
				));
			}				
		}
	}//hiddenMetaValidation()
	
	/**
	* GET SUBMIT BUTTON
	*
	* @return string
	*/
	private function getSubmitButton() {
		$classes = apply_filters('mcFoundation_Form_getSubmitButton_class', array(
			'button',
			'radius'
		), $this);
		
		$button = '<div class="row">';
		$button .= '<div class="small-12 columns">';
		$button .= '<input type="submit" name="submit_'.$this->getSetting('formID').'" value="'.$this->getSetting('submit_text').'" class="'.implode(' ', $classes).'" />';
		$button .= '</div>';
		$button .= '</div>';
		
		return apply_filters('mcFoundation_Form_getSubmitButton', $button, $this);
	}
	
	/**
	* GET CONTENT
	*
	* @return string
	*/
	private function getContent() {
		$content = $this->content;
		
		if( $this->getSetting('include_hidden_meta') ) {
			$hidden = $this->getHiddenMeta();
			foreach( $hidden as $name => $value ) {
				$content .= '<input type="hidden" name="'.$name.'" value="'.$value.'" />';
			}
		}
		$content .= $this->getSubmitButton();
		return $content;
	}
	
	/**
	* SET CONTENT
	*
	* @param string $content The content to set
	* @param bool $append Whether to append the content (true) or replace it
	* @return void
	*/
	public function setContent($content, $append = true) {
		$this->content = $append ? $this->content.$content : $content;
	}
	
	/**
	* GET SUBMISSION
	*
	* @return array
	*/
	public function getSubmission() {
		if( !$this->isSubmitted() ) return null;
		return $this->submission;
	}
	
	/**
	* Reset Submission
	*
	* @return void
	*/
	public function resetSubmission() {
		$method = $this->getSetting('method');
		$globals = strtolower($method) == 'post' ? $_POST : $_GET;
		unset($globals);
		$this->submitted = false;
		$this->submission = array();
		$this->validates = false;
	}
	
	/*************************
	* CONDITIONALS
	**************************/
	/**
	* IS SUBMITTED
	*
	* @return bool
	*/
	public function isSubmitted() {
		return $this->submitted;
	}
	
	/*************************
	* ERRORS
	**************************/
	/**
	* GET ERRORS
	*
	* @return array
	*/
	public function getErrors() {
		return $this->errors;
	}
	/**
	* FORM HAS ERRORS
	*
	* @return bool
	*/
	public function formHasErrors() {
		$errors = $this->getErrors();
		return empty($errors) ? false : true;
	}
	
	/**
	* ADD ERROR
	*
	* @param string $key Field key
	* @param string $msg Error message
	* @return void
	*/
	private function addError($key, $msg) {
		$this->errors[$key] = $msg;
	}
	
	/**
	* FIELD HAS ERROR
	*
	* @param string $key Field key
	* @return bool
	*/
	public function fieldHasError($key) {
		return array_key_exists($key, $this->getErrors());
	}
	
	/**
	* GET FIELD ERROR MESSAGE
	*
	* @param string $key Field key
	* @return string
	*/
	public function getFieldError($key) {
		if( !$this->fieldHasError($key) ) return '';
		$errors = $this->getErrors();
		return $errors[$key];
	}
	
	/**
	* SHOW ERRORS
	*
	* @return string
	*/
	private function showErrors() {
		if( !$this->formHasErrors() ) return '';
		
		$errors = $this->getErrors();
		$fields = array_keys($errors);
		ob_start();
		
		?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				
				var errors = <?php echo json_encode($errors); ?>;
				var errorFields = <?php echo json_encode($fields); ?>;
				
				//console.log(errors);
				
				//Add error class
				$.each(errors, function(i, el) {
					//console.log(i, el);
					$('[for="'+i+'"], [name="'+i+'"]').addClass('error');
					$('[name="'+i+'"]').after('<span class="error">'+el+'</span>');
				});
				
			});
		</script>
		<?php
		
		echo '<div class="alert-box alert radius" data-alert tabindex="0" aria-live="assertive" role="dialogalert">There were some problems with your submission. Please review and re-submit. <button href="#" tabindex="0" class="close" aria-label="Close Alert">&times;</button></div>';
		
		return ob_get_clean();
	}
	
	/*************************
	* SANITIZATION
	**************************/
	/**
	* GET SANITIZED SUBMISSION
	* Fetches either $_GET or $_POST, then performs sanitization.
	*
	* @return array
	*/
	public function getSanitizedSubmission() {
		$method = $this->getSetting('method');
		$fields = strtolower($method) == 'post' ? $_POST : $_GET;
		
		//Remove the submit button
		unset($fields['submit_'.$this->getSetting('formID')]);
		
		//Check whether all the sanitization requested fields are present
		$sanitizeFields = $this->getSetting('fieldSanitization');
		foreach( $sanitizeFields as $key => $callback ) {
			if( !array_key_exists($key, $fields)) {
				$fields[$key] = '';
			}
		}
		
		//Sanitize the fields
		foreach( $fields as $key => $value ) {
			$fields[$key] = $this->getSanitizedValue($key, $fields);
		}
		
		return $fields;
	}//getSanitizedSubmission()
	
	/**
	* SET FIELD SANITIZATION
	* Unless set explicitly every value will be run through @see sanitize_text_field()
	*
	* @param array $sanitizations Array of callback functions for sanitization
	* @return void
	*/
	public function setFieldSanitization($sanitizations) {
		$this->setSetting('fieldSanitization', $sanitizations);
	}
	
	/**
	* ADD FIELD SANITIZATION
	* Adds a sanitization callback function for a field
	*
	* @param string $field Field name
	* @param string|array $callable Name of callable function
	* @return void
	*/
	public function addFieldSanitization($field, $callable) {
		$sanitizations = $this->getSetting('fieldSanitization');
		
		//No sanitizations exist yet
		if( !array_key_exists($field, $sanitizations) ) {
			$sanitizations[$field] = is_array($callable) ? $callable : array($callable);
		}
		
		//Append the sanitizations
		else {
			if( is_array($callable) ) {
				$sanitizations[$field] = array_merge($sanitizations[$field], $callable);
			}
			else {
				$sanitizations[$field] = array_merge($sanitizations[$field], array($callable));
			}
		}
		
		$this->setFieldSanitization($sanitizations);
	}//addFieldSanitization()
	
	/**
	* GET SANITIZED VALUE
	*
	* @param string $key The field key
	* @return mixed
	*/
	public function getSanitizedValue($key, $fields = null) {
		
		if( !$this->isSubmitted() ) return;
		
		$fields = $fields ? $fields : $this->getSubmission();
		
		$sanitizations = $this->getSetting('fieldSanitization');
		
		//Make sure the key exists
		if( !array_key_exists($key, $fields) ) return $fields[$key];
		
		//Automatically apply sanitize_text_field
		if( !array_key_exists($key, $sanitizations) ) {
			return sanitize_text_field($fields[$key]);
		}
		
		//Apply custom sanitization callbacks
		else {
			$callbacks = $sanitizations[$key];
			$field = $fields[$key];
			foreach( $callbacks as $callback ) {
				
				if( is_array($callback) && array_key_exists('options', $callback) ) {
					$callback['options'][] = $field;
					$field = call_user_func_array($callback[0], $callback['options']);
					
				}
				else {
					$field = call_user_func($callback, $field);
				}
				
			}
		}
		return $field;
	}
	
	/**
	* GET SUBMITTED VALUE
	* Sanitizes a submitted value, then applies any callback functions, so that it can be displayed or utilized. Uses @see Form::getSanitizedValue()
	*
	* @param string $key The $_REQUEST array key
	* @param string|array $callbacks User callback functions to apply
	* @return string
	*/
	public function getSubmittedValue($key, $callbacks = array() ) {
		
		$value = $this->getSanitizedValue($key);
		
		//Create an array of callbacks
		if( is_string($callbacks) ) {
			$callbacks = array($callbacks);
		}
		
		//Loop thru each callback
		foreach( $callbacks as $callback ) {
			$value = call_user_func($callback, $value, $this);
		}
		
		return apply_filters('mcFoundation_Form_getSubmittedValue_'.$key, $value, $this);
	}
	
	/*************************
	* VALIDATION
	**************************/
	/**
	* SET REQUIRED FIELDS
	*
	* @param array $required Array with field names for required
	* @return void
	*/
	public function setRequiredFields($required) {
		$this->setSetting('required', $required);
	}
	
	/**
	* ADD REQUIRED FIELD
	*
	* @param string $field Name of the field
	* @return void
	*/
	public function addRequiredField($field) {
		$required = $this->getSetting('required');
		if( !in_array($field, $required) ) {
			$required[] = $field;
			$this->setRequiredFields($required);
		}
	}
	
	/**
	* GET REQUIRED FIELDS
	*
	* @return array
	*/
	public function getRequiredFields() {
		return $this->getSetting('required');
	}
	
	/**
	* HAS ALL REQUIRED FIELDS
	*
	* @return bool
	*/
	public function hasAllRequiredFields() {
		$submission = $this->getSubmission();
		$required = $this->getRequiredFields();
		$errors = array();
		foreach( $required as $key ) {
			if( !array_key_exists($key, $submission) || empty($submission[$key]) || $submission[$key] == '' ) {
				$errors[$key] = 'Field is required.';
			}
		}
		
		if( empty($errors) ) return true;
		
		//Add an error for each field
		foreach( $errors as $key => $msg ) {
			$this->addError($key, $msg);
		}
		
		return false;
	}
	
	/**
	* SET FIELD VALIDATION
	* Set additional validation for specific fields
	*
	* @param array $validation Array of callback functions. Each function must return (bool)true if the field validates.
	* @return void
	*/
	public function setFieldValidation($validation) {
		$this->setSetting('fieldValidation', $validation);
	}
	
	/**
	* ADD FIELD VALIDATION
	* Adds a validation callback function for a field
	*
	* @param string $field Field name
	* @param array $options
	*   ~ 'callbacks' => Array of callable function names
	*   ~ 'error' => (optional) Error message
	* @return void
	*/
	public function addFieldValidation($field, $options) {
		$validations = $this->getSetting('fieldValidation');
		
		//No validations exist for the field yet
		if( !array_key_exists($field, $validations) ) {
			$validations[$field] = $options;
		}
		
		//Append the validations
		else {
			foreach( $options['callable'] as $callable ) {
				$validations[$field]['callbacks'][] = $callable;
			}
			
			if( array_key_exists('error', $options) && !empty($options['error']) ) {
				$validations[$field]['error'] = $options['error'];
			}
		}
		
		$this->setFieldValidation($validations);
	}//addFieldValidation()
	
	/**
	* VALIDATE SUBMISSION
	*
	* @return void
	*/
	private function validateSubmission() {
		
		$fields = $this->getSubmission();
		$required = $this->hasAllRequiredFields();
		
		//If all fields are not filled out, no point in validating further
		if( $this->formHasErrors() ) return;
		
		//Loop thru all validations
		$validations = $this->getSetting('fieldValidation');
		//do_dump($validations);
		
		//If there are no validations, we are OK!
		if( empty($validations) ) {
			$this->validates = true;
			return;
		}
		
		//Loop thru each validation
		$errors = array();
		foreach( $validations as $key => $validation ) {
			$value = $fields[$key];
			$callbacks = $validation['callbacks'];
			$errorMsg = $validation['error'];
			foreach($callbacks as $function) {
				$validates = call_user_func($function, $value);
				if( !$validates ) $errors[$key] = $errorMsg;
			}
		}
		
		if( !empty($errors) ) {
			foreach( $errors as $key => $msg ) {
				$this->addError($key, $msg);
			}
		}
		
		//Return
		$this->validates = true;
	}//validateSubmission
	
	/*************************
	* DISPLAY
	**************************/
	/**
	* SHOW
	*
	* @return string
	*/
	public function show() {
		
		ob_start();
		
		echo '<div id="'.$this->getSetting('formID').'_wrapper">';
			
			echo $this->showErrors();
			
			echo '<form id="'.$this->getSetting('formID').'" method="'.$this->getSetting('method').'" action="'.$this->getSetting('action').'#'.$this->getSetting('formID').'_wrapper">';
				echo $this->getContent();
			echo '</form>';
		echo '</div>';
		
		$content = ob_get_clean();
		return $content;
	}
	
}//Form
?>