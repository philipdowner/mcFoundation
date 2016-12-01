<?php
class Sass {

	public $sass = '';
	private $file = '';
	private $pattern;
	private $defaultValue = '';
	
	/**
	* Constructor
	*
	* @param string $file Absolute path to the SASS file
	*/
	public function __construct($file) {
		
		if( !file_exists($file) ) return array();
		$this->file = $file;
		$this->setPattern('/^\$([a-zA-Z0-9-]*):[[:blank:]]([^;]*);$/m');
		$this->parse();
		
	}
	
	/**
	* SET PATTERN
	* Sets the regex pattern for parsing scss
	*
	* @param string $pattern
	* @return void
	*/
	public function setPattern($pattern) {
		$this->pattern = $pattern;
	}
	
	/**
	* SET DEFAULT VALUE
	* Sets a default return value if nothing is found by @see getValue
	*
	* @param mixed $default The default value
	*/
	public function setDefaultValue($default) {
		$this->defaultValue = $default;
	}
	
	/**
	* Parse
	*
	* @return void
	*/
	private function parse() {
		preg_match_all($this->pattern, file_get_contents($this->file), $matches, PREG_SET_ORDER);
		
		if( empty($matches) ) {
			$this->sass = $matches;
			return;
		}
		
		//Assign new array keys
		$keys = array_column($matches, 1);
		$this->sass = array_combine($keys, $matches);
	}
	
	/**
	* GET SASS VALUE
	*
	* @param string $key The named key to find the value of
	* @param bool $recurse Whether to recurse across variables in an attempt to find the final defined value
	* @return mixed
	*/
	public function getValue($key, $recurse = true, $default = null) {
		$default = !isset($default) ? $this->defaultValue : $default;
		
		if( !array_key_exists($key, $this->sass) ) return $default;
		
		$value = $this->sass[$key][2];
		if( $recurse && strpos($value, '$') === 0) {
			$key = str_replace('$', '', $value);
			return $this->getValue($key, true, $value);
		}
		
		return $value;
	}
	
	/**
	* GET VALUES
	*
	* @param array $keys Array of keys to get values for
	* @param $recurse Whether to recurse across variables in an attempt to find the final defined value.
	* @return array
	*/
	public function getValues($keys, $recurse = true) {
		$results = array();
		foreach( $keys as $key ) {
			$results[$key] = $this->getValue($key, $recurse);
		}
		return $results;
	}
	
}//Sass