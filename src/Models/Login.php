<?php

namespace Sentry\Models;

use Sentry\Interfaces\AuthEvent;

class Login implements AuthEvent
{
    public function __construct(
        private string $ip,
        private string $username,
        private int $score
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