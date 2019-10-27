<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Bnomei\Mailjet;
use PHPUnit\Framework\TestCase;

final class MailjetTest extends TestCase
{
    public function testMailjetLibExists()
    {
        $this->assertIsString(\Mailjet\Client::class);
    }

    public function testConstruct()
    {
        $mailjet = new Mailjet();

        $this->assertInstanceOf(Mailjet::class, $mailjet);
    }

    public function testClient()
    {
        $mailjet = new Mailjet();

        $this->assertInstanceOf(\Mailjet\Client::class, $mailjet->client());
    }

    public function testSingleton()
    {
        // static instance does not exists
        $mailjet = Bnomei\Mailjet::singleton();
        $this->assertInstanceOf(Mailjet::class, $mailjet);

        // static instance now does exist
        $mailjet = Bnomei\Mailjet::singleton();
        $this->assertInstanceOf(Mailjet::class, $mailjet);
    }

    public function testCallableOptions()
    {
        $mailjet = new Mailjet([
            'apikey' => function() { return 'APIKEY'; },
            'apisecret' => function() { return 'APISECRET'; },
        ]);

        $this->assertInstanceOf(Mailjet::class, $mailjet);
    }

    public function testSMTPTransportOptions()
    {
        $mailjet = new Bnomei\Mailjet([
            'apikey' => 'APIKEY',
            'apisecret' => 'APISECRET',
        ]);
        $smtpTO = $mailjet->transport();

        $this->assertEquals( 'smtp', $smtpTO['type']);
        $this->assertEquals( 'in-v3.mailjet.com', $smtpTO['host']);
        $this->assertEquals( 587, $smtpTO['port']);
        $this->assertEquals( 'tsl', $smtpTO['security']);
        $this->assertEquals( true, $smtpTO['auth']);
        $this->assertEquals( 'APIKEY', $smtpTO['username']);
        $this->assertEquals( 'APISECRET', $smtpTO['password']);
    }
}
