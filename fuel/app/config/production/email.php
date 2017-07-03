<?php

return array(
    'defaults' => array(
        'phpmailer' => array(
            'Mailer' => 'smtp',
            'SMTPAuth' => true,
            'SMTPSecure ' => 'ssl',
            'Host' => 'ssl://email-smtp.us-west-2.amazonaws.com',
            'Port' => 465,
            'Username' => '',
            'Password' => '',
            'Timeout' => 30, // 30 seconds
        ),
        'wordwrap' => 0
    )
);
