<?php

namespace Sentry\Services;

use Sentry\Models\Violation;
use Sentry\Models\Login;

class Inspector
{
    private static ?Inspector $instance = NULL;
    private array $banned_ips = [];
    private string $input_log;
    private array $unsorted_violations = [];
    private array $sorted_violations = [];
    private array $context_window = [];
    private array $auth_events = [];

    public function __construct(string $input_log)
    {
        $this->input_log = $input_log;
    }

    private static function getInstance(): self
    {
        if (self::$instance === NULL) {
            throw new \Exception('Violations instance not initialized');
        }
        return self::$instance;
    }

    public function analyzeLogFile(): void
    {
        $logFile = fopen($this->input_log, "r");

        while (!feof($logFile)) {
            $line = trim(fgets($logFile));

            // Maintain sliding window
            $this->context_window[] = $line;
            if (count($this->context_window) > Config::get('window_size')) {
                array_shift($this->context_window);
            }

            if ($this->isAuthError($line)) {
                $violation = $this->createViolation($line);
                if ($violation) {
                    $this->auth_events[] = $violation;
                }
            }
            else if ($this->isAuthSuccess($line)) {
                $success = $this->createAuthSucess($line);
                if ($success) {
                    $this->auth_events[] = $success;
                }
            }
        }
        fclose($logFile);

        $weighted_violations = $this->sortViolations();
        // $this->visualizeViolations($weighted_violations);

        // Store IPs that exceed the minimum score
        $this->sorted_violations = array_keys(array_filter(
            $weighted_violations,
            fn($data) => $data['total_score'] >= Config::get('bannable_score')
        ));

        echo "✓ Scan of $this->input_log complete\n\n";
    }

    private function createViolation(string $line): Violation|null
    {
        $username        = '';
        $ip              = '';
        $isMailboxAttack = FALSE;
        $mailboxExists   = FALSE;
        $score           = Config::get('violation_base_score');

        // Match IP pattern, skipping the first bracketed number (process ID)
        if (preg_match('/\[\d+\].*\[([\d.]+)\]/', $line, $matches)) {
            $ip = $matches[1];
        }

        if ($ip === '') return NULL;

        // Match sasl_username=value pattern, where value can be any non-whitespace character(s)
        if (preg_match('/sasl_username=(\S+)/', $line, $matches)) {
            $username = $matches[1];
        }

        if ($username && $this->hasSuccessfulLoginInContext($username)) {
            $mailboxExists = TRUE;
        }

        if ($username && $this->hasMailboxErrorInContext($username)) {
            $isMailboxAttack = TRUE;
            $score += Config::get('mailbox_attack_score');
        }

        return new Violation($ip, $username, $score, $isMailboxAttack, $mailboxExists, $line);
    }

    private function createAuthSucess(string $line): ?Login
    {
        $username = '';
        $ip       = '';
        $score    = Config::get('auth_success_score');

        // Extract IP from rip=x.x.x.x
        if (preg_match('/rip=([\d.]+)/', $line, $matches)) {
            $ip = $matches[1];
        }

        // Extract username, handling both <user> and user formats
        if (preg_match('/user=<?([^>,]+)>?/', $line, $matches)) {
            $username = $matches[1];
        }

        if ($ip === '' || $username === '') return NULL;

        return new Login($ip, $username, $score);
    }

    private function sortViolations(): array
    {
        $weighted_violations = [];

        // Group all auth events by IP
        foreach ($this->auth_events as $event) {
            if (!isset($weighted_violations[$event->getIp()])) {
                $weighted_violations[$event->getIp()] = [
                    'total_score' => 0,
                    'events'      => [],
                ];
            }

            $weighted_violations[$event->getIp()]['total_score'] += $event->getScore();
            $weighted_violations[$event->getIp()]['events'][]    = $event;
        }

        // Sort by total score in descending order
        uasort($weighted_violations, function ($a, $b)
        {
            return $b['total_score'] <=> $a['total_score'];
        });

        return $weighted_violations;
    }

    private function hasMailboxErrorInContext(string $mailbox_name): bool
    {
        foreach ($this->context_window as $line) {
            if (str_contains($line, "No such user '$mailbox_name'")) {
                return TRUE;
            }
        }
        return FALSE;
    }

    private function isAuthError(string $line): bool
    {
        $auth_errors = [
            'SASL LOGIN authentication failed',
            'SASL PLAIN authentication failed',
        ];

        foreach ($auth_errors as $error) {
            if (str_contains($line, $error)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    private function hasSuccessfulLoginInContext(string $username): bool
    {
        foreach ($this->context_window as $line) {
            if (
                str_contains($line, "imap-login: Login: user=<$username>") ||
                str_contains($line, "Login: user=$username")
            ) {
                return TRUE;
            }
        }
        return FALSE;
    }

    private function isAuthSuccess(string $line): bool
    {
        return str_contains($line, "imap-login: Login: user=");
    }

    private function visualizeViolations(array $violations): void
    {
        // Replace print_r with formatted output
        foreach ($violations as $ip => $data) {
            if ($data['total_score'] >= Config::get('bannable_score')) {
                echo sprintf("%s:<strong style='color: red;'>%d</strong>  ", $ip, $data['total_score']);
            }
            else {
                echo sprintf("<span style='color: grey;'>%s</span>:<strong style='color: lightgreen;'>%d</strong>  ", $ip, $data['total_score']);
            }
        }

        echo "\n\n";
    }

    public function getViolations(): array
    {
        return $this->sorted_violations;
    }
}