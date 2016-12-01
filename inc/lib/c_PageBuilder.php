<?php
/**
* Page Builder
* Uses the Advanced Custom Fields flexible content field to build pages.
*
* @author Philip Downer <philip@manifestbozeman.com>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @version v1.0
*/
class PageBuilder extends ManifestFramework {
	
	public $post;
	private $builderFieldName = 'builder_rows';
	public static $builderTemplateSlug = 'templates/modules/pageBuilder';
	public $currentRow;
	public $currentCol;
	public $rows;
	public $moduleNames;
	
	public function __construct($builderFieldName = null, $post = null) {
		if( !$post ) {
			global $post;
			$this->post = $post;
		}
		else {
			$this->post = is_object($post) ? $post : get_post($post);
		}
		
		$this->rows = get_field($this->builderFieldName, $this->post->ID);
		
		if($builderFieldName) $this->builderFieldName = $builderFieldName;
		
		//Filterable Defaults
		self::$builderTemplateSlug = apply_filters('mcFoundation_builder_template_slug', self::$builderTemplateSlug);
		
		//Class Actions (not a law-suit!)
		if( get_field('builder_show_title', $this->post->ID) ) {
			add_action('mcFoundation_builder_before_layout', array($this, 'show_page_title'));
		}
		
		//Class Filters
		add_filter('mcFoundation_builder_columns_class', array($this, 'perModuleClasses'), 10, 3);
		
		
		//Load Facebook JS SDK
		if( $this->builderHasModule('facebook-like') ) {
			add_action('mcFoundation_before_content_container', array($this, 'mcFoundation_builder_facebook_js_sdk'));
		}
		
		$GLOBALS['pageBuilder'] = $this;
		
		
	}//__construct()
	
	/**
	* GET BUILDER TEMPLATE SLUG
	*
	* @return string
	*/
	static function getBuilderTemplateSlug() {
		return self::$builderTemplateSlug;
	}
	
	
	
	/**
	* GET CONTENT
	* Wrapper function that outputs the full layout
	*
	* @return string
	*/
	public function getContent() {
		
		//Start an output buffer
		ob_start();
		
		$rows = $this->rows;
		
		echo '<div id="pageBuilder" class="'.$this->getBuilderWrapClass().'">';
		
			//If no rows return an error message
			if( empty($rows) ) {
				if( current_user_can('edit_pages') ) {
					$message = '<div id="mcFoundationBuilderNoRows" class="alert-box warning radius"><p>Your layout for this page contains no rows. Get started by <a href="'.admin_url('post.php?post='.$this->post->ID.'&action=edit').'">adding some rows now</a>.</p></div>';
					
				}
				else {
					$message = '<div id="mcFoundationBuilderNoRows" class="alert-box info radius"><p>It doesn\'t appear that there is any content for this page.</p></div>';
				}
				return apply_filters('mcFoundation_builder_no_rows', '<div class="row">'.$message.'</div>', $this);
			}
		
			do_action('mcFoundation_builder_before_layout', $rows, $this);
		
			//Loop thru each row
			foreach( $rows as $idx => $row ) {
				$this->getRowLayout($row, $idx);
			}
			
			do_action('mcFoundation_builder_after_layout', $rows, $this);
		
		echo '</div>';//#pageBuilder
		
		return ob_get_clean();
		
	}
	
	/**
	* GET ROW LAYOUT
	*
	* @param array $row
	* @param int $rowIndex
	*/
	private function getRowLayout($row, $rowIndex) {
		
		//Assemble the row options
		$rowOptions = array(
			'numCols' => count($row['column']),
			'containsCols' => array(),
			'colWidths' => count($row['column']) == 2 ? (int)$row['row_column_widths'] : 0,
			'cssId' => $row['row_css_id'],
			'index' => $rowIndex
		);
		
		//Alternate Row Layouts
		if( !empty($row['layout']) ) {
			$rowOptions['layoutOptions'] = array();
			$row['hasRowWrap'] = true;
			foreach($row['layout'] as $layout ) {
				$rowOptions['layoutOptions'][] = $layout;
			}
		}
		
		//Contains Columns
		foreach( $row['column'] as $column ) {
			$rowOptions['containsCols'][] = $column['acf_fc_layout'];
		}
		
		//Set the current row globally
		$this->currentRow = array('row' => $row, 'options' => $rowOptions);
		
		//Open the full width row wrapper
		if( array_key_exists('layoutOptions', $rowOptions) ) {
			echo '<div id="'.$this->getRowId().'" class="'.$this->getRowClass($row, $rowOptions).'">';
			unset($this->currentRow['options']['layoutOptions']);
		}
		
		//Open the row div
		echo '<div id="'.$this->getRowId().'" class="'.$this->getRowClass().'">';
			
			//Show the row headline
			if( $row['headline'] ) {
				$headlineClasses = apply_filters('mcFoundation_builder_rowheadline_class', array(
					'default' => 'rowHeadline columns end',
					'small' => 'small-12'
				), $row, $rowOptions, $this);
				
				echo '<div class="'.implode(' ', $headlineClasses).'">';
					echo $this->getElement('h2', $row['headline'], array('class' => 'headline'));
					//echo '<h2 class="headline">'.$row['headline'].'</h2>';
				echo '</div>';
			}
			
			//Loop thru the columns
			$this->getColumnsLayout($row['column']);
			
			//Show the call to action button
			if( $row['button_row_cta'] ) {
				echo '<div class="small-12 columns row_call_to_action">';
					echo '<a href="'.get_permalink($row['button_row_cta_link']->ID).'" class="'.$this->getButtonClass('row_cta', $rowOptions).'">'.$row['button_row_cta_label'].'</a>';
				echo '</div>';//.row_call_to_action
			}
			
		echo '</div>';//.row
		
		//Close the full width row wrapper
		if( isset($this->currentRow['row']['hasRowWrap']) ) {
			echo '</div>';//.rowWrap
		}
		
	}//getRowLayout
	
	/**
	* GET COLUMNS LAYOUT
	*
	* @param array $columns Array of columns within a row
	*/
	private function getColumnsLayout($columns) {
		
		//Create an options array for each column
		$colOptions = array(
			'numCols' => count($columns),
			'currentCol' => 1
		);
		
		foreach( $columns as $idx => $column ) {
			
			$colOptions['name'] = $column['acf_fc_layout'];
			$colOptions['index'] = $idx;
			$colOptions['hasHeadline'] = array_key_exists('headline', $column) && !empty($column['headline']);
			$colOptions['hasButton'] = (array_key_exists('show_button', $column) && $column['show_button']) ? true : false;
			$colOptions['isAside'] = array_key_exists('aside', $column) ? $column['aside'] : false;
			
			//Set the current column globally
			$this->currentCol = array('column' => $column, 'options' => $colOptions);
			
			//Open the column
			echo '<div id="'.$this->getUniqueId('column').'" class="'.$this->getColumnClass($column['acf_fc_layout'], $colOptions).'">';
				
				echo $colOptions['isAside'] ? '<aside>' : '';
				
				//Show the column headline
				echo $colOptions['hasHeadline'] ? $this->getElement('subheadline', $column['headline']) : '';
				
				//Show the module layout
				$this->getModuleLayout($column, $colOptions);
				
				//Show the button
				if( $colOptions['hasButton'] ) {
					echo $this->getElement('a', $column['button_text'], array(
						'class' => $this->getButtonClass($column['acf_fc_layout'], $colOptions),
						'attr' => array('href' => $column['link']),
						'before' => '<p class="moduleButtonWrap">',
						'after' => '</p>'
					));
				}
				
				echo $colOptions['isAside'] ? '</aside>' : '';
			echo '</div>';
			
			$colOptions['currentCol']++;
		}
		
	}//getColumnsLayout
	
	/**
	* GET MODULE LAYOUT
	* Modules are displayed within columns.
	*
	* @param array $column Fields for the column
	* @param array $colOptions Column options
	* @return void
	*/
	private function getModuleLayout($column, $colOptions) {
		
		$name = $column['acf_fc_layout'];
		
		//Start an output buffer to allow the content to be filtered
		ob_start();
		
		switch( $name ) {
			
			//TEXT COLUMN
			case 'column_text':
				echo apply_filters('the_content', ManifestFramework::unautop($column['content']));
			break;
			
			//IMAGE COLUMN
			//@todo Consider basing image thumb off # of columns
			case 'column_image':
				$image = $column['image'];
				$caption = $column['caption'];
				
				$imgClass = array();
				$imgClass[] = $column['img_styling'] ? 'th' : '';
				
				echo '<figure>';
					echo '<img src="'.$image['url'].'" alt="'.$image['alt'].'" class="'.implode(' ', $imgClass).'" />';
					
					if( $caption ) {
						echo '<figcaption>'.$caption.'</figcaption>';
					}
				echo '</figure>';
			
			break;
			
			//IMAGE GALLERY COLUMN
			case 'column_slideshow':
				
				$rowOptions = $this->currentRow['options'];
				$colOptions = $this->currentCol['options'];
				
				//Column Images
				$images = $column['images'];
				
				//Determine whether we are using thumbnail navigation
				$usingThumbs = in_array('thumbnails', $column['slideshow_options']);
				
				//Determine whether we are using adaptive height
				$adaptiveHeight = in_array('adaptiveHeight', $column['slideshow_options']);
				
				//Set the primary thumbnail size
				$primaryThumbSizes = ManifestFramework::getResponsiveImageSizes(null, !$adaptiveHeight);
				$primaryThumbSizes['default'] = $primaryThumbSizes['large'];
				if( $rowOptions['numCols'] == 2 ) {
					$primaryThumbSizes['default'] = !$adaptiveHeight ? 'mc_crop_600' : 'mc_600';
					$primaryThumbSizes['large'] = !$adaptiveHeight ? 'mc_crop_800' : 'mc_800';
				}
				if( $rowOptions['numCols'] == 3 ) {
					$primaryThumbSizes['default'] = $primaryThumbSizes['mc_crop_400'];
					$primaryThumbSizes['large'] = !$adaptiveHeight ? 'mc_crop_600' : 'mc_600';
				}
				
				//Set filterable defaults
				$thumbSize = apply_filters('mcFoundation_builder_thumb_size', array('slides' => $primaryThumbSizes, 'thumbnails' => 'thumbnail'), $name, $column, $this);
				
				$imgCount = count($images);
				$slidesToShow = $imgCount > 5 ? 5 : $imgCount > 10 ? 10 : $imgCount - 1;
								
				//Override a few slickJS defaults, so they can be set by admin UI users
				$slickOptions = array(
					'primary' => array(
						'slidesToShow' => 1,
						'slidesToScroll' => 1,
						'arrows' => false,
						'pauseOnHover' => false,
						'adaptiveHeight' => false
					),
					'thumbnails' => array(
						'arrows' => false,
						'pauseOnHover' => false,
						'slidesToShow' => $slidesToShow,
						'slidesToScroll' => 1,
						'centerMode' => true,
						'focusOnSelect' => true,
						'centerPadding' => '50px',
						'draggable' => false
					)
				);
				
				//Make user settings based on whether we are syncing slideshows for thumbnail navigation
				foreach( $column['slideshow_options'] as $option ) {
					switch( $option ) {
						case 'dots':
							if( !$usingThumbs ) {
								$slickOptions['primary']['dots'] = true;
							} else {
								$slickOptions['primary']['dots'] = false;
								$slickOptions['thumbnails']['dots'] = true;
							}
						break;
						
						case 'arrows':
							if( !$usingThumbs ) {
								$slickOptions['primary']['arrows'] = true;
							}
							else {
								$slickOptions['thumbnails']['arrows'] = true;
							}
							
						break;
						
						case 'thumbnails':
							//$slickOptions['primary']['fade'] = true;
							$slickOptions['primary']['asNavFor'] = $this->getUniqueId('#slickThumbNav');
							$slickOptions['thumbnails']['asNavFor'] = $this->getUniqueId('#slickSlides');
						break;
						
						case 'adaptiveHeight':
							$slickOptions['primary']['adaptiveHeight'] = true;
						break;
						
						default:
							$slickOptions['primary'][$option] = true;
							if( $usingThumbs ) $slickOptions['thumbnails'][$option] = true;
						break;
					}//switch
				}//foreach
				
				//Filter the slick slideshow options
				$slickOptions = apply_filters('mcFoundation_builder_slick_options', $slickOptions, $column, $name, $this);
				
//				do_dump($thumbSize);
//				do_dump($images);
				//Loop thru the images
				echo '<div id="'.$this->getUniqueId('slickSlides').'" class="imagesList slickSlides" data-slick=\''.json_encode($slickOptions['primary']).'\'>';
					foreach( $images as $img ) {
						
						//echo ManifestFramework::responsiveImg($img['ID'], $thumbSize['slides']);
						echo '<img src="'.$img['sizes'][$thumbSize['slides']['default']].'" alt="'.$img['alt'].'" />';
					}
				echo '</div>';
				
				//Display the thumbnail navigation
				if( $usingThumbs ) {
					echo '<div id="'.$this->getUniqueId('slickThumbNav').'" class="thumbList slickSlides" data-slick=\''.json_encode($slickOptions['thumbnails']).'\'>';
						foreach( $images as $img ) {
							echo '<img src="'.$img['sizes'][$thumbSize['thumbnails']].'" alt="'.$img['alt'].'" />';
						}
					echo '</div>';
				}
				
			break;
			
			//GALLERY THUMBNAILS COLUMN
			case 'column_thumbnails':
				
				//Column User Options
				$imagesPerRow = intval($column['images_row']);
				$defaultImgStyles = boolval($column['img_styling']);
				$onClick = $column['when_clicked'];
				$onClick = $onClick == 'none' ? false : $onClick;
				$images = $column['images'];
				
				//Filterable Defaults
				$blockGridClasses = array('block-grid');
				$blockGridClasses['size-small'] = ($imagesPerRow > 3 && $colOptions['numCols'] > 1) ? 'small-block-grid-2' : 'small-block-grid-'.$imagesPerRow;
				$blockGridClasses['size-medium'] = ($imagesPerRow > 3 || $colOptions['numCols'] > 1) ? 'medium-block-grid-3' : 'medium-block-grid-'.$imagesPerRow;
				$blockGridClasses['size-large'] = 'large-block-grid-'.$imagesPerRow;
				$blockGridClasses[] = $column['first_img_large'] ? 'firstLarge' : '';
				
				if( $onClick == 'enlarge' ) $blockGridClasses['clearing'] = 'clearing-thumbs';
				$blockGridClasses = apply_filters('mcFoundation_builder_block_grid_class', $blockGridClasses, $column, $name, $this);
				
				//Set the thumbnail sizes
				$thumbSize = apply_filters('mcFoundation_builder_thumb_size', array('images' => 'large', 'thumbnails' => 'thumbnail'), $name, $column, $this);
				
				//Set the first thumbnail size
				$firstImgThumbSize = $thumbSize['thumbnails'];
				if( $column['first_img_large'] ) {
					$firstImgThumbSize = apply_filters('mcFoundation_builder_first_thumb_size', 'mc_600', $name, $column, $this);
				}
				
				//Column Defaults
				$thumbGroup = $this->getUniqueId('thumbgroup');
				$imgClass = array();
				$imgClass[] = $defaultImgStyles ? 'th' : '';
				
				//Loop thru the images
				echo '<ul id="'.$thumbGroup.'" class="'.implode(' ', $blockGridClasses).'" '.($onClick == 'enlarge' ? 'data-clearing' : '').'>';
					foreach( $images as $idx => $img ) {
						
						//Set the image link
						$anchor = '';
						if( $onClick ) {
							switch( $onClick ) {
								case 'enlarge':
									$anchor = '<a href="'.$img['image']['sizes'][$thumbSize['images']].'" title="'.$img['image']['title'].'" >';
								break;
								
								case 'link_internal':
									$anchor = '<a href="'.get_permalink($img['link_internal']).'">';
								break;
								
								case 'link_external':
									$anchor = '<a href="'.$img['link_external'].'" class="external" target="_blank">';
								break;
							}
						}
						
						//Are we showing a caption
						$hasCaption = ($column['show_captions'] && $onClick == 'enlarge' && $img['caption']) ? true : false;
						
						echo '<li class="thumb-'.$idx.'">';
							
							$src = $idx === 0 ? $img['image']['sizes'][$firstImgThumbSize] : $img['image']['sizes'][$thumbSize['thumbnails']];
							
							$imgTag = $anchor;
							$imgTag .= '<img';
							$imgTag .= $hasCaption ? ' data-caption="'.$img['caption'].'"' : '';
							$imgTag .= ' class="'.implode(' ', $imgClass).'" src="'.$src.'" alt="'.$img['image']['alt'].'" />';
							$imgTag .= $anchor ? '</a>' : '';
							
							echo $imgTag;
						echo '</li>';
					}
				echo '</ul>';//.block-grid
				
			break;
			
			//SELECTED POSTS
			case 'selected_posts':
					$queryArgs = array(
						'post_type' => 'post',
						'posts_per_page' => (int)$column['posts_per_page'] < 1 ? -1 : (int)$column['posts_per_page'],
						'orderby' => $column['posts_orderby'],
						'order' => $column['posts_order'],
						'ignore_sticky_posts' => $column['posts_ignore_sticky_posts']
					);
					
					//do_dump($column);
				
					switch( $column['posts_matching'] ) {
						
						case 'manual':
							$queryArgs['posts_per_page'] = -1;
							$queryArgs['post__in'] = $column['posts_manual'];
							break;
						
						case 'category':
							$queryArgs['category__in'] = $column['posts_category'];
							break;
							
						case 'tag':
							$queryArgs['tag__in'] = $column['posts_tags'];
							break;
					}
					
					$queryArgs = apply_filters('mcFoundation_builder_query_args', $queryArgs, $column, $name, $this);
					
					//Show the posts
					$posts = new WP_Query($queryArgs);
					if( $posts->have_posts() ) {
						
						$listClasses = array(
							'postsList'
						);
						$listClasses[] = $column['posts_layout'] != 'list' ? 'no-bullet' : '';
						
						echo '<ul class="'.implode(' ', $listClasses).'">';
						
						switch( $column['posts_layout'] ) {
							case 'list':
								while( $posts->have_posts() ) {
									$post = $posts->the_post();
									echo '<li>';
									$template = apply_filters('mcFoundation_builder_get_template_part', array('slug' => self::$builderTemplateSlug.'-loop', 'name' => 'list'), $column, $name, $this);
									get_template_part($template['slug'], $template['name']);
									echo '</li>';
								}
								break;
								
							case 'thumb':
								while( $posts->have_posts() ) {
									$post = $posts->the_post();
									echo '<li>';
									$template = apply_filters('mcFoundation_builder_get_template_part', array('slug' => self::$builderTemplateSlug.'-loop', 'name' => ''), $column, $name, $this);
									get_template_part($template['slug'], $template['name']);
									echo '</li>';
								}
								break;
						}
						echo '</ul>';//.postsList
					}
					else {
						$template = apply_filters('mcFoundation_builder_get_template_part', array('slug' => self::$builderTemplateSlug.'-loop', 'name' => 'none'), $column, $name, $this);
						get_template_part($template['slug'], $template['name']);
					}
			break;
			
			//Accordions & Tabs (column_accordion_tabs)
			case 'column_accordion_tabs':
				
				$colIdx = $this->currentCol['options']['index'];
				$displayAs = $column['display_as'];
				$panels = $column['panels'];
				
				//Accordions
				if( $displayAs == 'accordion' ):
					echo '<ul class="accordion" data-accordion>';
						foreach( $panels as $idx => $panel ) {
							$panelClass = array();
							$panelClass[] = $idx === 0 ? 'active' : '';
							echo '<li class="accordion-navigation '.implode(' ', $panelClass).'">';
								echo '<a href="#accordion-'.$colIdx.'-panel-'.$idx.'">'.$panel['name'].'</a>';
								echo '<div id="accordion-'.$colIdx.'-panel-'.$idx.'" class="content '.implode(' ', $panelClass).'">'.$panel['content'].'</div>';
							echo '</li>';
						}
					echo '</ul>';
				
				//Tabs
				else:
					$tabsClass = array('tabs');
					$tabsClass[] = $column['tab_placement'] == 'vertical' ? 'vertical' : '';
					echo '<ul class="'.implode(' ', $tabsClass).'" data-tab>';
						foreach($panels as $idx => $panel) {
							$tabClass = array('tab-title');
							$tabClass[] = $idx === 0 ? 'active' : '';
							echo '<li class="'.implode(' ', $tabClass).'"><a href="#tabs-'.$colIdx.'-tab-'.$idx.'">'.$panel['name'].'</a></li>';
						}
					echo '</ul>';
					echo '<div class="tabs-content">';
						foreach($panels as $idx => $panel) {
							$panelClass = array('content');
							$panelClass[] = $idx === 0 ? 'active' : '';
							echo '<div id="tabs-'.$colIdx.'-tab-'.$idx.'" class="'.implode(' ', $panelClass).'">'.$panel['content'].'</div>';
						}
					echo '</div>';
				
				endif;
				
			break;
			
			//Navigation (column_navigation)
			case 'column_navigation':
				$navDefaults = array(
					'menu' => $column['menu'],
					'container' => false,
					'menu_class' => 'side-nav',
					'menu_id' => 'mcFoundation-column_navigation-'.$column['menu']
				);
				$navArgs = apply_filters('mcFoundation_builder_navigation_args', $navDefaults, $column, $name, $this);
				wp_nav_menu($navArgs);
			break;
			
			//Contact Information (column_contact)
			case 'column_contact':
				$methods = array();
				
				foreach( $column['display_contacts'] as $method ) {
					$methods[$method] = get_theme_option('option_'.$method);
				}
				
				//Telephone
				if( array_key_exists('telephone', $methods) && !empty($methods['telephone']) ) {
					echo '<h5 class="methodTitle">'.apply_filters('mcFoundation_builder_column_contact_title', 'Telephone', 'telephone', $methods).'</h5>';
					echo '<p>'.$methods['telephone'].'</p>';
				}
				
				//Toll-Free Telephone
				if( array_key_exists('telephone_tollfree', $methods) && !empty($methods['telephone_tollfree']) ) {
					echo '<h5 class="methodTitle">'.apply_filters('mcFoundation_builder_column_contact_title', 'Toll-Free Telephone', 'telephone_tollfree', $methods).'</h5>';
					echo '<p>'.$methods['telephone_tollfree'].'</p>';
				}
				
				//Email
				if( array_key_exists('company_email', $methods) && !empty($methods['company_email']) ) {
					echo '<h5 class="methodTitle">'.apply_filters('mcFoundation_builder_column_contact_title', 'Email Address', 'company_email', $methods).'</h5>';
					echo '<p><a href="mailto:'.antispambot($methods['company_email'], 1).'">'.antispambot($methods['company_email'], 0).'</a></p>';
				}
				
				//Fax
				if( array_key_exists('fax', $methods) && !empty($methods['fax']) ) {
					echo '<h5 class="methodTitle">'.apply_filters('mcFoundation_builder_column_contact_title', 'Fax', 'fax', $methods).'</h5>';
					echo '<p>'.$methods['fax'].'</p>';
				}
				
				//Address
				if( array_key_exists('physical_address', $methods) || array_key_exists('mailing_address', $methods) ) {
					
					//Check if the mailing and physical are the same
					$mailingDifferent = get_theme_option('option_mailing_address_different');
					
					if( !$mailingDifferent && !empty($methods['physical_address']) ) {
						$address = !empty($methods['physical_address']) ? $methods['physical_address'] : $methods['mailing_address'];
						echo '<h5 class="methodTitle">'.apply_filters('mcFoundation_builder_column_contact_title', 'Address', 'physical_address', $methods).'</h5>';
						echo '<address>'.$address.'</address>';
					}
					else {
						if( !empty($methods['physical_address']) ) {
							echo '<h5 class="methodTitle">'.apply_filters('mcFoundation_builder_column_contact_title', 'Address', 'physical_address', $methods).'</h5>';
							echo '<address>'.$methods['physical_address'].'</address>';
						}
						
						if( !empty($methods['mailing_address']) ) {
							echo '<h5 class="methodTitle">'.apply_filters('mcFoundation_builder_column_contact_title', 'Mailing Address', 'mailing_address', $methods).'</h5>';
							echo '<address>'.$methods['mailing_address'].'</address>';
						}
					}
				}
				
				//Google Map
				if( array_key_exists('google_map', $methods) && !empty($methods['google_map']) ) {
					echo '<h5 class="methodTitle">'.apply_filters('mcFoundation_builder_column_contact_title', 'Location Map', 'google_map', $methods).'</h5>';
					?>
					<script async defer src="https://maps.googleapis.com/maps/api/js?&callback=initContactMap"></script>
					<script type="text/javascript">
						var contactMap;
						function initContactMap() {
						  contactMap = new google.maps.Map(document.getElementById('contactMap'), {
						    center: {lat: <?php echo $methods['google_map']['lat']; ?>, lng: <?php echo $methods['google_map']['lng']; ?>},
						    zoom: <?php echo $column['map_zoom_level']; ?>
						  });
						  
						  var marker = new google.maps.Marker({
							  position: contactMap.center,
							  map: contactMap,
							  title: '<?php echo get_bloginfo('name'); ?>'
						  });
						}
					</script>
					
					<?php
					$contactMapStyles = apply_filters('mcFoundation_builder_column_contact_map_styles', array('height' => '400px'));
					echo '<div id="contactMap" class="mapCanvas" style="';
					foreach( $contactMapStyles as $k => $v ) {
						echo $k.':'.$v.';';
					}
					echo '"></div>';
				}
				
				//Social Networks
				if( array_key_exists('social_urls', $methods) ) {
					echo '<h5 class="methodTitle">'.apply_filters('mcFoundation_builder_column_contact_title', 'Connect with us on these sites&hellip;', 'social_urls', $methods).'</h5>';
					echo ManifestFramework::list_social_icons();
				}
			break;
			
			//Custom Modules
			default:
				//If it's a designated custom column, fetch a module slug
				if( $column['acf_fc_layout'] == 'column_custom' ) {
					$module = $column['custom_module'] !== '0' ? 'custom-'.$column['custom_module'] : '';
					$module = $module == 'template' ? $column['custom_template_slug'] : $module;
				}
				
				//It's a column with a fields UI, but no dedicated logic
				else {
					$module = $column['acf_fc_layout'];
				}
				
				//Allow the designated template to be filtered
				$template = apply_filters('mcFoundation_builder_get_template_part', array('slug' => self::$builderTemplateSlug, 'name' => $module), $column, $name, $this);
				
				//Create an array of template names (in descending priority) to locate
				$template_names = array();
				
				if( $template['name'] ) {
					$template_names[] = $template['slug'].'-'.$template['name'].'.php';
					
					if( self::$builderTemplateSlug !== $template['slug'] ) {
						$template_names[] = self::$builderTemplateSlug.'-'.$template['name'].'.php';
					}
				}
				
				//Generic fallback templates
				$template_names[] = $template['slug'].'-custom.php';
				if( self::$builderTemplateSlug !== $template['slug'] ) {
					$template_names[] = self::$builderTemplateSlug.'-custom.php';
				}
				
				include(locate_template($template_names, false));
			break;
		}
		
		//Filter the output
		$module = ob_get_clean();
		
		//Return the filtered content
		echo apply_filters('mcFoundation_builder_module', $module, $name, $column, $colOptions, $this);
		
	}//getModuleLayout
	
	/*************************
	* ELEMENTS
	**************************/
	/**
	* GET ELEMENT
	* Fetches a single layout element such as a module header by constructing the (filterable) HTML for the item
	*
	* @param string $element Name of the element
	* @param string $content Content between the open and closing tags. Ignored if $options['inline'] == true
	* @param array $options Array of options that influence the display of the element
	* @return string
	*/
	public function getElement($element, $content='', $options = array()) {
		
		//Set a few default options
		$defaults = array(
			'element' => $element,
			'class' => array(),
			'attr' => array(),
			'inline' => false,
			'before' => '',
			'after' => ''
		);
		
		//Set additional defaults by element name
		switch( $element ) {
			//Subheader
			case 'subheadline':
				$defaults = array(
					'element' => 'h3',
					'class' => array('subHeadline')
				);
			break;
		}
		
		//Filter the defaults
		apply_filters('mcFoundation_builder_element_defaults', $defaults, $element);
		
		//Filter the merged options array
		$options = apply_filters('mcFoundation_builder_element_options', array_merge($defaults, $options), $element, $defaults, $options);
		
		//Start an output buffer
		ob_start();
		
		//Show any before content
		echo $options['before'];
		
		//Construct the element opening
		echo '<'.$options['element'].' class="'.(is_array($options['class']) ? implode(' ', $options['class']) : $options['class']).'" ';
		
		if( !empty($options['attr']) ) {
			foreach($options['attr'] as $attr => $value) {
				echo $attr.'="'.$value.'" ';
			}
		}
		
		//Close the opening tag
		echo $options['inline'] ? '/>' : '>';
		
		//If not an inline element, continue building the HTML
		if( !$options['inline'] ) {
			
			//Show the element content. (e.g. the link text for an <a> element
			echo apply_filters('mcFoundation_builder_element_content', $content, $element, $options);
			
			//Close the element HTML
			echo '</'.$options['element'].'>';
		}
		
		//Show any after content
		echo $options['after'];
		
		//Clear the output buffer
		$content = ob_get_clean();
		
		//Filter and return
		return apply_filters('mcFoundation_builder_element_content', $content, $element, $options);
		
	}//getElement()
	
	/*************************
	* CSS CLASSES
	**************************/
	/**
	* GET UNIQUE ID
	* Creates a unique ID based on the current row and column indexes
	*
	* @param string $prepend String to prepend to the id integer
	* @return string
	*/
	private function getUniqueId($prepend = '', $column = true) {
		$id = $this->currentRow['options']['index'] + 1;
		if( $column ) {
			$id .= '-'.($this->currentCol['options']['index'] + 1);
		}
		return $prepend ? $prepend.'-'.$id : $id;
	}//getUniqueId
	
	/**
	* GET BUILDER WRAP CLASS
	*
	* @return string
	*/
	public function getBuilderWrapClass() {
		$defaults = array();
		$classes = apply_filters('mcFoundation_builder_wrap_class', $defaults, $this);
		return implode(' ', $classes);
	}
	
	/**
	* GET ROW ID
	*
	* @return string
	*/
	public function getRowId() {
		$row = $this->currentRow['row'];
		$rowOptions = $this->currentRow['options'];
		$customID = $row['row_css_id'] ? str_replace('#', '', $row['row_css_id']) : '';
		
		if( !empty($rowOptions['layoutOptions']) ) {
			return $customID ? 'rowWrap-'.$customID : $this->getUniqueId('rowWrap', false);
		}
		return $customID ? $customID : $this->getUniqueId('row', false);
	}
	
	/**
	* GET ROW CLASS
	*
	* @return string
	*/
	public function getRowClass() {
		$row = $this->currentRow['row'];
		$rowOptions = $this->currentRow['options'];
		
		$classes = array(
			'container' => 'row',
			'builderRow',
			'numCols-'.$rowOptions['numCols']
		);
		
		//Varying column widths
		$classes['hasColWidths'] = $rowOptions['colWidths'] ? 'hasColWidths' : ''; 
		
		//There are layout options, we need to wrap the row
		if( !empty($rowOptions['layoutOptions']) ) {
			$classes['container'] = 'rowWrap';
			$classes = array_merge($classes, $rowOptions['layoutOptions']);
		}
		
		//Reference the columns the row contains
		if( !empty($rowOptions['containsCols']) ) {
			foreach( $rowOptions['containsCols'] as $column ) {
				$classes[$column] = 'has-'.$column;
			}
		}
		
		//If any custom columns, add their name
		if( array_key_exists('column_custom', $classes) ) {
			foreach( $row['column'] as $col ) {
				if( $col['acf_fc_layout'] == 'column_custom' ) {
					$classes[] = 'customCol-'.$col['custom_module'];
				}
			}
		}
		
				
		$classes = apply_filters('mcFoundation_builder_row_class', $classes, $rowOptions, $row, $this);
		
		return implode(' ', $classes);
	}
	
	/**
	* GET COLUMN CLASS
	*
	* @param string $layout ACF flexible content layout name
	* @param array $colOptions
	*/
	public function getColumnClass($layout, $colOptions) {
		
		$rowOptions = $this->currentRow['options'];
		
		//Set the filterable defaults
		$defaults = array(
			'columns',
			'module',
			'module-'.$layout,
			'colOrder-'.$colOptions['currentCol'],
			'row-NumCols-'.$rowOptions['numCols']
		);
		
		//Column Width Preference
		if( $rowOptions['colWidths'] && $rowOptions['numCols'] == 2 ) {
			$defaults[] = $rowOptions['colWidths'] == $colOptions['currentCol'] ? 'colWidth-wider' : 'colWidth-default';
		}
		
		//Support for custom modules
		if( $layout == 'column_custom' ) {
			$defaults[] = 'customCol-'.$this->currentCol['column']['custom_module'];

			if( $this->currentCol['column']['custom_module'] == 'template' ) {
				$defaults[] = 'customCol-'.$this->currentCol['column']['custom_template_slug'];
			}
			
		}
		
		//Conditional classes
		$defaults['conditionals'] = implode(' ', array(
			'hasHeadline' => $colOptions['hasHeadline'] ? 'hasHeadline' : '',
			'hasButton' => $colOptions['hasButton'] ? 'hasButton' : '',
			'isAside' => $colOptions['isAside'] ? 'isAside' : ''
		));
		
		//Sizes are keyed by breakpoint for easy filtering
		$defaults['small'] = 'small-12';
		$defaults['medium'] = 'medium-'.floor(12/$colOptions['numCols']);
		
		//Check if the row has varying column widths, and if it's applied to the current column
		$varyingColWidths = $this->currentRow['options']['colWidths'];
		if( $varyingColWidths ) {
			$wideColumn = implode(' ', apply_filters('mcFoundation_builder_column_width_widest', array('medium' => 'medium-8'), $this));
			$narrowColumn = implode(' ', apply_filters('mcFoundation_builder_column_width_narrow', array('medium' => 'medium-4'), $this));
			$defaults['medium'] = $varyingColWidths == $colOptions['currentCol'] ? $wideColumn : $narrowColumn;
		}

		$classes = apply_filters('mcFoundation_builder_columns_class', $defaults, $layout, $colOptions, $this);
		$classes = apply_filters('mcFoundation_builder_module_class_'.$layout, $classes, $colOptions, $this);
		
		return implode(' ', $classes);
	}
	
	/**
	* PER MODULE CLASSES
	* CSS classes that are less global and only applied to certain module layouts. Hooked to 'mcFoundation_builder_columns_class'.
	*
	* @param array $defaults Default CSS classes for the column
	* @param string $layout The module layout name
	* @param array $colOptions User specified options for the column
	* @return array
	*/
	public function perModuleClasses($defaults, $layout, $colOptions) {
		
		$column = $this->currentCol['column'];
		
		switch( $layout ) {
			case 'selected_posts':
				$defaults[$layout.'_posts_layout'] = 'postsListDisplay-'.$column['posts_layout'];
			break;
			
			case 'column_slideshow':
				foreach( $column['slideshow_options'] as $option ) {
					$defaults[$layout.'_'.$option] = 'slideshowOption-'.$option;
				}
			break;
		}
		return $defaults;
		
	}//perModuleClasses()
	
	/**
	* GET BUTTON CLASS
	*
	* @param string $layout ACF flexible content name
	* @param array $colOptions
	* @return string
	*/
	public function getButtonClass($layout, $colOptions) {
		
		//Set the filterable defaults
		$defaults = array(
			'moduleButton',
			'button',
			'shape' => 'radius',
			'size' => ''
		);
		
		//Add a large class for primary cta buttons
		if( $layout == 'row_cta' ) {
			$defaults['cta'] = 'callToActionButton';
			$defaults['size'] = 'large';
		}
		
		//Use add a class if it's an aside
		if( array_key_exists('isAside', $colOptions) && $colOptions['isAside'] ) {
			$defaults['secondary'] = 'secondary';
		}
		
		$classes = apply_filters('mcFoundation_builder_button_class', $defaults, $layout, $colOptions, $this);
		
		return implode(' ', $classes);
	}
	
	/*************************
	* CLASS UTILITIES
	**************************/
	
	/**
	* GET UNIQUE MODULES
	*
	* Creates an array with the module names of each module type (image, text, etc.) that exists in the layout as the array key name and an array of hyphen separated row index and column indexes for each instance as the value.
	*
	* Has the potential to be memory intensive, so any functions that rely on this should first check for the existance of $this->moduleNames.
	*
	* @return array
	*/
	public function builderGetUniqueModules() {
		$modules = array();
		if( !$this->rows ) return $modules;
		
		//Loop thru the rows
		foreach($this->rows as $ridx => $row ) {
			$columns = $row['column'];
			//Loop thru each column
			foreach( $columns as $cidx => $module ) {
				$moduleName = $module['custom_module'] ? $module['custom_module'] : $module['acf_fc_layout'];
				$modules[$moduleName][] = $ridx.'-'.$cidx;
//				if( !array_key_exists($moduleName, $modules) ) {}
			}
		}
		$this->moduleNames = $modules;
		return $modules;
	}
	
	/**
	* CHECKS WHETHER AT LEAST 1 INSTANCE OF A PARTICULAR
	* MODULE EXISTS WITHIN THE ENTIRE LAYOUT
	*
	* @param string $module The module/column name
	* @return bool
	*/
	public function builderHasModule($module) {
		$modules = !$this->moduleNames ? $this->builderGetUniqueModules() : $this->moduleNames;
		return array_key_exists($module, $modules);
	}
	
	/**
	* GET MODULE BY INDEX
	*
	* @param int $row The row index
	* @param int $col The column index (0-2) within the row
	* @return array
	*/
	public function builderGetModuleByIndex($row, $column) {
		return $this->rows[$row]['column'][$column];
	}
	
	/*************************
	* FILTERABLE DEFAULTS
	**************************/
	/**
	* Get Default Thumbnail Sizes
	*
	* @param mixed $cols Number of columns in the row.
	*		~ Leave at default (null) to return current column based size
	*		~ Set to false to return all sizes
	*		~ Specify an int (1 - 3) to return a specific column based size
	* @return array Array containing thumb sizes for different media queries
	*/
	public function getDefaultThumbnailSizes($cols = null) {
		
		$row = $this->currentRow;
		$col = $this->currentCol;
		
		$defaultThumbs = apply_filters('mcFoundation_builder_default_thumb_sizes', array(
			'1' => array(
				'small' => 'mc_crop_400',
				'medium' => 'mc_crop_800',
				'large' => 'mc_crop_1200'
			),
			'2' => array(
				'small' => 'mc_crop_400',
				'medium' => 'mc_crop_600',
				'large' => 'mc_crop_600'
			),
			'3' => array(
				'small' => 'mc_crop_400',
				'medium' => 'mc_crop_400',
				'large' => 'mc_crop_400'
			)
		), $col['options']['numCols'], $col, $this);
		
		if( $cols ) return $defaultThumbs[$cols];
		if( $cols === false ) return $defaultThumbs;
		return $defaultThumbs[$col['options']['numCols']];
	}
	
	/*************************
	* ACTIONS
	**************************/
	public function show_page_title() {
		echo '<header class="row entryTitle pageTitle builderTitle">';
			$alternateTitle = get_field('builder_alternate_title', $this->post->ID);
			$title = $alternateTitle ? $alternateTitle : get_the_title($this->post->ID);
			echo $this->getElement('h1', $title, array(
				'before' => '<div class="small-12 columns">',
				'after' => '</div>'
			));
		echo '</header>';
	}
	
	/**
	* FILTER BUILDER CUSTOM TEMPLATES
	* A callable function that determines whether a specific file is a valid custom module
	*
	* @param $filename
	* @return bool
	*/
	static function filterBuilderCustomTemplates($filename) {
		$slug = array_pop(explode('/', self::$builderTemplateSlug)).'-custom';
		
		//The base module is a fallback, and not a selectable option
		if( $filename === $slug.'.php' ) {
			return false;
		}
		
		return strpos($filename, $slug) === false ? false : true;
	}
	
	/**
	* BUILDER CUSTOM MODULE TEMPLATES
	*
	* @param array $field ACF Field array
	* @return array
	*/
	static function builderCustomModuleTemplates( $field ) {
		$template_dir = dirname(get_template_directory().'/'.self::getBuilderTemplateSlug().'.php');
		$templates = array_diff(scandir($template_dir), array('.', '..', '.DS_Store'));
		$valid_templates = array_values(array_filter($templates, 'PageBuilder::filterBuilderCustomTemplates'));
		$baseSlug = array_pop(explode('/', self::$builderTemplateSlug));
		
		$customTemplates = array();
		foreach( $valid_templates as $idx => $template ) {
			$filePath = trailingslashit($template_dir).$template;
			$matches = array();
			$fileSlug = preg_match('/^'.$baseSlug.'-custom-([^\/\.]+)\.php/', $template, $matches);
			
			$customTemplates[] = array(
				'file' => $filePath,
				'slug' => $matches[1],
				'headers' => get_file_data($filePath, array('module_name' => 'Module Name'), 'mc_Builder'),
			);
			
		}
		
		//Compile the field options
		$field['choices'] = array();
		foreach( $customTemplates as $template ) {
			$field['choices'][$template['slug']] = $template['headers']['module_name'];
		}
		
		//Always include the template based slug option
		$field['choices']['template'] = 'Template Based Module (Advanced)';
		
		return $field;
	}
	
	/**
	* Filter Navigation Select Options
	*
	* @param array $field Field array
	* @return array
	*/
	static function mcFoundation_builder_navigation_select($field) {
		$menus = get_terms( 'nav_menu', array( 'hide_empty' => true ) );
		foreach( $menus as $menu ) {
			$field['choices'][$menu->slug] = $menu->name;
		}
		return $field;
	}
	
	/**
	* REMOVE ATTACHMENTS POST TYPE
	* Remove the attachments post type from post_object fields
	*
	* @param array $args The args used in get_posts() or get_pages() – depends on whether the post is hierarchical.
	* @param array The field array containing all attributes & settings
	* @param obj The current post object being edited
	* @return array
	*/
	static function mcFoundation_builder_remove_attachments_cpt($args, $field, $post) {
		if( !is_array($args['post_type']) ) return $args;
		if( strpos('pagebuilder', get_page_template_slug($post->ID)) === false ) return $args;
		if( !in_array('attachment', $args['post_type']) ) return $args;
		unset($args['post_type'][array_search('attachment', $args['post_type'])]);
		return $args;
	}
	
	/**
	* LOAD FONTAWESOME ICON SELECT
	*
	* @param array $field ACF Field array
	* @return array
	*/
	static function mcFoundation_builder_fa_icon_select($field) {
		require_once(INCLUDES_DIR.'/lib/c_Sass.php');
		$fa_vars = BOWER_PATH.'/fontawesome/scss/_variables.scss';
		$sass = new Sass($fa_vars);
		$fa_keys = array_keys($sass->sass);
		$fa_icon_names = array();
		foreach( $fa_keys as $key ) {
			if( strpos($key, 'fa-var-') === 0 ) {
				$name = str_replace('fa-var-', '', $key);
				$fa_icon_names[$name] = $name;
			}
		}
		$field['choices'] = $fa_icon_names;
		return $field;
	}
	
	/**
	* TAXONOMY TERM SELECT FIELD
	*
	* @param array $field ACF Field array
	* @return array
	*/
	static function mcFoundation_builder_taxonomy_term_select($field) {
		$taxonomies = get_taxonomies(array('public' => true), 'objects');
		$excludeTax = apply_filters('mcFoundation_builder_taxonomy_term_select_exclude', array('post_format'), $field);
		
		foreach( $taxonomies as $k => $tax ) {
			if( in_array($k, $excludeTax) ) {
				unset($taxonomies[$k]);
			}
		}
		$terms = get_terms(array_keys($taxonomies), array(
			'hide_empty' => false
		));
		
		foreach( $terms as $term ) {
			$field['choices'][$taxonomies[$term->taxonomy]->labels->name][$term->taxonomy.'|'.$term->term_id] = $term->name;
		}
		
		return $field;
	}//mcFoundation_builder_taxonomy_term_select()
	
	/**
	* REPLACE POST CONTENT
	* Hooks into pre_get_posts to replace the post content
	*
	* @param obj $query The WP_Query object
	* @return void
	*/
	static function mcFoundation_pre_get_posts($query) {
		if( !$query->is_main_query() || $query->is_admin() ) return;
		if( !$query->is_page() && 'template-pagebuilder' != get_page_template_slug($query->queried_object_id) ) return;
		
		$pageID = !$query->queried_object_id ? (int)$query->query_vars['page_id'] : $query->queried_object_id;
		
		$pb = new PageBuilder(null, $pageID);
		
		//add_action('the_post', array('PageBuilder', 'mcFoundation_builder_the_post') );
	}//mcFoundation_pre_get_posts
	
	static function mcFoundation_builder_the_post($post) {
		global $pageBuilder;
		if( $post->ID !== $pageBuilder->post->ID ) return;
		$post->post_content = $pageBuilder->getContent();
	}//mcFoundation_builder_the_post()
	
	/**
	* PAGE BUILDER OPEN GRAPH
	* Add hero images as top-priority Open Graph images
	*
	* @internal Hooked to priority 29, to immediately precede default WP-SEO output.
	*
	* @return void
	*/
	static function mcFoundation_builder_opengraph() {
		global $pageBuilder;
		$post = $pageBuilder->post;
		if( 'template-pagebuilder.php' !== get_page_template_slug($post->ID) ) return;
		
		//Hero Images
		if( $pageBuilder->builderHasModule('column_hero_image') ) {
			//Get the first hero image
			$moduleIndex = explode('-', $pageBuilder->moduleNames['column_hero_image'][0]);
			$module = $pageBuilder->builderGetModuleByIndex($moduleIndex[0], $moduleIndex[1]);
			
			$imageIds = array();
			if( $module['hero_type'] != 'slideshow' ) {
				$imageIds[] = $module['background']['ID'];
			}
			else {
				foreach( $module['background_images'] as $background ) {
					$imageIds[] = $background['ID'];
				}
			}
			
			foreach($imageIds as $imgID ) {
				$src = wp_get_attachment_image_src($imgID, 'mc_600', false);
				echo '<meta property="og:image" content="'.$src[0].'" />', "\n";
			}
		}
	}//mFoundation_builder_opengraph()
	
	/**
	* Facebook JS SDK
	*
	* @return void
	*/
	static function mcFoundation_builder_facebook_js_sdk() {
	?>
	<div id="fb-root"></div>
	<script>(function(d, s, id) {
	  var js, fjs = d.getElementsByTagName(s)[0];
	  if (d.getElementById(id)) return;
	  js = d.createElement(s); js.id = id;
	  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.5&appId=206743626047147";
	  fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));</script>
	<?php
	}//mcFoundation_builder_facebook_js_sdk()
	
}//PageBuilder Class

/***********************************************************************
* !PAGEBUILDER DEFAULT ACTIONS
/***********************************************************************/
//Attach PageBuilder to the post content
add_action('pre_get_posts', array('PageBuilder', 'mcFoundation_pre_get_posts'));

//Add hero images as Open Graph default images
add_action('wpseo_opengraph', array('PageBuilder', 'mcFoundation_builder_opengraph'), 29);

//Register the custom modules from the templates directory
add_filter('acf/load_field/name=custom_module', array('PageBuilder', 'builderCustomModuleTemplates'));

//Add WP Menus to navigation select field
add_filter('acf/load_field/key=field_55f8478f346db', array('PageBuilder', 'mcFoundation_builder_navigation_select'));

//Remove attachments post type from post_object query fields
add_filter('acf/fields/post_object/query/', array('PageBuilder', 'mcFoundation_builder_remove_attachments_cpt'), 10, 3);

//Load FontAwesome icons select field
add_filter('acf/load_field/name=icon', array('PageBuilder', 'mcFoundation_builder_fa_icon_select'));

//Load category/taxonomy term selections
add_filter('acf/load_field/key=field_566af7756ae39', array('PageBuilder', 'mcFoundation_builder_taxonomy_term_select'));
