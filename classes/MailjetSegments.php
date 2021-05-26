<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\Toolkit\A;
use Mailjet\Client;
use Mailjet\Resources;

final class MailjetSegments
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function all(): array
    {
        $segments = [];
        $response = $this->client->get(Resources::$Contactfilter, ['body' => ['limit' => 1000]]);
        if ($response->success()) {
            foreach ($response->getData() as $segment) {
                $segments[] = [
                    'text' => A::get($segment, 'Name'),
                    'value' => A::get($segment, 'ID'),
                ];
            }
        }

        return $segments;
    }

    /**
     * Get Segment ID by name
     *
     * @param int|string $name
     * @return int|null
     */
    public function getId($name): ?int
    {
        $id = null;
        if (ctype_digit($name)) {
            $segid = intval($name);
            $response = $this->client->get(Resources::$Contactfilter, ['body' => null]);
            foreach ($response->getData() as $r) {
                if ($segid === intval($r['ID'])) {
                    $id = intval($r['ID']);
                    break;
                }
            }
        } else {
            $response = $this->client->get(
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

        return $id;
    }
}
