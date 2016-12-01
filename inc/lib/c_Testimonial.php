<?php
class Testimonial {
	
	public $post;
	public $author;
	public $hasDate = false;
	public $time = '';
	public $dateFormat;
	
	/**
	* CONSTRUCTOR
	*
	* @param int|obj $post The post object or post ID
	*/
	public function __construct($post) {
		$this->post = is_object($post) ? $post : get_post($post);
		$this->dateFormat = get_option('date_format');
		$this->author = get_field('testimonial_author', $this->post->ID);
		
		if( $date = get_field('testimonial_date', $this->post->ID) ) {
			$this->hasDate = true;
			$this->time = strtotime($date);
			$this->setDate();
		}
	}
	
	/**
	* GET CONTENT
	* Fetches the testimonial content
	*
	* @param bool $unfiltered Whether to apply 'the_content' filter
	* @return string
	*/
	public function getContent($unfiltered = false, $words = null) {
		$content = $this->post->post_content;
		
		if( $words ) {
			$content = ManifestFramework::trim_excerpt($content,$words,'&hellip;',false);
		}
		
		return !$unfiltered ? apply_filters('the_content', $content) : $content;
	}
	
	/**
	* GET AUTHOR
	*
	* @return string
	*/
	public function getAuthor() {
		return $this->author;
	}
	
	/**
	* SET DATE FORMAT
	*
	* @param string $format A PHP date format string
	* @return void
	*/
	public function setDateFormat($format) {
		$this->dateFormat = $format;
		$this->setDate();
	}
	
	/**
	* SET DATE
	*
	* @return void
	*/
	private function setDate() {
		$this->date = date($this->dateFormat, $this->time);
	}
	
	/**
	* HAS DATE
	*
	* @return bool
	*/
	public function hasDate() {
		return $this->hasDate;
	}
	
	/**
	* GET DATE
	*
	* @param bool $timestamp Whether to return formatted as timestamp
	* @return mixed Either a timestamp int or a formatted string
	*/
	public function getDate($timestamp = false) {
		return $timestamp ? $this->time : $this->date;
	}
	
}//Testimonial
