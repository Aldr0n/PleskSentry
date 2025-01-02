<?php

namespace Sentry\Services;

class Logger
{
    private static ?Logger $instance = NULL;
    private Storage $storage;

    private function __construct()
    {
        $this->storage = Storage::getInstance();
    }

    private static function getInstance(): self
    {
        if (self::$instance === NULL) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function info(string $message): void
    {
        self::log('info', $message);
    }

    public static function error(string $message): void
    {
        self::log('error', $message);
    }

    private static function log(string $level, string $message): void
    {
        $log_path = Storage::getPath('operations_log');

        if (!is_dir(dirname($log_path))) {
            mkdir(dirname($log_path), 0755, TRUE);
        }

        $timezone = exec('timedatectl | grep "Time zone" | cut -d ":" -f2 | cut -d " " -f2');
        if ($timezone) {
            date_default_timezone_set(trim($timezone));
        }

        $timestamp        = date('Y-m-d H:i:s');
        $padding          = str_repeat(' ', strlen($timestamp) + 3);
        $formattedMessage = str_replace("\n", "\n" . $padding, $message);

        file_put_contents($log_path, "$timestamp - $formattedMessage\n", FILE_APPEND);
    }

    private function __clone() {}
}