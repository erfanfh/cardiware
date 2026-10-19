<?php

namespace App\Application\System\Reports;

use Illuminate\Support\Facades\Process;
use Throwable;

final class DependencySecurityAudit
{
    // null means the audit could not establish a result, never a clean bill of health.
    public function composerNeedsAttention(): ?bool
    {
        $result = $this->run([
            config('system-report.composer_binary'), 'audit', '--locked', '--format=json',
            '--no-interaction', '--no-ansi', '--no-plugins', '--no-scripts',
        ]);
        $data = $result['data'] ?? null;

        if (! is_array($data) || isset($data['error']) || ! is_array($data['advisories'] ?? null)) {
            return null;
        }

        if ($data['advisories'] !== [] || ! empty($data['malware'])) {
            return true;
        }

        // Abandoned packages alone are not security advisories, but may set a nonzero exit code.
        return $result['exit_code'] === 0 || ! empty($data['abandoned']) ? false : null;
    }

    public function npmHasCriticalVulnerabilities(): ?bool
    {
        $result = $this->run([
            config('system-report.npm_binary'), 'audit', '--json', '--audit-level=critical', '--include=dev',
        ]);
        $data = $result['data'] ?? null;
        $critical = $data['metadata']['vulnerabilities']['critical'] ?? null;

        if (isset($data['error']) || ! is_int($critical) || $critical < 0
            || ! in_array($result['exit_code'] ?? null, [0, 1], true)) {
            return null;
        }

        return $critical > 0;
    }

    private function run(array $command): ?array
    {
        try {
            $result = Process::path(base_path())
                ->timeout(config('system-report.audit_timeout'))
                ->run($command);

            return [
                'data' => json_decode($result->output(), true, flags: JSON_THROW_ON_ERROR),
                'exit_code' => $result->exitCode(),
            ];
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }
}
