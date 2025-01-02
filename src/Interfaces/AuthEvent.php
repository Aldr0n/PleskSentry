<?php

namespace Sentry\Interfaces;

interface AuthEvent
{
    public function getScore(): int;
    public function getIp(): string;
    public function getUsername(): string;
}