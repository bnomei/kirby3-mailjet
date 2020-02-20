<?php

$email = 'client@example.com';
$listID = mailjet()->contactslist('My Newsletter'); // int
$force = false;

mailjet()->subscribeToContactslist($email, $listID, [
    'Name' => get('name')
], $force);
mailjet()->unsubscribeFromContactslist($email, $listID);
mailjet()->removeFromContactslist($email, $listID);

mailjet()->excludeContactFromAllCampaigns($email);
