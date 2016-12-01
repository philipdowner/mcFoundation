<?php
do_action('mcFoundation_before_sidebar');
echo '<ul class="widgetList">';
mcFoundation_get_sidebar();
echo '</ul>';
do_action('mcFoundation_after_sidebar');