<?php

namespace Sentry\Services;

class Storage
{
    private static ?Storage $instance = NULL;
    private string $root_dir;

    private function __construct()
    {
        $this->root_dir = dirname(__DIR__, 2);
    }

    public static function getInstance(): self
    {
        if (self::$instance === NULL) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function getPath(string $pathKey): string
    {
        $instance = self::getInstance();
        $paths    = Config::get('paths');
        if (isset($paths[$pathKey])) {
            return $instance->root_dir . '/' . $paths[$pathKey];
        }

        throw new \Exception("Invalid path requested: $pathKey");
    }

    public static function runScript(string $script, array $args = []): string
    {
        $script = self::getPath($script);

        echo "Running script: $script\n";

        if (!file_exists($script)) {
            echo "✗ Error: $script not found\n\n";
            // throw new \Exception("Error: $script not found\n");
            return '';

        }

        if (!is_executable($script)) {
            // throw new \Exception("Error: $script is not executable\n");
            echo "✗ Error: $script is not executable\n\n";
            return '';
        }

        // Build command with escaped arguments
        $command = escapeshellcmd($script);
        foreach ($args as $arg) {
            $command .= ' ' . escapeshellarg($arg);
        }

        exec($command, $output, $returnCode);
        $output = is_array($output) ? implode("\n", $output) : $output;

        if ($returnCode !== 0) {
            throw new \Exception("Error: Script execution \n\"$command\"\nfailed with code $returnCode and message: \n\"$output\"\n");
        }

        return $output;
    }

    public static function write(string $path, string $content): void
    {
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, TRUE);
        }
        file_put_contents($path, $content);
    }

    public static function read(string $path): array
    {
        if (!file_exists($path)) {
            echo "✗ Error: $path not found\n\n";
            return [];
        }

        return file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    }

    public static function delete(string $path): void
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }

    private function __clone() {}
}