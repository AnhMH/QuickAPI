<?php 
return array(	
	'upload_dir' => '/var/www/upload/',
	'img_dir' => '/var/www/img/',
	'path' => '/var/www/img/' . date('Y/m/d') . '/',
	'auto_process' => false,
	'normalize' => true,
	'change_case' => 'lower',
	'randomize' => true,
	'ext_whitelist' => array('jpeg', 'jpg', 'gif', 'png', 'mp4', 'flv'),
);