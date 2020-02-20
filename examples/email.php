<?php

// regular "transactional" email
$to = 'roger.rabbit@disney.com';

// Mailjets "magic" Contactlist-E-Mail will trigger a Newsletter/Campaign
$to = 'PLACEHOLDER@lists.mailjet.com'; //

$success = kirby()->email([
    'from' => 'b@bnomei.com', // your verified mailjet sender
    'to' => $to,
    'subject' => 'Sending E-Mails is fun',
    'body' => [
        'html' => '<h1>Headline</h1><p>Text</p>',
        'text' => "Headline\n\nText",
    ],
    'transport' => mailjet()->transport(),
])->isSent();
