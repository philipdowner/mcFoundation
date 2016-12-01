<html>
	<head>
		<title><?php echo $headline; ?></title>
		<?php
			$css = file_get_contents(CSS_DIR.'/email.min.css');
			echo '<style type="text/css">'.$css.'</style>';
		?>
	</head>
	<body>
	<?php
		//It's possible that this should be moved inside the class as a method
		$wrapperClasses = array('email', 'mcMail', 'msgId-'.$mailObj->ID);
		$wrapperClasses[] = $mailObj->getTemplate() ? 'template-'.$mailObj->getTemplate() : '';
		echo '<div id="wrapper" class="'.implode(' ', $wrapperClasses).'">';
	?>
	<table id="primary" width="100%">