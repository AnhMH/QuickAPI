<?php
$envConf = array(
    'img_url' => 'http://img.quick.localhost/',
    'send_email' => true,
    'test_email' => '', // will send to this email for testing  
    'twitter' => array(
        'key' => 'diXip9KkGrJ0QGhRaxXMNtPve',
        'secret' => 'ugZG8bPb2oiDZMPU6XbdvLozCy5Di4ZYZCaKEsxHJWzyhroljk'
    ),
    'facebook' => array(
        'app_id' => '145671709338491',
        'app_secret' => 'f72e15e23688a11dd707ab50f705370c',
    ),
    'api_check_security' => false,
    'api_request_minute' => 24 * 60,
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