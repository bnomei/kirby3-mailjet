<?php

// regular "transactional" email
$to = 'roger.rabbit@disney.com';

// or Mailjets "magic" Contactlist-E-Mail will trigger a Newsletter/Campaign
$to = 'PLACEHOLDER@lists.mailjet.com'; //

$success = kirby()->email([
    'from' => new \Kirby\Cms\User([
        'email' => 'mailjet@example.com', // your verified mailjet sender
        'name' => 'Example Name', // your name
    ]),
    'to' => $to,
    'subject' => 'Sending E-Mails is fun',
    'body' => [
        'html' => '<h1>Headline</h1><p>Text</p>',
        'text' => "Headline\n\nText",
    ],
    'transport' => mailjet()->transport(),
])->isSent();
