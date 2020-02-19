<?php

declare(strict_types=1);

namespace Bnomei;

final class MailjetSMS
{
    /**
     * @var string
     */
    private $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * @param string $from
     * @param string $to
     * @param string $text
     * @return bool
     */
    public function send(string $from, string $to, string $text): bool
    {
        $client = new \GuzzleHttp\Client();

        $response = $client->post(
            'https://api.mailjet.com/v4/sms-send',
            [
                'Authorization' => 'Bearer ' . $this->token,
                'json' => [
                    'From' => $from,
                    'To' => $to,
                    'Text' => $text
                ],
            ]
        );

        return $response->getStatusCode() === 200;
    }
}
