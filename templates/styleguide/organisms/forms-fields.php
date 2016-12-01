<div class="row">
	<div class="small-12 medium-6 columns">
		<h4 class="elementHeader">Standard Fields</h4>
		<form>
		  <fieldset>
		    <legend>Fieldset</legend>
		
		    <div class="row">
		      <div class="small-12 columns input-wrapper">
		        <label>Field Label <span class="required">*</span></label>
		        <input type="text" placeholder="Placeholder text" required="true">
		        <small class="description">A description can be added to fields to make it more apparent what information you require.</small>
		      </div>
		    </div>
		    
		    <div class="row">
		    	<div class="small-12 columns input-wrapper">
			    	<label>Pre-filled value</label>
				    <input type="text" value="User Provided Input" />
			    </div>
		    </div>
		    
		    <div class="row">
		    	<div class="small-12 columns input-wrapper">
			    	<label>Read-only value</label>
				    <input type="text" value="https://manifestbozeman.com" readonly />
			    </div>
		    </div>
		
		    <div class="row">
		      <div class="small-12 medium-6 columns input-wrapper">
		        <label>Input Label</label>
		        <input type="text" placeholder="">
		      </div>
		      <div class="small-12 medium-6 columns input-wrapper">
		        <label>Input Label</label>
		        <input type="text" placeholder="">
		      </div>
		    </div>
		    <div class="row">
		      <div class="small-12 columns input-wrapper">
		        <label>Input Label</label>
		        <div class="row collapse">
		          <div class="small-9 columns">
		            <input type="text" placeholder="Postfix example">
		          </div>
		          <div class="small-3 columns">
		            <span class="postfix">.com</span>
		          </div>
		        </div>
		        <small class="description">A postfix field has something appended.</small>
		      </div>
		    </div>
		
		    <div class="row">
		      <div class="large-12 columns input-wrapper">
		        <label>Textarea Label</label>
		        <textarea placeholder="" rows="5">This is an example of a textarea field. It is useful when you need a user to be able to input a longer amount of text. Go ahead, type something!</textarea>
		      </div>
		    </div>
		  </fieldset>
		  
		  <fieldset>
		  	<legend>Select Fields</legend>
		  		<div class="row">
		  			<div class="small-12 columns input-wrapper">
			  			<label for="exampleSelect">Single Selection <span class="required">*</span></label>
			  			<select name="exampleSelect" id="exampleSelect">
				  			<option value="1" selected>Choose one&hellip;</option>
				  			<option value="2">Option Two</option>
				  			<option value="3">Option Three</option>
				  			<option value="4">Option Four</option>
			  			</select>
		  			</div>
		  		</div>
		  		
		  		<div class="row">
		  			<div class="small-12 columns input-wrapper">
			  			<label for="exampleSelectOptions">Option Groups</label>
			  			<select name="exampleSelectOptions" id="exampleSelectOptions">
				  			<option value="1" selected>Choose one&hellip;</option>
				  			<optgroup label="Group 1">
							    <option>Option 1.1</option>
							  </optgroup> 
							  <optgroup label="Group 2">
							    <option>Option 2.1</option>
							    <option>Option 2.2</option>
							  </optgroup>
							  <optgroup label="Group 3 (disabled)" disabled>
							    <option>Option 3.1</option>
							    <option>Option 3.2</option>
							    <option>Option 3.3</option>
							  </optgroup>
			  			</select>
		  			</div>
		  		</div>
		  		
		  		<div class="row">
		  			<div class="small-12 columns input-wrapper">
			  			<label for="exampleSelectMultiple">Multiple Selection</label>
			  			<select name="exampleSelectMultiple" id="exampleSelectMultiple" multiple="true">
				  			<optgroup label="Marketing">
							    <option>Print</option>
							    <option>Web</option>
							    <option>Social Media</option>
							  </optgroup> 
							  <optgroup label="Industry">
							    <option>Aviation</option>
							    <option>Construction</option>
							    <option>Hospitality</option>
							  </optgroup>
							  <optgroup label="Interests (disabled)" disabled>
							    <option>Fishing</option>
							    <option>Kayaking</option>
							    <option>Snowmobiling</option>
							  </optgroup>
			  			</select>
			  			<small class="description">To select more than one, click holding the Control key (Windows) or the Command key (Macintosh).</small>
		  			</div>
		  		</div>
		  </fieldset>
		  
		  <fieldset>
		  	<legend>Checkboxes and Radio Buttons</legend>
		  	<div class="row">
		  		<div class="small-12 medium-6 columns input-wrapper">
			  		<label>Checkboxes</label>
			  		<ul class="inputGroup checkboxGroup">
				  		<?php
					  	$checked = rand(0,6);
				  		for( $i=0; $i < 6; $i++ ) {
					  		echo '<li><input type="checkbox" name="checkboxExample['.$i.']" id="checkboxExample['.$i.']" '.checked($checked, $i, false).' />';
					  		echo '<label for="checkboxExample['.$i.']" class="inline">Checkbox #'.($i + 1).'</label></li>';
				  		}
				  		?>
			  		</ul>
		  		</div>
		  		<div class="small-12 medium-6 columns input-wrapper">
			  		<label>Radio Buttons</label>
			  		<ul class="inputGroup radioGroup">
				  		<?php
				  		for( $i=0; $i < 6; $i++ ) {
					  		echo '<li><input type="radio" name="radioExample[]" id="radioExample-'.$i.'" '.checked($checked, $i, false).' />';
					  		echo '<label for="radioExample-'.$i.'" class="inline">Radio Button #'.($i + 1).'</label></li>';
				  		}
				  		?>
			  		</ul>
		  		</div>
		  	</div>
		  </fieldset>
		  
		  <fieldset>
		  	<legend>HTML5 Fields</legend>
		  	
		  	<div class="row">
		  		<div class="small-12 columns">
		  			<div class="panel">
		  			<p>Each user's web browser is responsible for how these following fields will display and function. This helps to ensure that they are fully functional both on desktop computers and in mobile browsers.</p><p>Try them out in <a href="https://browsehappy.com" target="_blank">a few different browsers</a> and see if you can spot the differences.</p>
		  			</div>
		  		</div>
		  	</div>
		  	
		  	<div class="row">
		  		<div class="small-12 columns input-wrapper">
			  		<label>Color Picker</label>
			  		<input type="color" value="#efefef" />
		  		</div>
		  	</div>
		  	
		  	<div class="row">
		  		<div class="small-12 medium-6 columns input-wrapper">
			  		<label>Date</label>
			  		<input type="date" />
		  		</div>
		  		<div class="small-12 medium-6 columns input-wrapper">
			  		<label>Datetime</label>
			  		<input type="datetime-local" />
		  		</div>
		  	</div>
		  	
		  	<div class="row">
		  		<div class="small-12 columns input-wrapper">
			  		<label>Password</label>
			  		<input type="password" />
		  		</div>
		  	</div>
		  	
		  	<div class="row">
		  		<div class="small-12 columns input-wrapper">
			  		<label>Range</label>
			  		<input type="range" min="1" max="100" step="10" />
		  		</div>
		  	</div>
		  </fieldset>
		  
		  <fieldset>
		  	<legend>Pre/Postfix Labels & Actions</legend>
		  	  <div class="row collapse">
				    <div class="small-3 large-2 columns">
				      <span class="prefix">http://</span>
				    </div>
				    <div class="small-9 large-10 columns input-wrapper">
				      <input type="text" placeholder="Enter your URL...">
				    </div>
				  </div>
				  <div class="row">
				    <div class="large-12 columns">
				      <div class="row collapse">
				        <div class="small-10 columns input-wrapper">
				          <input type="text" placeholder="Hex Value">
				        </div>
				        <div class="small-2 columns">
				          <a href="#" class="button postfix">Go</a>
				        </div>
				      </div>
				    </div>
				  </div>
				  <div class="row">
				    <div class="large-6 columns">
				      <div class="row collapse prefix-radius">
				        <div class="small-3 columns">
				          <span class="prefix">Label</span>
				        </div>
				        <div class="small-9 columns input-wrapper">
				          <input type="text" placeholder="Value">
				        </div>
				      </div>
				    </div>
				    <div class="large-6 columns">
				      <div class="row collapse postfix-radius">
				        <div class="small-9 columns input-wrapper">
				          <input type="text" placeholder="Value">
				        </div>
				        <div class="small-3 columns">
				          <span class="postfix">Label</span>
				        </div>
				      </div>
				    </div>
				  </div>
				  <div class="row">
				    <div class="large-6 columns">
				      <div class="row collapse prefix-round">
				        <div class="small-3 columns">
				          <a href="#" class="button prefix">Go</a>
				        </div>
				        <div class="small-9 columns input-wrapper">
				          <input type="text" placeholder="Value">
				        </div>
				      </div>
				    </div>
				    <div class="large-6 columns">
				      <div class="row collapse postfix-round">
				        <div class="small-9 columns input-wrapper">
				          <input type="text" placeholder="Value">
				        </div>
				        <div class="small-3 columns">
				          <a href="#" class="button postfix">Go</a>
				        </div>
				      </div>
				    </div>
				  </div>
		  </fieldset>
		  
		  <fieldset>
		  	<legend>Error States</legend>
		  	<div class="row">
			    <div class="large-6 columns error input-wrapper">
			      <label class="error">Error
			        <input type="text" class="error" />
			      </label>
			      <small class="error">Invalid entry</small>
			    </div>
			    
			    <div class="large-6 columns input-wrapper">
			      <label class="error">Another Error
			        <input type="text" class="error"/>
			      </label>
			      <small class="error">Invalid entry</small>
			    </div>
			  </div>
			  
			  <div class="row">
	  			<div class="small-12 columns input-wrapper error">
		  			<label for="exampleSelect">Select Field<span class="required">*</span></label>
		  			<select name="exampleSelect" id="exampleSelect">
			  			<option value="1" selected>Choose one&hellip;</option>
			  			<option value="2">Option Two</option>
			  			<option value="3">Option Three</option>
			  			<option value="4">Option Four</option>
		  			</select>
		  			<small class="error">Please choose an option</small>
	  			</div>
	  		</div>
			  
			  <textarea class="error" placeholder="Message..."></textarea>
			  <small class="error">Please write a message.</small>
			  <small class="description">Field description is still visible.</small>
		  </fieldset>
		</form>
	</div>
	
	<div class="small-12 medium-6 columns">
		<h4 class="elementHeader">Gravity Forms Plugin</h4>
		<?php echo apply_filters('the_content', '[gravityform id="3" title="true" description="true" ajax="true"]'); ?>
	</div>
</div>
