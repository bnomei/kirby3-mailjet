<?php

@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('bnomei/mailjet', [
    'options' => [
        'apikey' => null, // or callback
        'apisecret' => null, // or callback
        'smstoken' => null, // or callback
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
        ],
        'cache' => true,
        'expires' => 1, // minutes
    ],
    'siteMethods' => [
        'mailjetContactslists' => function() {
            return \Bnomei\Mailjet::singleton()->contactslists();
        },
        'mailjetSegments' => function() {
            return \Bnomei\Mailjet::singleton()->segments();
        },
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
