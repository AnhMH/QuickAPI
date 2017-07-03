<?php

return array(
    'apns' => array(
        'local_cert'     => APPPATH . 'vendor/pem/final.pem',
        'local_cert_dev' => APPPATH . 'vendor/pem/development.pem',
        'expiry'         => 30,
        'debug_log'      => false,
    ),
    'gcm' => array(
        'authorization_key' => 'AIzaSyCW05_iUxdsHCQv1etRg3qM0laUDaGauPk',
        'timeout'           => 30,
    )
);
