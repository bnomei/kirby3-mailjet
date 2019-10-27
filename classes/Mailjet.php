<?php

declare(strict_types=1);

namespace Bnomei;

use Mailjet\Client;

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

        foreach ($options as $key => $callable) {
            if (is_callable($callable) && in_array($key, ['apikey', 'apisecret'])) {
                $this->options[$key] = $callable();
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

    /** @var \Bnomei\Mailjet */
    private static $singleton;

    /**
     * @param array $options
     * @return \Bnomei\Mailjet
     */
    public static function singleton(array $options = [])
    {
        if (! self::$singleton) {
            self::$singleton = new self($options);
        }

        return self::$singleton;
    }
}
