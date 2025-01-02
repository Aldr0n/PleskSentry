<?php

namespace Sentry\Models;

use Sentry\Interfaces\AuthEvent;
class Violation implements AuthEvent
{
    public function __construct(
        private string $ip,
        private string $username,
        private int $score,
        public bool $isMailboxAttack,
        public bool $mailboxExists,
        public string $original_line,
    ) {}

    public function getScore(): int
    {
        return $this->score;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getUsername(): string
    {
        return $this->username;
    }
}