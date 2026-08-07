<?php

namespace Tests\Unit\Launcher;

use Tests\TestCase;

class LauncherConfigurationTest extends TestCase
{
    public function test_security_defaults_are_safe(): void
    {
        $this->assertSame(2, config('launcher.max_active_devices_per_user'));
        $this->assertGreaterThanOrEqual(1, config('launcher.download_url_ttl_minutes'));
        $this->assertGreaterThanOrEqual(1, config('launcher.checkout_session_ttl_minutes'));
    }

    public function test_only_configured_download_hosts_are_exposed(): void
    {
        $hosts = config('launcher.allowed_download_hosts');

        $this->assertIsArray($hosts);
        $this->assertSame(
            $hosts,
            array_values(array_filter(array_map('trim', $hosts)))
        );
    }
}
