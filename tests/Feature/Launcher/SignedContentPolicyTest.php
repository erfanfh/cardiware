<?php

namespace Tests\Feature\Launcher;

use App\Application\Launcher\Actions\Security\IssueOfflineLicenseAction;
use App\Application\Launcher\Actions\Security\SignContentProtectionPolicyAction;
use App\Application\Launcher\Exceptions\DeviceTransferRequiredException;
use App\Domain\Identity\Models\User;
use App\Domain\Launcher\Models\LauncherDevice;
use App\Http\Requests\Api\V1\Identity\LoginRequest;
use App\Http\Requests\Api\V1\Identity\LoginWithLauncherOtpRequest;
use App\Infrastructure\Services\Setting\BaseSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tests\Support\UsesEphemeralLauncherSigningKeys;
use Tests\TestCase;

class SignedContentPolicyTest extends TestCase
{
    use RefreshDatabase, UsesEphemeralLauncherSigningKeys;

    protected function setUp(): void
    {
        parent::setUp();
        BaseSettings::clearResolvedGroups();
        $this->useEphemeralLauncherSigningKeys();
    }

    protected function tearDown(): void
    {
        BaseSettings::clearResolvedGroups();
        parent::tearDown();
    }

    public function test_policy_signs_the_exact_utf8_json_and_binds_the_case_sensitive_content_key(): void
    {
        $policy = ['strict' => true, 'gate_files' => ['Data\\بازی.bin'],
            'modules' => [['path' => 'modules/main.lua', 'tier' => 'content']]];
        $signed = app(SignContentProtectionPolicyAction::class)->execute($policy, 'Game-KEY-01');
        $this->assertSame(1, $this->verifyPolicySignature($signed));
        $this->assertSame($policy + ['content_key_id' => 'Game-KEY-01'], json_decode($signed['content_protection_json'], true));
        $this->assertSame(hash('sha256', $this->signingPublicKey), $signed['content_protection_key_id']);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $signed['content_protection_signature']);
        $signed['content_protection_json'] = str_replace('Game-KEY-01', 'game-key-01', $signed['content_protection_json']);
        $this->assertSame(0, $this->verifyPolicySignature($signed));
    }

    public function test_policy_and_offline_license_use_the_same_signing_key(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['active_device_slot' => 1])->save();
        $device = LauncherDevice::query()->create([
            'user_id' => $user->id, 'device_id' => (string) Str::uuid(), 'name' => 'Test PC',
            'platform' => 'windows', 'app_version' => '1.21.118', 'device_slot' => 1,
            'public_key_fingerprint' => str_repeat('a', 64), 'fingerprint_hash' => str_repeat('b', 64),
        ]);
        $license = app(IssueOfflineLicenseAction::class)->execute($user, $device);
        $signed = app(SignContentProtectionPolicyAction::class)->execute(['strict' => true], 'protected-game');
        $this->assertSame($license['key_id'], $signed['content_protection_key_id']);
        $this->assertSame(1, $this->verifyPolicySignature($signed));
    }

    public function test_both_login_requests_accept_optional_hardware_hashes_and_reject_invalid_values(): void
    {
        foreach ([new LoginRequest, new LoginWithLauncherOtpRequest] as $request) {
            $rule = $request->rules()['device.hardware_fingerprint_hash'];
            foreach ([null, str_repeat('a', 64)] as $value) {
                $this->assertTrue(Validator::make(['hash' => $value], ['hash' => $rule])->passes());
            }
            foreach (['bad', str_repeat('A', 64), ['not-a-string']] as $value) {
                $this->assertTrue(Validator::make(['hash' => $value], ['hash' => $rule])->fails());
            }
        }
    }

    public function test_transfer_response_describes_the_configured_cooldown(): void
    {
        config(['launcher.device_transfer_cooldown_days' => 30]);
        $request = Request::create('/api/v1/auth/login');
        $details = (new DeviceTransferRequiredException(true))->render($request)->getData(true)['details'];
        $this->assertFalse($details['transfer_is_final']);
        $this->assertSame(30, $details['transfer_cooldown_days']);
        config(['launcher.device_transfer_cooldown_days' => 0]);
        $details = (new DeviceTransferRequiredException(true))->render($request)->getData(true)['details'];
        $this->assertTrue($details['transfer_is_final']);
    }
}
