# Kirby 3 Mailjet

![Release](https://flat.badgen.net/packagist/v/bnomei/kirby3-mailjet?color=ae81ff)
![Stars](https://flat.badgen.net/packagist/ghs/bnomei/kirby3-mailjet?color=272822)
![Downloads](https://flat.badgen.net/packagist/dt/bnomei/kirby3-mailjet?color=272822)
![Issues](https://flat.badgen.net/packagist/ghi/bnomei/kirby3-mailjet?color=e6db74)
[![Build Status](https://flat.badgen.net/travis/bnomei/kirby3-mailjet)](https://travis-ci.com/bnomei/kirby3-mailjet)
[![Coverage Status](https://flat.badgen.net/coveralls/c/github/bnomei/kirby3-mailjet)](https://coveralls.io/github/bnomei/kirby3-mailjet) 
[![Maintainability](https://flat.badgen.net/codeclimate/maintainability/bnomei/kirby3-mailjet)](https://codeclimate.com/github/bnomei/kirby3-mailjet) 
[![Demo](https://flat.badgen.net/badge/website/examples?color=f92672)](https://kirby3-plugins.bnomei.com/mailjet) 
[![Gitter](https://flat.badgen.net/badge/gitter/chat?color=982ab3)](https://gitter.im/bnomei-kirby-3-plugins/community) 
[![Twitter](https://flat.badgen.net/badge/twitter/bnomei?color=66d9ef)](https://twitter.com/bnomei)

Send transactional SMS, transactional E-Mail and Campaigns with Mailjet

## Commercial Usage

This plugin is free but if you use it in a commercial project please consider to 
- [make a donation 🍻](https://www.paypal.me/bnomei/1) or
- [buy me ☕](https://buymeacoff.ee/bnomei) or
- [buy a Kirby license using this affiliate link](https://a.paddle.com/v2/click/1129/35731?link=1170)

## Installation

- unzip [master.zip](https://github.com/bnomei/kirby3-mailjet/archive/master.zip) as folder `site/plugins/kirby3-mailjet` or
- `git submodule add https://github.com/bnomei/kirby3-mailjet.git site/plugins/kirby3-mailjet` or
- `composer require bnomei/kirby3-mailjet`

## Setup

You can set the apikey and apisecret in the config.

**site/config/config.php**
```php
return [
    // other config settings ...
    'bnomei.mailjet.apikey' => 'YOUR-KEY-HERE',
    'bnomei.mailjet.apisecret' => 'YOUR-SECRET-HERE',
    'bnomei.mailjet.smstoken' => 'YOUR-TOKEN-HERE',
];
```

You can also set a callback if you use the [dotenv Plugin](https://github.com/bnomei/kirby3-dotenv).

**site/config/config.php**
```php
return [
    // other config settings ...
    'bnomei.mailjet.apikey' => function() { return env('MAILJET_APIKEY'); },
    'bnomei.mailjet.apisecret' => function() { return env('MAILJET_APISECRET'); },
    'bnomei.mailjet.smstoken' => function() { return env('MAILJET_SMSTOKEN'); },
];
```

## Usecase

This plugin is a wrapper around [Mailjet API v3 PHP](https://github.com/mailjet/mailjet-apiv3-php) while adding some Kirby 3 workflow specific helpers.

### Get Mailjet Client

Create a Mailjet Client instance and initialize it with the apikey and apisecret set in you config file.

```php
$mj = \Bnomei\Mailjet::singleton()->client();
// or just
$mj = mailjet()->client();
```

### Sending SMS

```php
mailjet()->sendSMS(
    'Your Sendername',
    '+49123456789',
    'Your Message'
);
```

### Get SMTP Transport Options

This plugin comes with [sensible defaults](https://github.com/bnomei/kirby3-mailjet/blob/master/index.php#L10) for your Mailjet SMTP configuration. If needed you can override them using your `site/config/config.php` File.

```php
$smtpTransportOptions = mailjet()->transport();
```

### Sending a SMTP E-Mail with Authentification

```php
$success = kirby()->email([
    'from' => 'mailjet@example.com', // you verified mailjet sender
    'to' => 'roger@rabbit.us', // regular or Mailjets "magic" Contactlist-E-Mail
    'subject' => 'Subject Text',
    'body' => [
        'html' => '<h1>Headline</h1><p>Text</p>',
        'text' => "Headline\n\nText",
    ],
    'transport' => mailjet()->transport(),
])->isSent();
```

> TIP: Read more about [sending E-Mails with Kirby 3](https://getkirby.com/docs/guide/emails) in the docs.

### Contactlists and Segments

**PHP**

```php
$listID = mailjet()->contactslist('My Newsletter'); // int
$segmentID = mailjet()->segment('VIP'); // int
```

**Panel blueprints: select field**

This plugin provides two site methods `site()->mailjetContactslists()` and `site()->mailjetSegments()` which you can use in select fields to query the mailjet api.

```yaml
mj_contactslists:
  label: Mailjet Contactslists
  type: select
  options: query
  query:
    fetch: site.mailjetContactslists # site.mailjetSegments
    text: "{{ arrayItem.text }}"
    value: "{{ arrayItem.value }}"
```

### Managing Contactslists

```php
$email = 'client@example.com';
$listID = mailjet()->contactslist('My Newsletter'); // int
$force = false;

mailjet()->subscribeToContactslist($email, $listID, [
    'Name' => get('name')
], $force);
mailjet()->unsubscribeFromContactslist($email, $listID);
mailjet()->removeFromContactslist($email, $listID);

mailjet()->excludeContactFromAllCampaigns($email);
```

### Managing Contact Properties

```php
$email = 'client@example.com';

// get key-value array
$props = mailjet()->getContactProperties($email);

// 'name' does not exist yet 
$doesNotExistYet = A::get($props, 'name'); // => null

// create
// https://dev.mailjet.com/email/reference/contacts/contact-properties/
$success = mailjet()->createContactProperty('name', 'str');

// set
$props['name'] = 'Mr. Robot';
$success = mailjet()->setContactProperties($email, $props);
```

> IMPORTANT: The `setContactProperties()` will **not** create missing properties but fail if encountering them. You have to manually create them with `createContactProperty()` or the Mailjet dashboard.

### Sending Campaigns: Basic

You can send a campaign via SMTP to Mailjets "magic" contactlist e-mail address. You can find that e-mail address in the Mailjet dashboard when managing a specific contactslist. They look like this `abcdefghi@lists.mailjet.com`.

### Sending Campaigns: Segmentation and Schedules

Sending more complex campaigns consists of creating and/or updating a campaign object using the Mailjet API identified by an unique ID, adding optional schedules and later issuing the publication. This plugin provides no specific helpers in that regard so please read the official docs on how to accomplish that (or hire me 😉).

## Cache

This plugin will cache certain API responses for a short time (default: 1 minute) to avoid make the same requests over and over again. This will reduce the risk of your account being temporarily suspended. 

## Settings

| bnomei.mailjet.              | Default        | Description               |            
|---------------------------|----------------|---------------------------|
| apikey | `null|callback` |  |
| apisecret | `null|callback` |  |
| smstoken | `null|callback` |  |
| email | `array` | mailjet specific default transport options for Kirbys mail helper |
| expires |`1` | in minutes |

## Roadmap

- [ ] Explanation on how to use [Janitor Plugin](https://github.com/bnomei/kirby3-janitor) buttons to test and send E-Mails

## Dependencies

- [Mailjet API v3 PHP](https://github.com/mailjet/mailjet-apiv3-php)

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](https://github.com/bnomei/kirby3-mailjet/issues/new).

## License

[MIT](https://opensource.org/licenses/MIT)

It is discouraged to use this plugin in any project that promotes racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.

## Credits

based on V2 versions of
- https://github.com/bnomei/kirby-mailjet
