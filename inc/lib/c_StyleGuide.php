<?php
class StyleGuide {
	
	private $config;
	public $Sass;
	private $currentType;
	private $currentSection;
	private $currentBlock;
	
	function __construct() {
		
		//Fetch the config file
		$this->setConfig();
		
		//Fetch the stylesheet
		$this->setSass();
		
		//Set the template root
		$this->setTemplateRoot();
		
		//Show the layout
		$this->show();
		
	}
	
	/**
	* SET CONFIG
	*
	* @param string $file Absolute file path to JSON config file
	* @return void
	*/
	private function setConfig($file = null) {
		
		if( !$file) {
			$file = apply_filters('mc_StyleGuide_config_file', INCLUDES_DIR.'/styleguide/guide.json', $this);
		}
		
//		$contents = json_decode(file_get_contents($file));
//		$error = json_last_error();
//		$errorMsg = json_last_error_msg();
//		do_dump($contents);
//		do_dump($error);
//		do_dump($errorMsg);
		
		$this->config = ManifestFramework::getJsonFile($file);
		
	}
	
	/**
	* GET CONFIG
	*
	* @return obj
	*/
	public function getConfig() {
		return $this->config;
	}
	
	/**
	* SET SASS
	*
	* @param string $file Absolute file path
	* @return void
	*/
	private function setSass($file = null) {
		$file = !$file ? apply_filters('mc_StyleGuide_sass_file', STYLESHEETPATH.'/scss/config/_settings.scss', $this) : $file;
		$this->Sass = new Sass($file);
	}
	
	private function setTemplateRoot($path = null) {
		$this->templateRoot = !$path ? apply_filters('mc_StyleGuide_template_path', 'templates/styleguide', $this) : $path;
	}
	
	private function getTemplateRoot() {
		return $this->templateRoot;
	}
	
	private function show() {
		
		$config = $this->getConfig();
		
		echo '<div id="styleGuide">';
		foreach( $config as $name => $type) {
			$this->showType($name, $type);
		}
		echo '</div>';
	}
	
	private function showType($name, $type) {
		$this->currentType = $name;
		
		echo '<div id="type-'.$name.'" class="sectionWrap">';
		foreach( $type as $name => $section ) {
			$this->showSection($name, $section);
		}
		echo '</div>';//.sectionWrap
	}
	
	private function showSection($name, $section) {
		$this->currentSection = $name;
		
		echo '<div id="section-'.$name.'" class="section row">';
			echo '<div class="small-12 columns">';
		
				echo '<h2 class="sectionTitle">'.($section->title ? $section->title : ucfirst($name)).'</h2>';
				echo $section->description ? '<p class="description sectionDescription">'.$section->description.'</p>' : '';
				
				if( empty($section) || !isset($section->blocks) ) {
					$templateFile = $this->getTemplateRoot().'/'.$this->currentType.'/'.$name.'.php';
					$template = locate_template(array($templateFile), false);
					
					if( $template ) {
						require($template);
					}
					else {
						echo '<div class="panel"><p>Use template: '.$templateFile.'</p></div>';
					}
				} else {
					foreach( $section->blocks as $name => $block ) {
						$this->showBlock($name, $block);
					}
				}
			echo '</div>';//.columns
		echo '</div>';//.row
		
	}
	
	private function showBlock($name, $block) {
		$this->currentBlock = $name;
		
		echo '<div id="block-'.$name.'" class="block">';
		echo $block->title ? '<h3 class="blockTitle">'.$block->title.'</h3>' : '';
		
		$templates = array();
		$templateRoot = $this->getTemplateRoot();
		$templates[] = $templateRoot.'/'.$this->currentType.'/'.$this->currentSection.'-'.$name.'.php';
		$templates[] = $templateRoot.'/'.$this->currentType.'/'.$this->currentSection.'.php';
		
		$template = locate_template($templates, false);
		if( $template ) {
			require($template);
		}
		else {
			echo 'Template file doesn\'t exist...';
			do_dump($templates);
		}
		echo '</div>';//.row
	}
	
}//StyleGuide
