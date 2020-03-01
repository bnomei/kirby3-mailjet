<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\A;
use Mailjet\Client;
use Mailjet\Resources;

final class Mailjet
{
    /** @var Client */
    private $client;

    /** @var array */
    private $options;
    /**
     * @var MailjetContact
     */
    private $contact;
    /**
     * @var MailjetContactProperties
     */
    private $contactProperties;
    /**
     * @var MailjetSegments
     */
    private $segments;
    /**
     * @var MailjetSMS
     */
    private $sms;
    /**
     * @var MailjetContactslists
     */
    private $contactlists;
    /**
     * @var MailjetLog
     */
    private $log;

    /**
     * Mailjet constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $defaults = [
            'debug' => option('debug'),
            'log' => option('bnomei.mailjet.log'),
            'expire' => option('bnomei.mailjet.expire', 1),
            'apikey' => option('bnomei.mailjet.apikey'),
            'apisecret' => option('bnomei.mailjet.apisecret'),
            'smstoken' => option('bnomei.mailjet.smstoken'),
        ];
        $this->options = array_merge($defaults, $options);

        foreach ($this->options as $key => $callable) {
            if (is_callable($callable) && in_array($key, ['apikey', 'apisecret', 'smstoken'])) {
                $this->options[$key] = trim($callable()) . '';
            }
        }

        $this->client = new Client(
            $this->options['apikey'],
            $this->options['apisecret'],
            true,
            ['version' => 'v3']
        );

        $this->log = new MailjetLog((bool)$this->option('debug'), $this->option('log'));
        $this->contact = new MailjetContact($this->client);
        $this->contactProperties = new MailjetContactProperties($this->client);
        $this->contactlists = new MailjetContactslists($this->client, $this->contact, $this->contactProperties, $this->log);
        $this->segments = new MailjetSegments($this->client);
        $this->sms = new MailjetSMS((string) $this->option('smstoken'), $this->log);

        if ($this->option('debug')) {
            kirby()->cache('bnomei.mailjet')->flush();
        }
    }

    /**
     * @param string|null $key
     * @return array|mixed
     */
    public function option(?string $key = null)
    {
        if ($key) {
            return A::get($this->options, $key);
        }
        return $this->options;
    }

    /**
     * Get Mailjet Client Instance
     *
     * @return Client
     */
    public function client(): Client
    {
        return $this->client;
    }

    /**
     * Get SMTP Email Transport Options Array
     *
     * @return array
     */
    public function transport(): array
    {
        return array_merge(
            [
                'username' => $this->option('apikey'),
                'password' => $this->option('apisecret'),
            ],
            option('bnomei.mailjet.email.transport')
        );
    }

    public function cacheKey(string $key): string
    {
        return sha1(implode([
            'M4ilj3T',
            $this->option('apikey'),
            __FILE__,
            $this->option('apisecret'),
            $key
        ]));
    }

    /**
     * @param string $key
     * @return mixed|null
     * @throws InvalidArgumentException
     */
    private function cacheRead(string $key)
    {
        if ($this->option('debug')) {
            return null;
        }
        return kirby()->cache('bnomei.mailjet')->get(
            $this->cacheKey($key)
        );
    }

    private function cacheWrite(string $key, $value): bool
    {
        if ($this->option('debug')) {
            return false;
        }
        return kirby()->cache('bnomei.mailjet')->set(
            $this->cacheKey($key),
            $value,
            (int) $this->option('expire')
        );
    }

    public function sendSMS(string $from, string $to, string $text): bool
    {
        return $this->sms->send($from, $to, $text);
    }

    public function campaignDraft(?int $campaign = null): MailjetCampaignDraft
    {
        $draft = new MailjetCampaignDraft($this->client, $this->log);
        if ($campaign) {
            $draft->setCampaign($campaign);
        }
        return $draft;
    }

    public function excludeContactFromAllCampaigns(string $email): bool
    {
        return $this->contact->exclude($email);
    }

    public function sender(string $email): ?int
    {
        if ($cache = $this->cacheRead('sender-' . $email)) {
            return $cache;
        }
        $response = $this->client->get(Resources::$Sender, ['id' => $email]);
        $value = $response->success() ? $response->getData()[0]['ID'] : null;
        $this->cacheWrite('sender-' . $email, $value);
        return $value;
    }

    public function contactslists(): array
    {
        if ($cache = $this->cacheRead('contactslists')) {
            return $cache;
        }
        $value = $this->contactlists->all();
        $this->cacheWrite('contactslists', $value);
        return $value;
    }

    public function contactslist($name): ?int
    {
        if ($cache = $this->cacheRead('contactslist-' . $name)) {
            return $cache;
        }
        $value = $this->contactlists->getId($name);
        $this->cacheWrite('contactslist-' . $name, $value);
        return $value;
    }

    public function removeFromContactslist(string $email, int $contactslistID): bool
    {
        return $this->contactlists->remove($email, $contactslistID);
    }

    public function unsubscribeFromContactslist(string $email, int $contactslistID): bool
    {
        return $this->contactlists->unsubscribe($email, $contactslistID);
    }

    public function subscribeToContactslist(string $email, int $contactslistID, array $contactData = [], bool $force = false): bool
    {
        return $this->contactlists->subscribe($email, $contactslistID, $contactData, $force);
    }

    public function segments(): array
    {
        if ($cache = $this->cacheRead('segments')) {
            return $cache;
        }
        $value = $this->segments->all();
        $this->cacheWrite('segments', $value);
        return $value;
    }

    public function segment($name): ?int
    {
        if ($cache = $this->cacheRead('segment-' . $name)) {
            return $cache;
        }
        $value = $this->segments->getId($name);
        $this->cacheWrite('segment-' . $name, $value);
        return $value;
    }

    public function getContactProperties(string $email): ?array
    {
        return $this->contactProperties->get($email);
    }

    public function setContactProperties(string $email, array $data): bool
    {
        return $this->contactProperties->set($email, $data);
    }

    public static function createContactProperty(string $name, string $type = 'str', string $namespace = 'static')
    {
        return MailjetContactProperties::create($name, $type, $namespace);
    }

    public function scheduledCampaignDrafts(): ?array
    {
        if ($cache = $this->cacheRead('scheduled')) {
            return $cache;
        }
        $value = MailjetCampaignDraft::scheduled();
        $this->cacheWrite('scheduled', $value);
        return $value;
    }

    /** @var Mailjet */
    private static $singleton;

    /**
     * @param array $options
     * @return Mailjet
     */
    public static function singleton(array $options = [])
    {
        if (!self::$singleton) {
            self::$singleton = new self($options);
        }

        return self::$singleton;
    }
}
