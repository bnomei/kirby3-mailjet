<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\Toolkit\A;
use Mailjet\Client;
use Mailjet\Resources;

use function mailjet;

final class MailjetContactProperties
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
     * @param string $email
     * @return array|null
     */
    public function get(string $email): ?array
    {
        // find
        $response = $this->client->get(Resources::$Contactdata, [
            'id' => strtolower($email),
            'body' => null
        ]);

        if ($response->success()) {
            $dataKV = [];
            foreach (A::get($response->getData()[0], 'Data', []) as $item) {
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
    public function set(string $email, array $data): bool
    {
        // find
        $response = $this->client->get(Resources::$Contactdata, [
            'id' => strtolower($email),
            'body' => null
        ]);

        // does exist
        if ($response->success() && count($data) === 0) {
            return true; // can not update with empty data => 400
        }
        if ($response->success()) {
            $dataToAdd = [];
            foreach ($data as $key => $value) {
                if (strtolower($key) === 'email') {
                    continue;
                }
                $dataToAdd[] = ['Name' => $key, 'Value' => $value];
            }
            $response = $this->client->put(Resources::$Contactdata, [
                'id' => $email,
                'body' => ['Data' => $dataToAdd]
            ]);
            return $response->getStatus() === 200;
        }

        return false;
    }

    public static function create(string $name, string $type = 'str', string $namespace = 'static')
    {
        $response = mailjet()->client()->post(Resources::$Contactmetadata, [
            'body' => [
                'Datatype' => $type,
                'Name' => $name,
                'NameSpace' => $namespace,
            ]
        ]);
        return $response->getStatus() === 200;
    }
}
