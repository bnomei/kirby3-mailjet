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
        'sms.from' => '{{ user.name }}',
        'sms.maxlength' => 140,
        'sms.cooldown' => 2500,
        'cache' => true,
        'expires' => 1, // minutes
        'log.enabled' => false,
        'log' => function (string $msg, string $level = 'info', array $context = []): bool {
            if (option('bnomei.mailjet.log.enabled')) {
                if (function_exists('monolog')) {
                    monolog()->{$level}($msg, $context);
                } elseif (function_exists('kirbyLog')) {
                    kirbyLog('bnomei.janitor.log')->log($msg, $level, $context);
                }
                return true;
            }
            return false;
        },
    ],
    'collections' => [
        'mailjetContactslists' => function () {
            return array_map(
                function ($item) {
                    return new \Kirby\Toolkit\Obj($item);
                },
                \Bnomei\Mailjet::singleton()->contactslists()
            );
        },
        'mailjetSegments' => function () {
            return array_map(
                function ($item) {
                    return new \Kirby\Toolkit\Obj($item);
                },
                \Bnomei\Mailjet::singleton()->segments()
            );
        },
    ],
    'siteMethods' => [
        'mailjetContactslists' => function () {
            return kirby()->collection('mailjetContactslists');
        },
        'mailjetSegments' => function () {
            return kirby()->collection('mailjetSegments');
        },
    ],
    'translations' => [
        'en' => require_once 'src/i18n/en.php',
        'de' => require_once 'src/i18n/de.php',
    ],
    'api' => [
        'routes' => [
            [
                'pattern' => 'mailjet/schedule/list',
                'action' => function () {
                    return mailjet()->scheduledCampaignDrafts();
                },
            ],
            [
                'pattern' => 'mailjet/sms/config',
                'action' => function () {
                    return [
                        'from' => \Kirby\Toolkit\Str::template(
                            \option('bnomei.mailjet.sms.from'),
                            [
                                'kirby' => kirby(),
                                'site' => kirby()->site(),
                                'user' => kirby()->user(),
                            ]
                        ),
                        'maxlength' => \option('bnomei.mailjet.sms.maxlength'),
                        'cooldown' => \option('bnomei.mailjet.sms.cooldown'),
                    ];
                },
            ],
            [
                'pattern' => 'mailjet/sms/send',
                'method' => 'POST',
                'action' => function () {
                    $data = kirby()->request()->data();
                    $from = trim(str_replace(' ', '',
                        \Kirby\Toolkit\A::get($data, 'from' ,'')
                    ));
                    $to = trim(str_replace(' ', '',
                        \Kirby\Toolkit\A::get($data, 'to' ,'')
                    ));
                    $message = \Kirby\Toolkit\A::get($data, 'message');
                    $success = mailjet()->sendSMS($from, $to, $message);

                    return [
                        'statusCode' => $success ? 200 : 204,
                    ];
                },
            ],
        ],
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
