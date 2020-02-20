<?php

declare(strict_types=1);

namespace Bnomei;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

final class MailjetSMS
{
    /**
     * @var string
     */
    private $token;
    /**
     * @var MailjetLog
     */
    private $log;

    public function __construct(string $token, MailjetLog $log)
    {
        $this->token = $token;
        $this->log = $log;
    }

    /**
     * @param string $from
     * @param string $to
     * @param string $text
     * @return bool
     */
    public function send(string $from, string $to, string $text): bool
    {
        $request = new Request(
            'POST',
            'https://api.mailjet.com/v4/sms-send',
            [
                'Authorization' => 'Bearer ' . $this->token,
                'content-type' => 'application/json; charset=utf-8',
            ],
            json_encode([
                'From' => $from,
                'To' => $to,
                'Text' => $text
            ])
        );
        $response = (new Client())->send($request);

        return $response->getStatusCode() === 200;
    }
}
