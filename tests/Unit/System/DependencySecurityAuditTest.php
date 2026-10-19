<?php

namespace Tests\Unit\System;

use App\Application\System\Reports\DependencySecurityAudit;
use Illuminate\Support\Facades\Process;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

final class DependencySecurityAuditTest extends TestCase
{
    #[DataProvider('composerResults')]
    public function test_composer_reports_security_findings_without_confusing_failures_or_abandoned_packages(array $data, int $exitCode, ?bool $expected): void
    {
        Process::fake(['*' => Process::result(output: json_encode($data), exitCode: $exitCode)]);

        $this->assertSame($expected, app(DependencySecurityAudit::class)->composerNeedsAttention());
        Process::assertRan(fn ($process) => in_array('--locked', $process->command, true)
            && in_array('--no-plugins', $process->command, true)
            && $process->path === base_path() && $process->timeout === 120);
    }

    public static function composerResults(): array
    {
        return [
            'clean' => [['advisories' => [], 'abandoned' => []], 0, false],
            'vulnerable' => [['advisories' => ['vendor/package' => [['title' => 'Secret finding']]]], 1, true],
            'abandoned only' => [['advisories' => [], 'abandoned' => ['vendor/package' => null]], 2, false],
            'malware' => [['advisories' => [], 'malware' => ['vendor/package' => []]], 4, true],
            'registry error' => [['error' => 'Registry unavailable'], 1, null],
            'unexpected schema' => [[], 0, null],
            'failed with empty findings' => [['advisories' => []], 1, null],
        ];
    }

    #[DataProvider('npmResults')]
    public function test_npm_only_reports_critical_findings_and_marks_errors_unknown(array $data, int $exitCode, ?bool $expected): void
    {
        Process::fake(['*' => Process::result(output: json_encode($data), exitCode: $exitCode)]);

        $this->assertSame($expected, app(DependencySecurityAudit::class)->npmHasCriticalVulnerabilities());
        Process::assertRan(fn ($process) => in_array('--audit-level=critical', $process->command, true)
            && in_array('--include=dev', $process->command, true));
    }

    public static function npmResults(): array
    {
        return [
            'clean' => [['metadata' => ['vulnerabilities' => ['critical' => 0]]], 0, false],
            'high only' => [['metadata' => ['vulnerabilities' => ['high' => 9, 'critical' => 0]]], 0, false],
            'critical' => [['metadata' => ['vulnerabilities' => ['critical' => 2]]], 1, true],
            'registry error' => [['error' => ['code' => 'EAI_AGAIN']], 1, null],
            'error with metadata' => [['error' => ['code' => 'FAIL'], 'metadata' => ['vulnerabilities' => ['critical' => 0]]], 1, null],
            'unexpected schema' => [[], 0, null],
            'invalid count' => [['metadata' => ['vulnerabilities' => ['critical' => -1]]], 0, null],
            'execution failure' => [['metadata' => ['vulnerabilities' => ['critical' => 0]]], 127, null],
        ];
    }

    public function test_invalid_json_and_process_exceptions_are_unknown(): void
    {
        Process::fake(['*' => Process::result(output: 'not JSON', exitCode: 1)]);
        $this->assertNull(app(DependencySecurityAudit::class)->composerNeedsAttention());

        Process::fake(function () {
            throw new RuntimeException('Audit timed out or executable unavailable.');
        });
        $this->assertNull(app(DependencySecurityAudit::class)->npmHasCriticalVulnerabilities());
    }
}
