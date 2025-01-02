<?php

namespace Sentry\Services;

class Warden
{
    private array $banned_ips = [];
    private array $trusted_ips = [];

    public function __construct()
    {
        $this->banned_ips  = $this->fetchBannedIps();
        $this->trusted_ips = $this->fetchTrustedIps();
    }

    public function fetchBannedIps(): array
    {
        $banned_ips_file = Storage::getPath('banned_ips');
        Storage::runScript('fetch_banned_script', [$banned_ips_file]);

        $bannedIps = [];
        $output    = Storage::read($banned_ips_file);

        if (!$output) return [];

        // remove header
        array_shift($output);

        foreach ($output as $line) {
            $parts = preg_split('/\s+/', trim($line));

            if (count($parts) >= 2) {
                $bannedIps[] = $parts[0];
            }
        }

        return $bannedIps;
    }

    public function fetchTrustedIps(): array
    {
        $trusted_ips_file = Storage::getPath('trusted_ips');
        Storage::runScript('fetch_trusted_script', [$trusted_ips_file]);

        $trusted_ips = Storage::read($trusted_ips_file);

        if (!$trusted_ips) return [];

        // remove header
        array_shift($trusted_ips);

        return $trusted_ips;
    }

    public function banIps(array $ips): void
    {
        $ips_to_ban_path = Storage::getPath('ips_to_ban');
        $ips_to_ban      = array_filter($ips, function ($ip)
        {
            return $this->isBannableIp($ip);
        });

        $this->printReport($ips_to_ban);

        if (!empty($ips_to_ban)) {
            Storage::write($ips_to_ban_path, implode(', ', $ips_to_ban));
            Storage::runScript('ban_script', [Config::get('default_jail'), $ips_to_ban_path]);
            Logger::info("Banned IPs: " . implode(', ', $ips_to_ban));
        }
        else {
            Logger::info("No new IPs to ban");
        }

        echo "✓ Ban complete\n\n";
    }

    private function isBannableIp(string $ip): bool
    {
        $isAlreadyBanned = in_array($ip, $this->banned_ips);
        $isTrusted       = in_array($ip, $this->trusted_ips);

        return !$isAlreadyBanned && !$isTrusted;
    }

    private function printReport(array $ips_to_ban): void
    {
        echo "✓ Found " . count($ips_to_ban) . " new ips to ban";
        if (!empty($ips_to_ban)) {
            echo ":\n" . implode(', ', $ips_to_ban);
        }
        echo "\n\n";
    }

}