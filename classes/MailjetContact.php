<?php

declare(strict_types=1);

namespace Bnomei;

use Mailjet\Client;
use Mailjet\Resources;

final class MailjetContact
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    /**
     * Add a Contact to Mailjet
     *
     * @param string $email
     * @return bool
     */
    public function add(string $email): bool
    {
        // find
        $response = $this->client->get(Resources::$Contact, [
            'id' => strtolower($email),
            'body' => null
        ]);

        // does not exist yet
        if (! $response->success()) {
            $response = $this->client->post(Resources::$Contact, ['body' => [
                'Email' => strtolower($email),
            ]]);
        }

        return $response->success();
    }

    /**
     * @param string $email
     * @return bool
     */
    public function exclude(string $email): bool
    {
        // find
        $response = $this->client->get(Resources::$Contact, [
            'id' => strtolower($email),
            'body' => null
        ]);

        // does exist
        if ($response->success()) {
            $response = $this->client->put(
                Resources::$Contact,
                [
                    'id' => strtolower($email),
                    'body' => ['IsExcludedFromCampaigns' => 'true']
                ]
            );
        }

        return $response->success();
    }
}
