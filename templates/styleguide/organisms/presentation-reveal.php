<div class="row">
	<div class="small-6 columns">
		<p><a href="#" data-reveal-id="firstModal" class="radius button">Example Modal&hellip;</a>
		<a href="#" data-reveal-id="videoModal" class="radius button">Full-Width Modal w/Video&hellip;</a></p>
		<!-- Reveal Modals begin -->
		<div id="firstModal" class="reveal-modal" data-reveal>
		  <h3>This is a modal.</h3>
		  <p>A modal window is very easy to summon and dismiss. The close button is displayed in the upper right corner. Clicking anywhere outside the modal will also dismiss it.</p>
		  <p>Your modal window can even trigger additional modals and they will be handled gracefully!</p>
		  <p><a href="#" data-reveal-id="secondModal" class="secondary button">Login</a></p>
		  <a class="close-reveal-modal">&#215;</a>
		</div>
		
		<div id="secondModal" class="reveal-modal small" data-reveal>
		  <h2>This is a second modal.</h2>
		  <form action="">
			  <fieldset>
			  	<legend>Example Login Form</legend>
			  	<div class="row">
			  		<div class="small-12 columns"><label for="">Username</label>
				  		<input type="email" /></div>
			  	</div>
			  	<div class="row">
			  		<div class="small-12 columns"><label for="">Password</label><input type="password" /></div>
			  	</div>
			  	<div class="row">
			  		<div class="small-12 columns"><input type="submit" value="Login" class="button radius expand" /></div>
			  	</div>
			  </fieldset>
		  </form>
		  <a class="close-reveal-modal">&#215;</a>
		</div>
	</div>
	
	<div class="small-6 columns">
		<div id="videoModal" class="reveal-modal full" data-reveal>
		  <h3>This modal has video</h3>
		  <div class="flex-video">
		          <iframe width="420" height="315" src="//www.youtube.com/embed/aiBt44rrslw" frameborder="0" allowfullscreen></iframe>
		  </div>
		
		  <a class="close-reveal-modal">&#215;</a>
		</div>
	</div>
</div>