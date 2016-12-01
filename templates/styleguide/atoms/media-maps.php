<div class="row">
<div class="small-12 columns">
		<?php
			$address = str_replace(array("\r", "\n"), '', get_theme_option('option_physical_address'));
		?>
		<script type="text/javascript">
			var sgMap;
			function initSgMap() {
			  var location = {
				  lat: <?php echo $block->location->lat; ?>,
				  lng: <?php echo $block->location->lng; ?>
			  };
			  
			  sgMap = new google.maps.Map(document.getElementById('styleGuideMapCanvas'), {
				  center: location,
				  zoom: 15
			  });
			  
			  var marker = new google.maps.Marker({
				  position: location,
				  title: "Manifest Creative office",
				  map: sgMap
			  });
			  
			  var contentString = '<div class="infoWindowContent"><h5>Manifest Creative, LLC</h5><p>';
			  contentString += "<?php echo $address; ?>";
			  contentString += '</p></div>';
			  var infoWindow = new google.maps.InfoWindow({
				  content: contentString
			  });
			  
			  marker.addListener('click', function() {
				  infoWindow.open(sgMap, marker);
			  });
			}
		</script>
		<script async defer
      src="https://maps.googleapis.com/maps/api/js?key=&callback=initSgMap">
    </script>
	<div id="styleGuideMapCanvas" class="gMap mapCanvas"></div>
</div>
</div>