<?php

namespace Sentry\Services;

class Config
{
    private static ?Config $instance = NULL;
    private static array $config = [];

    private function __construct()
    {
        self::loadConfig('sentry');
        self::loadConfig('inspector');
    }

    private static function loadConfig(string $name): void
    {
        $configPath = dirname(__DIR__, 2) . '/config/' . $name . '.php';
        if (file_exists($configPath)) {
            self::$config = array_merge(self::$config, require $configPath);
        }
    }

    private static function getInstance(): self
    {
        if (self::$instance === NULL) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function get(string $key, mixed $default = NULL): mixed
    {
        self::getInstance();

        $keys  = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $segment) {
            if (!isset($value[$segment])) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    private function __clone() {}
}