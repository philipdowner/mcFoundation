jQuery(document).foundation({
	interchange: {
		named_queries: {
			'mc_600': 'only screen and (max-width: 600px)',
			'mc_400': 'only screen and (max-width: 400px)'
		}
	},
	'magellan-expedition': {
		fixed_top:45,
		destination_threshold:5
	}
});
jQuery(document).ready(function($) {
	/***********************************************************************
	* !MANIFEST FRAMEWORK
	/***********************************************************************/
	var MC = {
		PageBuilder: {
			elements: {
				pb: $('#pageBuilder'),
				header: $('header#primary'),
				rows: {}
			},
			
			/** PageBuilder Modules **/
			modules: {}
		},
	
		init: function() {
			// Joyride init
			$('#start-jr').on('click', function() {
			  $(document).foundation('joyride','start');
			});
			
			//SlickJS init
			$('.slickSlides').slick();
			
			//PageBuilder init
			if( MC.PageBuilder.elements.pb.length ) {
				MC.PageBuilder.init();
			}
		}
	};
	
	/*************************
	* PAGEBUILDER INIT
	**************************/
	MC.PageBuilder.init = function() {
		
		/** Set PageBuilder Rows **/
		this.elements.rows = this.elements.pb.find('.row');
		
		//Initialize each module
		$.each(this.modules, function(i, el) {
			if( MC.PageBuilder.modules[i] !== undefined ) {
				MC.PageBuilder.modules[i]();
			}
		});
	};//MC.PageBuilder.init()
	
	/*************************
	* MODULE UTILITIES
	**************************/
	/**
	* IS FIRST MODULE
	*
	* @param obj module jQuery object to compare first module to
	* @return bool
	*/
	MC.PageBuilder.modules.isFirstModule = function(module) {
		if( !module ) return;
		
		var firstModule = $(MC.PageBuilder.elements.rows[0]).find('.module:first-child');
		
		return firstModule[0] == module[0];
	};
	
	/*************************
	* MODULE - HERO IMAGE
	**************************/
	MC.PageBuilder.modules.heroImg = function() {
		
		//All hero image elements
		this.elements = MC.PageBuilder.elements.pb.find('[data-herobg]');
		
		//Static, global dimensions such as window and header#primary
		this.dimensions = {
			header: {
				height: $(MC.PageBuilder.elements.header).outerHeight(true)
			}
		};
		
		/**
		* MODULE
		* Logic for each individual module
		*
		* @param int i The index of the module on the page
		* @param obj el The parent module element
		* @return void
		*/
		this.Module = function(i, el) {
			
			this.parentID = $(el).parent('.module').attr('id');
			this.index = i;
			this.el = el;
			this.$el = $(el);
			this.slides = this.$el.find('.slide');
			
			/**
			* GET HERO DATA
			* Creates the settings object for the current hero image
			*
			* @return obj
			*/
			this.getHeroData = function() {
				return this.$el.data('herobg');
			};
			
			/**
			* SET DIMENSIONS
			* Creates the dimensions object for the current hero image
			*
			* @param obj dimensions
			* @return void
			*/
			this.setDimensions = function(dimensions) {
				
				var that = this;
				
				//Reset the object
				this.dimensions = {};
				
				//Elements to get sizes for
				var elements = {
					contentWrap: this.$el.find('.contentWrap'),
					slideshow: this.$el.find('.heroSlideshow')
				};
				
				//Get the dimensions for each element
				$.each(elements, function(i, el) {
					that.dimensions[i] = {
						width: $(el).width(),
						height: $(el).height()
					};
				});
				
				//Merge any passed dimensions
				if( dimensions ) {
					this.dimensions = $.extend(this.dimensions, dimensions);
				}
				
				//Whenever the dimensions are calculated the layout should be adjusted
				this.setLayout();
			};
			
			/**
			* SET LAYOUT
			* Positions the layout elements
			*
			* @return void
			*/
			this.setLayout = function() {
				var settings = this.settings;
				var dimensions = this.dimensions;
				
				//Get the current offset of the first PageBuilder element
				var pbOffset = $(MC.PageBuilder.elements.pb).offset();
				
				//Determine whether the content can fit over the slideshow
				var contentFits = settings.header_adjust ? dimensions.contentWrap.height < dimensions.slideshow.height - pbOffset.top : dimensions.contentWrap.height < dimensions.slideshow.height;
				
				//No matter the positioning, the content always overlays on medium and larger screens
				if( Foundation.utils.is_medium_up() && contentFits ) {
		
					//Set the wrapper height to avoid overflow
					this.$el.css({
						height: 'auto'
					});
					
					//Adjust the image to sit behind the header
					if( settings.header_adjust ) {
						var headerEl = MC.PageBuilder.elements.header;
						var headerZIndex = $(headerEl).css('z-index');
						if( headerZIndex == 'auto' ) {
							headerZIndex = 2;
							$(headerEl).css({
								position: 'relative',
								zIndex: headerZIndex
							});
						}
						
						$(this.$el).css({
							marginTop: pbOffset.top * -1,
							zIndex: 1
						});
					}
					
					//Set the positioning based on UI settings
					var position = {};
					
					switch(settings.location) {
						case 'upperleft':
						case 'upperright':
							position.contentWrap = {
								top: settings.header_adjust ? pbOffset.top : 0
							};
						break;
						
						case 'center':
							position.contentWrap = {
								top: Math.ceil((dimensions.slideshow.height/2) - (dimensions.contentWrap.height/2))
							};
						break;
						
						default:
							position.contentWrap = {
								top: Math.floor(dimensions.slideshow.height - dimensions.contentWrap.height)
							};
						break;
					}
					
					//Alwaus use absolute positioning
					position.contentWrap.position = 'absolute';
					
					//Move the content into position
					this.$el.find('.contentWrap').css(position.contentWrap);
					
					//Add an overlay class
					this.$el.removeClass('contentSmall');
					this.$el.addClass('contentLarge');
				}
				
				//Small screen positioning
				else {
					this.$el.css({
						height: Math.ceil(dimensions.slideshow.height + dimensions.contentWrap.height),
						overflow: 'visible'
					});
					this.$el.find('.contentWrap').css({
						top:0,
						position: 'relative'
					});
					
					//Add an overlay class
					this.$el.removeClass('contentLarge');
					this.$el.addClass('contentSmall');
				}
			};//setLayout()
			
			/**
			* INITIALIZE SLICK SLIDESHOW
			*
			* @return void
			*/
			this.initSlideshow = function() {
				this.slideshow = this.$el.children('.heroSlideshow');
				$(this.slideshow).slick({
					autoplay: true,
					autoplaySpeed: 3000,
					arrows: false,
					fade: true,
					slide: '.slide',
					pauseOnHover: false
				});
			};
			
			/** MODULE INIT **/
			this.init = function() {
				
				var that = this;
				this.settings = this.getHeroData();
				
				//Header adjustment is only available for the first row/module
				if( this.settings.header_adjust && !MC.PageBuilder.modules.isFirstModule(this.$el.parent('.module')) ) {
					this.settings.header_adjust = false;
				}
				
				//Set dimensions on Interchange replace
				$(document).on('replace', '#' + this.parentID + ' .slide:first-child img', function(e, new_path, original_path) {
					Foundation.utils.image_loaded($(new_path.el), function() {
						that.setDimensions({
		 					slideshow: {
		 						height:$(new_path.el).height()
		 					}
		 				});
					});
				});
				
				//Set dimensions on window resize
				$(window).on('resize', function() {
					that.setDimensions();
				});
				
				//Init Slideshow
				if( this.settings.type == 'slideshow' ) {
					this.initSlideshow();
				}
			};//init()
			this.init();
		};//module()
		
		/**
		* HERO IMAGE INIT
		*   
		* @return void
		*/
		this.init = function() {
			var that = this;
			
			//Loop thru each hero image module
			$.each(this.elements, function(i, el) {
				new that.Module(i, el);
			});//each()
			
		};//init()
		this.init();
	};//MC.PageBuilder.modules.heroImg
	
	//MC INIT
	MC.init();	
});