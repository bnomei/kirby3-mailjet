<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Bnomei\MailjetCampaignDraft;
use PHPUnit\Framework\TestCase;

final class MailjetCampaignDraftTest extends TestCase
{
    private $to;
    private $fromName;
    private $from;

    /**
     * @var MailjetCampaignDraft
     */
    private $campaign;

    private function needsAPI(): void
    {
        if (!file_exists(__DIR__ . '/site/config/config.php')) {
            $this->markTestSkipped('No config file with API-Key.');
        }
    }

    public function setUp(): void
    {
        $this->to = \option('campaign.test.to.email');
        $this->fromName = \option('campaign.test.from.name');
        $this->from = \option('campaign.test.from.email');

        $this->campaign = new MailjetCampaignDraft(mailjet()->client());
        $this->campaign
            ->setLocale('de_DE') // required
            ->setName($this->fromName)
            ->setFrom($this->from);
    }

    public function testTest()
    {
        $this->needsAPI();

        $subject = md5((string) microtime());

        $success = $this->campaign
            ->setSubject($subject) // required
            ->setText('Smarkatch')
            ->setHtml('<b>S</b><i>mark</i>atc<h1>h</h1>')
            ->saveDraft();

        $this->assertTrue($success);

        $success = $this->campaign->test($this->to);
        $this->assertTrue($success);
    }

    public function testScheduleAndPublish()
    {
        $this->needsAPI();

        $subject = md5((string) microtime());

        $success = $this->campaign
            ->setSubject($subject) // required
            ->saveDraft();
        $this->assertTrue($success);

        $success = $this->campaign
            ->setDatetime(new \DateTime('+1 day'))
            ->schedule();
        $this->assertTrue($success);

        $success = $this->campaign->cancelSchedule();
        $this->assertTrue($success);

        // TODO:
        // $success = $this->campaign->send();
        // $this->assertTrue($success);
    }

    public function testUpdateExisting()
    {
        $this->needsAPI();

        // load
        $success = $this->campaign
            ->setSubject('Album of the week') // required
            ->saveDraft();
        $this->assertTrue($success);

        $id = \option('campaign.test.id');
        $this->assertNotNull($this->campaign->id());
        $this->assertEquals($id, $this->campaign->id());

        $success = $this->campaign
            ->setText('Gypsi Punks')
            ->setHtml('<h1>Gypsi Punks</h1>')
            ->saveDraft();
        $this->assertTrue($success);
        $this->assertEquals($id, $this->campaign->id());
    }

    // TODO: testUpdateExistingWithDifferentContactslist
    // TODO: testUpdateExistingWithDifferentSegment
}
