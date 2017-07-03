<?php
Autoloader::add_core_namespace( 'Twitter' );
Autoloader::add_classes( array(
	'Social\\Twitter'		=> __DIR__.'/classes/twitter.php',
	'Social\\TwitterOAuth'	=> __DIR__.'/vendor/twitter/twitteroauth.php',
));