<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.7
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

Autoloader::add_core_namespace('Facebook');
Autoloader::add_classes(array(
	'Facebook\\FacebookSession' => __DIR__ . '/src/Facebook/FacebookSession.php',		
	'Facebook\\FacebookRequest' => __DIR__ . '/src/Facebook/FacebookRequest.php',		
	'Facebook\\FacebookResponse' => __DIR__ . '/src/Facebook/FacebookResponse.php',
	'Facebook\\FacebookRequestException' => __DIR__ . '/src/Facebook/FacebookRequestException.php',		
	'Facebook\\Entities\\AccessToken' => __DIR__ . '/src/Facebook/Entities/AccessToken.php',		
	'Facebook\\FacebookRedirectLoginHelper' => __DIR__ . '/src/Facebook/FacebookRedirectLoginHelper.php',		
	'Facebook\\FacebookSDKException' => __DIR__ . '/src/Facebook/FacebookSDKException.php',		
	'Facebook\\FacebookRequestException' => __DIR__ . '/src/Facebook/FacebookRequestException.php',		
	'Facebook\\FacebookAuthorizationException' => __DIR__ . '/src/Facebook/FacebookAuthorizationException.php',	
	'Facebook\\HttpClients\\FacebookCurlHttpClient' => __DIR__ . '/src/Facebook/HttpClients/FacebookCurlHttpClient.php',	
	'Facebook\\HttpClients\\FacebookHttpable' => __DIR__ . '/src/Facebook/HttpClients/FacebookHttpable.php',	
	'Facebook\\HttpClients\\FacebookCurl' => __DIR__ . '/src/Facebook/HttpClients/FacebookCurl.php',	
	'Facebook\\GraphObject' => __DIR__ . '/src/Facebook/GraphObject.php',
	'Facebook\\GraphUser' => __DIR__ . '/src/Facebook/GraphUser.php',			
)); 