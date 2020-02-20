<?php
$senderemail = "b@bnomei.com";
$draft = \mailjet()->campaignDraft();
$draft
    ->setLocale('en_EN')
    ->setSender(
        \mailjet()->sender($senderemail)
    )
    ->setSenderemail($senderemail)
    ->setSubject('A fluent newsletter tool')
    ->setContactslist(
        \mailjet()->contactslist('TEST')
    )
    ->setSegment(
        \mailjet()->segment('getkirby')
    )
    ->setText('Kirby 3 Mailjet Plugin')
    ->setHtml('Kirby 3 <i>Mailjet</i> Plugin')
    ->saveDraft();

// test
$draft->test('best.buddy@friends.design');

// set a schedule
$draft->setDatetime(new DateTime('+3 days'))->schedule();
// or cancel it
$draft->cancelSchedule();

// or publish
$draft->publish();
