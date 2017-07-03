<?php
$envConf = array(
	'img_url' => 'http://localhost/',
    'fe_url' => 'http://localhost/',
	'adm_url' => 'http://localhost/',
    'send_email' => true,
    'test_email' => '', // will send to this email for testing   
    'api_check_security' => true,
);

if (isset($_SERVER['SERVER_NAME'])) {
    if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . $_SERVER['SERVER_NAME'] . '.php')) {
        include_once (__DIR__ . DIRECTORY_SEPARATOR . $_SERVER['SERVER_NAME'] . '.php');
        $envConf = array_merge($envConf, $domainConf);
    }
}
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'db.read.php')) {
    include_once (__DIR__ . DIRECTORY_SEPARATOR . 'db.read.php');
    $envConf = array_merge($envConf, $dbReadConf);
}
return $envConf;