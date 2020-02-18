<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\Toolkit\A;
use \Mailjet\Client;
use \Mailjet\Resources;

final class Mailjet
{
    /** @var \Mailjet\Client */
    private $client;

    /** @var array */
    private $options;

    /**
     * Mailjet constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $defaults = [
            'debug' => option('debug'),
            'expire' => option('bnomei.mailjet.expire', 1),
            'apikey' => option('bnomei.mailjet.apikey'),
            'apisecret' => option('bnomei.mailjet.apisecret'),
            'smstoken' => option('bnomei.mailjet.smstoken'),
        ];
        $this->options = array_merge($defaults, $options);

        foreach ($this->options as $key => $callable) {
            if (is_callable($callable) && in_array($key, ['apikey', 'apisecret'])) {
                $this->options[$key] = trim($callable()) . '';

            }
        }

        $this->client = new Client(
            $this->options['apikey'],
            $this->options['apisecret'],
            true,
            ['version' => 'v3']
        );

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
     * @return \Mailjet\Client
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
                'username' => $this->options['apikey'],
                'password' => $this->options['apisecret'],
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

    public function contactslists(): array
    {
        $contactslists = kirby()->cache('bnomei.mailjet')->get(
            $this->cacheKey('contactslists')
        );
        if ($contactslists && !$this->option('debug')) {
            return $contactslists;
        }

        $contactslists = [];
        $response = $this->client()->get(Resources::$Contactslist, ['body' => null]);
        if ($response->success()) {
            foreach ($response->getData() as $segment) {
                $contactslists[] = [
                    'text' => A::get($segment, 'Name'),
                    'value' => A::get($segment, 'ID'),
                ];
            }
        }

        kirby()->cache('bnomei.mailjet')->set(
            $this->cacheKey('contactslists'),
            $contactslists,
            (int)$this->option('expire')
        );

        return $contactslists;
    }

    /**
     * Get Contactlist ID by name
     *
     * @param int|string $name
     * @return int|null
     */
    public function contactslist($name): ?int
    {
        $response = kirby()->cache('bnomei.mailjet')->get(
            $this->cacheKey('contactslist-' . $name)
        );
        if ($response && !$this->option('debug')) {
            return $response;
        }

        $id = null;
        if (ctype_digit($name)) {
            $id = intval($name);

        } else {
            $response = $this->client()->get(
                Resources::$Contactslist,
                ['filters' => ['Name' => $name], 'body' => null]
            );
            if ($response->success()) {
                foreach ($response->getData() as $r) {
                    if ($r['Name'] === $name) {
                        $id = intval($r['ID']);
                        break;
                    }
                }
            }
        }

        if ($id) {
            kirby()->cache('bnomei.mailjet')->set(
                $this->cacheKey('contactslist-' . $name),
                $id,
                (int)$this->option('expire')
            );
            return $id;
        }

        return null;
    }

    public function segments(): array
    {
        $segments = kirby()->cache('bnomei.mailjet')->get(
            $this->cacheKey('segments')
        );
        if ($segments && !$this->option('debug')) {
            return $segments;
        }

        $segments = [];
        $response = $this->client()->get(Resources::$Contactfilter, ['body' => null]);
        if ($response->success()) {
            foreach ($response->getData() as $segment) {
                $segments[] = [
                    'text' => A::get($segment, 'Name'),
                    'value' => A::get($segment, 'ID'),
                ];
            }
        }

        kirby()->cache('bnomei.mailjet')->set(
            $this->cacheKey('segments'),
            $segments,
            (int)$this->option('expire')
        );

        return $segments;
    }

    /**
     * Get Segment ID by name
     *
     * @param int|string $name
     * @return int|null
     */
    public function segment($name): ?int
    {
        $response = kirby()->cache('bnomei.mailjet')->get(
            $this->cacheKey('segment-' . $name)
        );
        if ($response && !$this->option('debug')) {
            return $response;
        }

        $id = null;
        if (ctype_digit($name)) {
            $segid = intval($name);
            $response = $this->client()->get(Resources::$Contactfilter, ['body' => null]);
            foreach ($response->getData() as $r) {
                if ($segid === intval($r['ID'])) {
                    $id = intval($r['ID']);
                    break;
                }
            }

        } else {
            $response = $this->client()->get(
                Resources::$Contactfilter,
                ['filters' => ['Name' => $name], 'body' => null]
            );
            foreach ($response->getData() as $r) {
                if ($name === $r['Name']) {
                    $id = intval($r['ID']);
                    break;
                }
            }
        }

        if ($id) {
            kirby()->cache('bnomei.mailjet')->set(
                $this->cacheKey('segment-' . $name),
                $id,
                (int)$this->option('expire')
            );
            return $id;
        }

        return null;
    }

    /**
     * @param string $email
     * @return array|null
     */
    public function getContactProperties(string $email): ?array
    {
        // find
        $response = $this->client()->get(Resources::$Contactdata, [
            'id' => strtolower($email),
            'body' => null
        ]);

        if ($response->success()) {
            $dataKV = [];
            foreach(A::get($response->getData()[0], 'Data', []) as $item) {
                $dataKV[$item['Name']] = $item['Value'];
            }
            return $dataKV;
        }

        return null;
    }

    /**
     * @param string $email
     * @param array $data
     * @return bool
     */
    public function setContactProperties(string $email, array $data): bool
    {
        // find
        $response = $this->client()->get(Resources::$Contactdata, [
            'id' => strtolower($email),
            'body' => null
        ]);

        // does exist
        if ($response->success() && count($data)) {
            $dataToAdd = [];
            foreach ($data as $key => $value) {
                if (strtolower($key) === 'email') {
                    continue;
                }
                $dataToAdd[] = ['Name' => $key, 'Value' => $value];
            }
            $response = $this->client()->put(Resources::$Contactdata, [
                'id' => $email,
                'body' => ['Data' => $dataToAdd]
            ]);
            return $response->getStatus() === 200;
        }

        return false;
    }

    public function createContactProperty(string $name, string $type = 'str', string $namespace = 'static') {
        $response = $this->client()->post(Resources::$Contactmetadata, [
            'body' => [
                'Datatype' => $type,
                'Name' => $name,
                'NameSpace' => $namespace,
            ]
        ]);
        return $response->getStatus() === 200;
    }

    /**
     * Add a Contact to Mailjet
     *
     * @param string $email
     * @return bool
     */
    private function addContact(string $email): bool
    {
        // find
        $response = $this->client()->get(Resources::$Contact, [
            'id' => strtolower($email),
            'body' => null
        ]);

        // does not exist yet
        if (! $response->success()) {
            $response = $this->client()->post(Resources::$Contact, ['body' => [
                'Email' => strtolower($email),
            ]]);
        }

        return $response->success();
    }

    /**
     * @param string $email
     * @return bool
     */
    public function excludeContactFromAllCampaigns(string $email): bool
    {
        // find
        $response = $this->client()->get(Resources::$Contact, [
            'id' => strtolower($email),
            'body' => null
        ]);

        // does exist
        if ($response->success()) {
            $response = $this->client()->put(
                Resources::$Contact, [
                    'id' => strtolower($email),
                    'body' => ['IsExcludedFromCampaigns' => 'true']
                ]
            );
        }

        return $response->success();
    }

    /**
     * @param string $email
     * @param int $contactslistID
     * @return bool
     */
    public function removeFromContactslist(string $email, int $contactslistID): bool
    {
        $response = $this->client()->post(
            Resources::$ContactslistManagecontact, [
                'id' => $contactslistID,
                'body' => ['Email' => $email, 'Action' => 'remove']
            ]
        );

        return $response->success();
    }

    /**
     * @param string $email
     * @param int $contactslistID
     * @return bool
     */
    public function unsubscribeFromContactslist(string $email, int $contactslistID): bool
    {
        $response = $this->client()->post(
            Resources::$ContactslistManagecontact, [
                'id' => $contactslistID,
                'body' => ['Email' => $email, 'Action' => 'unsub']
            ]
        );
        if ($response->success()) {
            return true;
        }

        return false;
    }

    /**
     * @param string $email
     * @param int $contactslistID
     * @param array $contactData
     * @param bool $force
     * @return bool
     */
    public function subscribeToContactslist(string $email, int $contactslistID, array $contactData = [], bool $force = false): bool
    {
        if ($this->addContact($email) === false) {
            return false;
        }

        $response = $this->client()->post(
            Resources::$ContactManagecontactslists,
            ['id' => $email, 'body' => ['ContactsLists' => [[
                'ListID' => $contactslistID,
                'Action' => $force ? 'addforce' : 'addnoforce',
            ]]]]
        );

        if ($response->success()) {
            return $this->setContactProperties($email, $contactData);
        }

        return $response->success();
    }

    /**
     * @param string $from
     * @param string $to
     * @param string $text
     * @return bool
     */
    public function sendSMS(string $from, string $to, string $text): bool
    {
        $client = new \GuzzleHttp\Client();

        $response = $client->post(
            'https://api.mailjet.com/v4/sms-send',
            [
                'Authorization' => 'Bearer ' . $this->option('smstoken'),
                'json' => [
                    'From' => $from,
                    'To' => $to,
                    'Text' => $text
                ],
            ]
        );

        return $response->getStatusCode() === 200;
    }

    /** @var \Bnomei\Mailjet */
    private static $singleton;

    /**
     * @param array $options
     * @return \Bnomei\Mailjet
     */
    public static function singleton(array $options = [])
    {
        if (!self::$singleton) {
            self::$singleton = new self($options);
        }

        return self::$singleton;
    }
}
