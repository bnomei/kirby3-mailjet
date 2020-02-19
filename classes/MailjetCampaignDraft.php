<?php

declare(strict_types=1);

namespace Bnomei;

use Mailjet\Client;
use Mailjet\Resources;

final class MailjetCampaignDraft
{
    /** @var Client */
    private $client;
    /** @var string|null */
    private $locale;
    /** @var string|null */
    private $name;
    /** @var string */
    private $from;
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

    public static $fluentProperties = [
        'locale',
        'name',
        'from',
        'to',
        'subject',
        'title',
        'datetime',
        'template',
        'text',
        'html',
    ];
    /** @var int|null */
    private $segment;
    /** @var int|null */
    private $contactslist;
    /** @var int|null */
    private $campaign;

    public function __construct(Client $client)
    {
        $this->client = $client;
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
        }

        return null;
    }

    private function set(string $name, $value): self
    {
        $setters = array_map(static function($item) {
            return 'set' . ucfirst($item);
        }, static::$fluentProperties);

        $key = array_search($name, $setters);
        if ($key !== false) {
            $this->{static::$fluentProperties[$key]} = $value;
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
        }

        $response = $this->client->post(Resources::$CampaigndraftDetailcontent, [
            'ID' => $this->campaign,
            'Body' => $this->detailContent(),
        ]);

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
            'Sender' => $this->name,
            'SenderEmail' => $this->from,
            'Subject' => $this->subject,
            'Title' => $this->title,
            'Url' => $this->url,
            'Contactslist' => $this->contactslist,
            'SegmentationID' => $this->segment,
            'TemplateID' => $this->template,
        ];

        $body = array_filter($body, static function($value) {
           return ! is_null($value) && strlen(trim(strval($value))) > 0;
        });

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
        return $response->success() && count($response->getData()) ?
            $response->getData()[0]['ID'] :
            null;
    }

    private function createDraft(): ?int
    {
        $response = $this->client->post(Resources::$Campaigndraft, [
            'Body' => $this->bodyFull(),
        ]);

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

        return $exists->success() ? $exists->getData()[0]['Status'] : null;
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
        $response = $this->client->post(Resources::$CampaigndraftSchedule, [
            'ID' => $this->campaign
        ]);
        return $response->success() ? in_array($response->getData()['Data'][0]['Status'], ['programmed', 'sent']) : false;
    }

    public function schedule(): bool
    {
        if (! $this->campaign || ! $this->datetime) {
            return false;
        }
        $status = $this->status();

        // new
        if (! $status) {
            $response = $this->client->post(Resources::$CampaigndraftSchedule, [
                'ID' => $this->campaign,
                'Body' => ['Date' => $this->datetime->format('c')]
            ]);
            return $response->success() ? $response->getData()['Data'][0]['Status'] === 'Programmed' : false;
        }

        // put
        if (in_array($status, ['Programmed', 'Draft'])) {
            $response = $this->client->put(Resources::$CampaigndraftSchedule, [
                'ID' => $this->campaign,
                'Body' => ['Date' => $this->datetime->format('c')]
            ]);
            return $response->success() ? $response->getData()['Data'][0]['Status'] === 'Programmed' : false;
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

        return $response->success() && count($response->getData()) ? $response->getData()[0]['Data'] : [];
    }
}
