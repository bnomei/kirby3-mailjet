<?php

declare(strict_types=1);

namespace Bnomei;

final class MailjetLog
{
    /**
     * @var bool
     */
    private $debug;
    /**
     * @var callable
     */
    private $logger;

    public function __construct(bool $debug, callable $log)
    {
        $this->debug = $debug;
        $this->logger = $log;
    }

    public function write(string $msg = '', string $level = 'info', ?array $context = null): bool
    {
        if ($this->logger && is_callable($this->logger)) {
            if (!$this->debug && $level == 'debug') {
                // skip but...
                return true;
            } else {
                $log = $this->logger;
                return $log($msg, $level, $context ?? []);
            }
        }
        return false;
    }
}
