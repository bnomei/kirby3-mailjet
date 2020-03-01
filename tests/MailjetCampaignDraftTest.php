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
    /**
     * @var Closure
     */
    private $log;

    private function needsAPI(): void
    {
        if (!file_exists(__DIR__ . '/site/config/config.php')) {
            $this->markTestSkipped('No config file with API-Key.');
        }
    }

    public function setUp(): void
    {
        $this->to = option('campaign.test.to.email');
        $this->from = option('campaign.test.from.email');

        $this->campaign = mailjet()->campaignDraft();
        $this->campaign
            ->setLocale('de_DE') // required
            ->setSenderemail($this->from);
    }

    public function testTest()
    {
        $this->needsAPI();

        $subject = md5((string) microtime());

        $success = $this->campaign
            ->setSender(mailjet()->sender($this->from))
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
        $list = mailjet()->contactslist('BNOMEI');

        $success = $this->campaign
            ->setSender(mailjet()->sender($this->from))
            ->setSubject($subject)
            ->setContactslist($list)
            ->setText('Voi-La')
            ->setHtml('<i>Voi</i>-La')
            ->saveDraft();
        $this->assertTrue($success);

        $success = $this->campaign->send();
        $this->assertTrue($success);
    }

    public function testUpdateExisting()
    {
        $this->needsAPI();

        $success = $this->campaign
            ->setSender(mailjet()->sender($this->from))
            ->setSubject('Album of the week') // required
            ->saveDraft(); // load since local and subject match
        $this->assertTrue($success);

        $id = option('campaign.test.id');
        $this->assertNotNull($this->campaign->id());
        $this->assertEquals($id, $this->campaign->id());

        $success = $this->campaign
            ->setText('Gypsi Punks')
            ->setHtml('<h1>Gypsi Punks</h1>')
            ->saveDraft();
        $this->assertTrue($success);
        $this->assertEquals($id, $this->campaign->id());


        $success = $this->campaign
            ->setSubject('Album of the week') // required
            ->saveDraft(); // load since local and subject match
        $this->assertTrue($success);
    }

    public function testCancelScheduled()
    {
        $this->needsAPI();

        $subject = md5((string) microtime());
        $list = mailjet()->contactslist('TEST');

        foreach ([1,2,3] as $num) {
            $success = $this->campaign
                ->setSender(mailjet()->sender($this->from))
                ->setSubject($subject . ' ' . $num)
                ->setContactslist($list)
                ->setDatetime(new DateTime('+1 day'))
                ->setText('Gogol ' . $num)
                ->setHtml('<b>G</b>ogol ' . $num)
                ->saveDraft();
            $this->assertTrue($success);

            $success = $this->campaign
                ->schedule();
            $this->assertTrue($success);
        }

        // cached: \mailjet()->scheduledCampaignDrafts()
        // live: MailjetCampaignDraft::scheduled()
        foreach (MailjetCampaignDraft::scheduled() as $draft) {
            $campaign = mailjet()->campaignDraft($draft['Value']);
            $success = $campaign->cancelSchedule();
            $this->assertTrue($success);
        }
    }

    // TODO: testUpdateExistingWithDifferentContactslist
    // TODO: testUpdateExistingWithDifferentSegment
}
