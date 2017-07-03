<?php 
return array(	
	'upload_dir' => realpath('../../' . dirname(basename(__FILE__))) . DS . 'upload' . DS,
	'img_dir' => realpath('../../' . dirname(basename(__FILE__))) . DS . 'upload' . DS . 'img' . DS,
	'path' => realpath('../../' . dirname(basename(__FILE__))) . DS . 'upload' . DS . 'img' . DS . date('Y' . DS . 'm' . DS . 'd') . DS,
	'auto_process' => false,
	'normalize' => true,
	'change_case' => 'lower',
	'randomize' => true,	
	'ext_whitelist' => array('jpeg', 'jpg', 'gif', 'png', 'mp4', 'flv'),
    'max_size' => 10 * 1024 * 1024 // 10Mb
);