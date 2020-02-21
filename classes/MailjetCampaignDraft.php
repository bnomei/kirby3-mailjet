<?php

declare(strict_types=1);

namespace Bnomei;

use Mailjet\Client;
use Mailjet\Resources;

/**
 * @method setLocale(string $value): self
 * @method setSender(int $value): self
 * @method setSenderemail(string $value): self
 * @method setSubject(string $value): self
 * @method setTitle(string $value): self
 * @method setDatetime(\DateTime $value): self
 * @method setTemplate(int $value): self
 * @method setText(string $value): self
 * @method setHtml(string $value): self
 * @method setUrl(string $value): self
 * @method setContactslist(int $value): self
 * @method setSegment(int $value): self
 * @method setCampaign(int $value): self
 * @method getLocale(): ?string
 * @method getSender(): ?int
 * @method getSenderemail(): ?string
 * @method getSubject(): ?string
 * @method getTitle(): ?string
 * @method getDatetime(): ?\Datetime
 * @method getTemplate(): ?int
 * @method getText(): ?string
 * @method getHtml(): ?string
 * @method getUrl(): ?string
 * @method getContactslist(): ?int
 * @method getSegment(): ?int
 * @method getCampaign(): ?int
 */
final class MailjetCampaignDraft
{
    /** @var Client */
    private $client;
    /** @var string|null */
    private $locale;
    /** @var string|null */
    private $sender;
    /** @var string */
    private $senderemail;
    /** @var string */
    private $subject;
    /** @var string */
    private $title;
    /** @var string */
    private $text;
    /** @var string */
    private $html;
    /** @var string */
    private $url;
    /** @var \DateTime|null */
    private $datetime;
    /** @var int|null */
    private $template;
    /** @var int|null */
    private $segment;
    /** @var int|null */
    private $contactslist;
    /** @var int|null */
    private $campaign;

    public static $fluentProperties = [
        'locale',
        'sender',
        'senderemail',
        'subject',
        'title',
        'datetime',
        'template',
        'text',
        'html',
        'url',
        'contactslist',
        'segment',
        'campaign',
    ];
    /**
     * @var MailjetLog
     */
    private $log;

    public function __construct(Client $client, MailjetLog $log)
    {
        $this->client = $client;
        $this->log = $log;
    }

    public function __call(string $name, $arguments)
    {
        if(strlen($name) > 3 && strpos($name, 'get') === 0) {
            return $this->get($name);
        }

        if(strlen($name) > 3 && strpos($name, 'set') === 0 &&
            is_array($arguments) && count($arguments) === 1) {
            return $this->set($name, $arguments[0]);
        }
    }

    private function get(string $name)
    {
        $getters = array_map(static function($item) {
            return 'get' . ucfirst($item);
        }, static::$fluentProperties);

        $key = array_search($name, $getters);
        if ($key !== false) {
            return $this->{static::$fluentProperties[$key]};
        } else {
            throw new \Exception('Invalid getter: ' . $name);
        }
    }

    private function set(string $name, $value): self
    {
        $setters = array_map(static function($item) {
            return 'set' . ucfirst($item);
        }, static::$fluentProperties);

        $key = array_search($name, $setters);
        if ($key !== false) {
            $this->{static::$fluentProperties[$key]} = $value;
        } else {
            throw new \Exception('Invalid getter: ' . $name);
        }

        return $this;
    }

    public function id(): ?int
    {
        return $this->campaign;
    }

    public function saveDraft(): bool
    {
        $wasCreated = false;

        $campaign = $this->findDraft();
        if (is_null($campaign)) {
            $campaign = $this->createDraft();
            $wasCreated = true;
            if (is_null($campaign)) {
                return false;
            }
        }
        $this->campaign = $campaign;

        if (! $wasCreated) {
            $response = $this->client->put(Resources::$Campaigndraft, [
                'ID' => $this->campaign,
                'Body' => $this->bodyFull(),
            ]);
            // $this->log->write('saveDraft.put', 'info', $response->getData());
        }

        $response = $this->client->post(Resources::$CampaigndraftDetailcontent, [
            'ID' => $this->campaign,
            'Body' => $this->detailContent(),
        ]);
        // $this->log->write('saveDraft.detailcontent.post', 'info', $response->getData());

        return $response->success();
    }

    private function detailContent(): array
    {
        $detailContent = [
            'Text-part' => $this->text,
            'Html-part' => $this->html,
        ];
        return $detailContent;
    }

    private function bodyUnique(): array
    {
        $body = [
            'Locale' => $this->locale,      // required
            'Subject' => $this->subject,    // required
        ];
        if ($this->contactslist) {
            $body['Contactslist'] = $this->contactslist;
        }
        if ($this->segment) {
            $body['SegmentationID'] = $this->segment;
        }
        return $body;
    }

    public function bodyFull(): array
    {
        $body = [
            'Locale' => $this->locale,
            'Sender' => $this->sender,
            'SenderEmail' => $this->senderemail,
            'Subject' => $this->subject,
            'Title' => $this->title,
            'Url' => $this->url,
            'ContactsListID' => $this->contactslist,
            'SegmentationID' => $this->segment,
            'TemplateID' => $this->template,
        ];

        $body = array_filter($body, static function($value) {
           return ! is_null($value) && strlen(trim(strval($value))) > 0;
        });
        $body = array_map(static function($value) {
            return strval($value);
        }, $body);

        return $body;
    }

    private function findDraft(): ?int
    {
        if (! $this->locale || ! $this->subject) {
            return null;
        }

        $response = $this->client->get(Resources::$Campaigndraft, [
            'Filters' => $this->bodyUnique(),
        ]);
        //$this->log->write('findDraft', 'debug', $response->getData());

        return $response->success() && count($response->getData()) ?
            $response->getData()[0]['ID'] :
            null;
    }

    private function createDraft(): ?int
    {
        $response = $this->client->post(Resources::$Campaigndraft, [
            'Body' => $this->bodyFull(),
        ]);
        // $this->log->write('createDraft', 'debug', $response->getData());

        return $response->success() && count($response->getData()) ?
            $response->getData()[0]['ID'] :
            null;
    }

    public function status(): ?string
    {
        if (! $this->campaign) {
            return null;
        }

        $exists = $this->client->get(Resources::$CampaigndraftStatus, [
            'ID' => $this->campaign
        ]);

        return $exists->success() ? strtolower($exists->getData()[0]['Status']) : null;
    }

    public function statusSchedule(): ?string
    {
        if (! $this->campaign) {
            return null;
        }

        $exists = $this->client->get(Resources::$CampaigndraftSchedule, [
            'ID' => $this->campaign
        ]);

        return $exists->success() ? strtolower($exists->getData()[0]['Status']) : null;
    }

    public function test(string $email): bool
    {
        if (! $this->campaign) {
            return false;
        }

        $response = $this->client->post(Resources::$CampaigndraftTest, [
            'ID' => $this->campaign,
            'Body' => ['Recipients' => [['Email' => $email]]    ]
        ]);

        return $response->success() ? $response->getData()[0]['Status'] === 'draft' : false;
    }

    public function send(): bool
    {
        $response = $this->client->post(Resources::$CampaigndraftSend, [
            'ID' => $this->campaign
        ]);
        return $response->success() ? in_array($response->getData()[0]['Status'], ['programmed', 'sent']) : false;
    }

    public function schedule(): bool
    {
        if (! $this->campaign || ! $this->datetime) {
            return false;
        }
        $status = $this->statusSchedule();

        // new
        if (! $status) {
            $response = $this->client->post(Resources::$CampaigndraftSchedule, [
                'ID' => $this->campaign,
                'Body' => [
                    'Date' => substr(
                        $this->datetime->format('c'),
                        0,
                        strlen('2018-01-01T00:00:00')
                    )
                ]
            ]);

            return $response->success() ? $response->getData()[0]['Status'] === 'programmed' : false;
        }

        // put
        if (in_array($status, ['programmed', 'draft'])) {
            $response = $this->client->put(Resources::$CampaigndraftSchedule, [
                'ID' => $this->campaign,
                'Body' => [
                    'Date' => substr(
                        $this->datetime->format('c'),
                        0,
                        strlen('2018-01-01T00:00:00')
                    )
                ]
            ]);

            return $response->success() ? $response->getData()[0]['Status'] === 'programmed' : false;
        }

        return false;
    }

    public function cancelSchedule(): bool
    {
        if (! $this->campaign) {
            return false;
        }

        $response = $this->client->delete(Resources::$CampaigndraftSchedule, [
            'ID' => $this->campaign
        ]);

        return $response->getStatus() === 204;
    }

    public static function scheduled(): array
    {
        $response = \mailjet()->client()->get(Resources::$Campaigndraft, [
            'Filters' => [
                'Status' => '1',
                //'Status' => '0', // '0' = ALL, else comma seperated list
                // -2 : deleted
                // -1 : archived draft
                // 0 : draft
                // 1 : programmed => schedule
                // 2 : sent
                // 3 : A/X testing
            ]
        ]);

        return $response->success() && count($response->getData()) ?
            array_map(static function($value) {
                return [
                    'Name' => $value['Subject'],
                    'Value' => $value['ID'],
                ];
            }, $response->getData()) : [];
    }
}
