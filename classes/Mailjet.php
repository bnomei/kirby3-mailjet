<?php

declare(strict_types=1);

namespace Bnomei;

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
            'apikey' => option('bnomei.mailjet.apikey'),
            'apisecret' => option('bnomei.mailjet.apisecret'),
        ];
        $this->options = array_merge($defaults, $options);

        foreach ($this->options as $key => $callable) {
            if (is_callable($callable) && in_array($key, ['apikey', 'apisecret'])) {
                $this->options[$key] = trim($callable()) . '';

            }
        }

        $this->client = new Client($this->options['apikey'], $this->options['apisecret']);
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

    /**
     * Get Contactlist ID by name
     *
     * @param int|string $name
     * @return int|null
     */
    public function contactslist($name): ?int
    {
        if (ctype_digit($name)) {
            return intval($name);
        }

        $response = $this->client()->get(Resources::$Contactslist, ['filters' => ['Name' => $name], 'body' => null]);
        if ($response->success()) {
            foreach ($response->getData() as $r) {
                if ($r['Name'] === $name) {
                    return intval($r['ID']);
                }
            }
        }

        return null;
    }

    /**
     * Get Segment ID by name
     *
     * @param int|string $name
     * @return int|null
     */
    public function segment($name): ?int
    {
        if (ctype_digit($name)) {
            $segid = intval($name);
            $response = $this->client()->get(Resources::$Contactfilter, ['body' => null]);
            foreach ($response->getData() as $r) {
                if ($segid === intval($r['ID'])) {
                    return intval($r['ID']);
                }
            }
        }

        $response = $this->client()->get(Resources::$Contactfilter, ['filters' => ['Name' => $name], 'body' => null]);
        foreach ($response->getData() as $r) {
            if ($name === $r['Name']) {
                return intval($r['ID']);
            }
        }

        return null;
    }

    /**
     * Add a Contact to Mailjet
     *
     * @param string $email
     * @return bool
     */
    public function addContact(string $email): bool
    {
        $response = $this->client()->post(Resources::$Contact, ['body' => [
            'Email' => strtolower($email),
        ]]);

        // new
        if ($response->success()) {
            // $contactID = $response->getData()[0]['ID']; // first element
            return true;
        }

        // exists
        if (intval($response->getData()['StatusCode']) === 400) {
            // $contactID = strtolower($email);
            return true;
        }

        // failed
        return false;
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
        if ($response->success() || intval($response->getData()['Status']) === 400) {
            return true;
        }

        return false;
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
        if ($response->success() || intval($response->getData()['Status']) === 400) {
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
            $dataToAdd = [];
            foreach ($contactData as $key => $value) {
                if ($key === 'email') {
                    continue;
                }
                $dataToAdd[] = ['Name' => $key, 'value' => $value];
            }
            if (count($dataToAdd)) {
                $this->client()->put(Resources::$Contactdata, [
                    'id' => $email,
                    'body' => ['Data' => $dataToAdd]
                ]);
            }
            return true;
        }

        return false;
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
