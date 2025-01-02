<?php

namespace Sentry;

use Sentry\Services\Warden;
use Sentry\Services\Storage;
use Sentry\Services\Inspector;

class Sentry
{
    private bool $dry_run;
    private Inspector $inspector;
    private Warden $warden;

    public function __construct(
        string $input_log,
        bool $dry_run = FALSE
    ) {
        $this->dry_run = $dry_run;

        if (!file_exists($input_log)) {
            echo "Log file not found\n";
            return;
        }

        $this->inspector = new Inspector($input_log);
        $this->warden    = new Warden();
        $this->process();
    }

    private function process(): void
    {
        $this->inspector->analyzeLogFile();
        $this->warden->banIps($this->inspector->getViolations());
        $this->cleanup();
    }

    private function cleanup(): void
    {
        Storage::delete(Storage::getPath('banned_ips'));
        Storage::delete(Storage::getPath('trusted_ips'));
        Storage::delete(Storage::getPath('ips_to_ban'));

        echo "✓ Cleanup complete\n\n";
    }
}