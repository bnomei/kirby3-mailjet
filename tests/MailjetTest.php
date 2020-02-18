<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Bnomei\Mailjet;
use PHPUnit\Framework\TestCase;

final class MailjetTest extends TestCase
{
    /** @var array */
    private $bot;

    private function needsAPI(): void
    {
        if (!file_exists(__DIR__ . '/site/config/config.php')) {
            $this->markTestSkipped('No config file with API-Key.');
        }
    }

    public function setUp(): void
    {
        $this->bot = ['bot' => true];
    }

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

    public function testContactslist()
    {
        $this->needsAPI();

        $id = \mailjet()->contactslist('INVALID');
        $this->assertNull($id);

        $id = \mailjet()->contactslist('TEST');
        $this->assertNotNull($id);
    }

    public function testAllContactslists()
    {
        $this->needsAPI();

        $list = \mailjet()->contactslists();
        $this->assertIsArray($list);
        $this->assertTrue(count($list) > 0);
    }

    public function testAllSegments()
    {
        $this->needsAPI();

        $list = \mailjet()->segments();
        $this->assertIsArray($list);
        $this->assertTrue(count($list) > 0);
    }

    public function testSegment()
    {
        $this->needsAPI();

        $id = \mailjet()->segment('INVALID');
        $this->assertNull($id);

        $id = \mailjet()->segment('TEST');
        $this->assertNotNull($id);
    }

    public function testSubAndUnsubscribe()
    {
        $this->needsAPI();

        $email = md5(microtime()) . '@bnomei.com';
        $contactlistID = \mailjet()->contactslist('TEST');

        $success = \mailjet()->subscribeToContactslist(
            $email,
            $contactlistID,
            $this->bot,
            false // force
        );
        $this->assertTrue($success);

        $success = \mailjet()->unsubscribeFromContactslist(
            $email,
            $contactlistID
        );
        $this->assertTrue($success);

        $success = \mailjet()->unsubscribeFromContactslist(
            'invalid@bnomei.com',
            $contactlistID
        );
        $this->assertTrue($success);
    }

    public function testRemove()
    {
        $this->needsAPI();

        $contactlistID = \mailjet()->contactslist('TEST');

        $success = \mailjet()->subscribeToContactslist(
            'removeMe@bnomei.com',
            $contactlistID,
            $this->bot,
            false // force
        );
        $this->assertTrue($success);

        $success = \mailjet()->removeFromContactslist(
            'removeMe@bnomei.com',
            $contactlistID
        );
        $this->assertTrue($success);
    }

    public function testExclude()
    {
        $this->needsAPI();

        $email = md5(microtime()) . '@bnomei.com';
        $contactlistID = \mailjet()->contactslist('TEST');

        $success = \mailjet()->subscribeToContactslist(
            $email,
            $contactlistID,
            $this->bot,
            true // force
        );
        $this->assertTrue($success);

        $success = \mailjet()->excludeContactFromAllCampaigns($email);
        $this->assertTrue($success);
    }

    public function testSMS()
    {
        $this->needsAPI();

        $success = \mailjet()->sendSMS(
            'Kirby Mailjet PHPUnit',
            option('sms.target'),
            'This is a test.'
        );
        $this->assertTrue($success);
    }

    public function testContactProperties()
    {
        $email = md5(microtime()) . '@bnomei.com';
        $contactlistID = \mailjet()->contactslist('TEST');

        // add
        $success = \mailjet()->subscribeToContactslist(
            $email,
            $contactlistID,
            $this->bot,
            true // force
        );
        $this->assertTrue($success);

        $success = \mailjet()->createContactProperty('name', 'str', 'static');
        // might fail if exists
        $this->assertTrue($success === true || $success === false);

        // get
        $props = \mailjet()->getContactProperties($email);
        $this->assertNotNull($props);
        $this->assertIsArray($props);

        // set
        $props = [];
        $props['name'] = 'Mr. Robot';
        $props['bot'] = "true";
        $success = \mailjet()->setContactProperties($email, $props);
        $this->assertTrue($success);

        // get
        $props = \mailjet()->getContactProperties($email);
        $this->assertEquals('Mr. Robot', A::get($props, 'name'));
        $this->assertEquals('true', A::get($props, 'bot'));
    }
}
