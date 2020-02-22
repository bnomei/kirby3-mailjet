<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\Toolkit\A;
use Mailjet\Client;
use Mailjet\Resources;

final class MailjetContactslists
{
    /**
     * @var Client
     */
    private $client;

    /** @var MailjetContact */
    private $contact;

    /** @var MailjetContactProperties */
    private $contactProperties;
    /**
     * @var MailjetLog
     */
    private $log;

    public function __construct(
        Client $client,
        MailjetContact $contact,
        MailjetContactProperties $contactProperties,
        MailjetLog $log
    ) {
        $this->client = $client;
        $this->contact = $contact;
        $this->contactProperties = $contactProperties;
        $this->log = $log;
    }

    public function all(): array
    {
        $contactslists = [];
        $response = $this->client->get(Resources::$Contactslist, ['body' => null]);
        if ($response->success()) {
            foreach ($response->getData() as $segment) {
                $contactslists[] = [
                    'text' => A::get($segment, 'Name'),
                    'value' => A::get($segment, 'ID'),
                ];
            }
        }

        return $contactslists;
    }

    /**
     * Get Contactlist ID by name
     *
     * @param int|string $name
     * @return int|null
     */
    public function getId($name): ?int
    {
        $id = null;
        if (ctype_digit($name)) {
            $id = intval($name);
        } else {
            $response = $this->client->get(
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

        return $id;
    }

    /**
     * @param string $email
     * @param int $contactslistID
     * @return bool
     */
    public function remove(string $email, int $contactslistID): bool
    {
        $response = $this->client->post(
            Resources::$ContactslistManagecontact,
            [
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
    public function unsubscribe(string $email, int $contactslistID): bool
    {
        $response = $this->client->post(
            Resources::$ContactslistManagecontact,
            [
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
    public function subscribe(string $email, int $contactslistID, array $contactData = [], bool $force = false): bool
    {
        if ($this->contact->add($email) === false) {
            return false;
        }

        $response = $this->client->post(
            Resources::$ContactManagecontactslists,
            ['id' => $email, 'body' => ['ContactsLists' => [[
                'ListID' => $contactslistID,
                'Action' => $force ? 'addforce' : 'addnoforce',
            ]]]]
        );

        if ($response->success()) {
            return $this->contactProperties->set($email, $contactData);
        }

        return $response->success();
    }
}
