<?php

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('bnomei/mailjet', [
    'options' => [
        'apikey' => null, // or callback
        'apisecret' => null, // or callback
        'email' => [
            'transport' => [
                'type' => 'smtp',
                'host' => 'in-v3.mailjet.com',
                'port' => 587,
                'security' => 'tsl',
                'auth' => true,
//                'username' => null, // will default to apikey
//                'password' => null, // will default to apisecret
            ]
        ]
    ],
  ]);

if (!class_exists('Bnomei\Mailjet')) {
    require_once __DIR__ . '/classes/Mailjet.php';
}

if (!function_exists('mailjet')) {
    function mailjet()
    {
        return \Bnomei\Mailjet::singleton();
    }
}
