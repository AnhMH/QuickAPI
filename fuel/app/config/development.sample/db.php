<?php

/**
 * The production database settings. These get merged with the global settings.
 */
return array(
    'default' => array(
        'connection' => array(
            'dsn' => 'mysql:host=localhost:3306;dbname=quick',
            'username' => 'root',
            'password' => 'root',
        ),
        'timezone' => '+9:00',
    ),
    /*'replica1' => array(
	  	'connection' => array(
	    	'dsn' => 'mysql:host=localhost:3306;dbname=campusan',
	    	'username' => 'campusan',
	    	'password' => 'campusan',
  		),
  		'timezone' => '+9:00',
        'type'         => 'pdo',
        'identifier'   => '`',
        'table_prefix' => '',
        'charset'      => 'utf8',
        'enable_cache' => true,
        'profiling'    => false,
        'readonly'     => false,
	),*/
);
