<?php

namespace Tests\Feature\Launcher;

use App\Application\Identity\Support\OtpCodeHasher;
use App\Application\Launcher\Actions\Security\ResetLauncherDeviceSecurityAction;
use App\Application\Launcher\Exceptions\DeviceLimitReachedException;
use App\Domain\Identity\Enums\OtpTypeEnum;
use App\Domain\Identity\Models\Otp;
use App\Domain\Identity\Models\User;
use App\Domain\Launcher\Models\LauncherDevice;
use App\Http\Controllers\Api\V1\Identity\AuthController;
use App\Infrastructure\Services\Setting\BaseSettings;
use App\Infrastructure\Services\Setting\Groups\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class HardwareReactivationTest extends TestCase
{
    use RefreshDatabase;

    private array $deviceKeys = [];

    protected function setUp(): void
    {
        parent::setUp();
        BaseSettings::clearResolvedGroups();
        Cache::flush();
        Queue::fake();
        $this->withoutMiddleware(ThrottleRequests::class);
        $this->travelTo(now()->startOfDay()->addHours(12));
        config(['launcher.device_transfer_cooldown_days' => 30, 'launcher.device_reactivation_limit' => 3,
            'launcher.device_reactivation_window_days' => 30]);
        app(GeneralSettings::class)->set('launcher_maintenance_enabled', false);
        app(GeneralSettings::class)->set('launcher_emergency_offline_enabled', false);
    }

    protected function tearDown(): void
    {
        BaseSettings::clearResolvedGroups();
        parent::tearDown();
    }

    public function test_legacy_login_and_later_hardware_enrollment_preserve_existing_identity(): void
    {
        $user = $this->user();
        $legacy = $this->device();
        $this->login($user, $legacy)->assertOk();
        $this->assertNull($user->fresh()->first_device_hardware_fingerprint);
        $hash = str_repeat('a', 64);
        $this->login($user, $this->device($hash, $legacy))->assertOk();
        $this->login($user, $this->device(null, $legacy))->assertOk();
        $this->assertSame($hash, $user->fresh()->first_device_hardware_fingerprint);
        $device = $user->launcherDevices()->firstOrFail();
        $this->assertSame($hash, $device->hardware_fingerprint_hash);
        $this->assertArrayNotHasKey('hardware_fingerprint_hash', $device->toArray());
        $this->assertArrayNotHasKey('first_device_hardware_fingerprint', $user->fresh()->toArray());
        $this->assertDatabaseCount('launcher_device_reactivations', 0);
    }

    public function test_windows_reinstall_rebinds_the_active_slot_without_consuming_transfer(): void
    {
        $user = $this->user();
        $hash = str_repeat('a', 64);
        $this->login($user, $this->device($hash))->assertOk();
        $old = $user->launcherDevices()->firstOrFail();
        $transferredAt = now()->subDays(2);
        $user->forceFill(['device_transfer_used' => true, 'transferred_at' => $transferredAt])->save();
        $new = $this->device($hash);
        $this->login($user, $new)->assertOk()->assertJsonPath('data.device.slot', 1)->assertDontSee($hash);
        $fresh = $user->fresh();
        $this->assertSame($new['public_key'], $fresh->first_device_public_key);
        $this->assertTrue($fresh->device_transfer_used);
        $this->assertTrue($transferredAt->equalTo($fresh->transferred_at));
        $this->assertNotNull($old->fresh()->revoked_at);
        $this->assertSame(0, $old->tokens()->count());
        $this->assertSame(1, $user->tokens()->count());
        $this->assertDatabaseHas('launcher_device_reactivations', [
            'user_id' => $user->id, 'device_slot' => 1, 'new_public_key_fingerprint' => $new['public_key_fingerprint'],
        ]);
    }

    public function test_reactivation_limit_and_rolling_window_are_enforced(): void
    {
        config(['launcher.device_reactivation_limit' => 1]);
        $user = $this->user();
        $hash = str_repeat('b', 64);
        $this->login($user, $this->device($hash))->assertOk();
        $user->forceFill(['device_transfer_used' => true, 'transferred_at' => now()])->save();
        $this->login($user, $this->device($hash))->assertOk();
        $this->login($user, $this->device($hash))->assertStatus(409)->assertJsonPath('code', 'device_limit_reached');
        $this->assertDatabaseCount('launcher_device_reactivations', 1);
        DB::table('launcher_device_reactivations')->update(['created_at' => now()->subDays(31)]);
        $this->login($user, $this->device($hash))->assertOk();
        $this->assertDatabaseCount('launcher_device_reactivations', 2);
    }

    public function test_other_hardware_requires_an_otp_and_a_used_transfer_stays_blocked(): void
    {
        $user = $this->user();
        $this->login($user, $this->device(str_repeat('a', 64)))->assertOk();
        $new = $this->device(str_repeat('c', 64));
        $this->login($user, $new, '123456')->assertStatus(422)->assertJsonPath('code', 'invalid_otp');
        $this->assertDatabaseCount('launcher_devices', 1);
        $this->otp($user, OtpTypeEnum::DEVICE_TRANSFER);
        $this->login($user, $this->device(str_repeat('c', 64)) , '123456')->assertOk()->assertJsonPath('data.device.slot', 2);
        $firstSlotKey = $user->fresh()->first_device_public_key;
        $reactivated = $this->device(str_repeat('c', 64));
        $this->login($user, $reactivated)->assertOk()->assertJsonPath('data.device.slot', 2);
        $this->assertSame($reactivated['public_key'], $user->fresh()->second_device_public_key);
        $this->assertSame($firstSlotKey, $user->fresh()->first_device_public_key);
        $this->login($user, $this->device(str_repeat('d', 64)), '123456')
            ->assertStatus(409)->assertJsonPath('code', 'device_limit_reached');
        $this->assertDatabaseCount('launcher_device_reactivations', 1);
    }

    public function test_cooldown_expiry_allows_a_new_verified_transfer(): void
    {
        $user = $this->user();
        $this->login($user, $this->device(str_repeat('a', 64)))->assertOk();
        $user->forceFill(['device_transfer_used' => true, 'transferred_at' => now()->subDays(30)])->save();
        $this->assertFalse($user->isLauncherDeviceTransferBlocked());
        $this->otp($user, OtpTypeEnum::DEVICE_TRANSFER);
        $this->login($user, $this->device(str_repeat('b', 64)), '123456')->assertOk();
        $this->assertTrue($user->fresh()->isLauncherDeviceTransferBlocked());
        $this->assertSame(2, $user->fresh()->active_device_slot);
    }

    public function test_zero_cooldown_preserves_the_one_time_transfer_rule(): void
    {
        config(['launcher.device_transfer_cooldown_days' => 0]);
        $user = $this->user();
        $this->login($user, $this->device())->assertOk();
        $user->forceFill(['device_transfer_used' => true, 'transferred_at' => now()->subYear()])->save();
        $this->login($user, $this->device())->assertStatus(409)->assertJsonPath('code', 'device_limit_reached');
    }

    public function test_login_otp_also_supports_hardware_reactivation(): void
    {
        $user = $this->user();
        $hash = str_repeat('d', 64);
        $this->login($user, $this->device($hash))->assertOk();
        $otp = $this->otp($user, OtpTypeEnum::LOGIN);
        $this->postJson(route('api.v1.auth.otp.verify'), [
            'mobile' => $user->mobile, 'otp_code' => '123456', 'device' => $this->device($hash),
        ])->assertOk();
        $this->assertTrue($otp->fresh()->is_used);
        $this->assertDatabaseCount('launcher_device_reactivations', 1);
        $this->assertFalse($user->fresh()->device_transfer_used);
    }

    public function test_a_matching_hardware_hash_does_not_bypass_device_proof_or_emergency_pause(): void
    {
        $user = $this->user();
        $hash = str_repeat('e', 64);
        $old = $this->device($hash);
        $this->login($user, $old)->assertOk();
        $invalid = $this->device($hash);
        $invalid['proof_signature'] = base64_encode('invalid');
        $this->login($user, $invalid)->assertStatus(422)->assertJsonPath('code', 'invalid_device_proof');
        $mismatched = $old;
        $mismatched['fingerprint_hash'] = str_repeat('0', 64);
        $this->login($user, $this->device($hash, $mismatched))
            ->assertStatus(409)->assertJsonPath('code', 'device_fingerprint_mismatch');
        app(GeneralSettings::class)->set('launcher_emergency_offline_enabled', true);
        $this->login($user, $this->device($hash))->assertStatus(409)->assertJsonPath('code', 'device_transfer_paused');
        $this->login($user, $this->device($hash, $old))->assertOk();
        $this->assertDatabaseCount('launcher_device_reactivations', 0);
    }

    public function test_reactivation_is_rechecked_under_the_account_lock(): void
    {
        config(['launcher.device_reactivation_limit' => 1]);
        $user = $this->user();
        $hash = str_repeat('a', 64);
        $this->login($user, $this->device($hash))->assertOk();
        $user->forceFill(['device_transfer_used' => true, 'transferred_at' => now()])->save();
        $controller = app(AuthController::class);
        $new = $this->device($hash);
        $classify = new \ReflectionMethod($controller, 'isDeviceTransfer');
        $this->assertFalse($classify->invoke($controller, $user->fresh(), $new));
        DB::table('launcher_device_reactivations')->insert([
            'user_id' => $user->id, 'device_slot' => 1, 'hardware_fingerprint_hash' => $hash,
            'new_public_key_fingerprint' => str_repeat('f', 64), 'created_at' => now(),
        ]);
        $complete = new \ReflectionMethod($controller, 'completeDeviceLogin');
        $this->expectException(DeviceLimitReachedException::class);
        $complete->invoke($controller, $user->id, $new, '127.0.0.1', false);
    }

    public function test_admin_reset_clears_both_hardware_slots_and_revokes_tokens(): void
    {
        $user = $this->user();
        $this->login($user, $this->device(str_repeat('a', 64)))->assertOk();
        $user->forceFill(['second_device_hardware_fingerprint' => str_repeat('b', 64)])->save();
        app(ResetLauncherDeviceSecurityAction::class)->execute($user, 'Test reset');
        $this->assertNull($user->fresh()->first_device_hardware_fingerprint);
        $this->assertNull($user->fresh()->second_device_hardware_fingerprint);
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_the_new_security_events_are_accepted_and_unknown_events_are_ignored(): void
    {
        $user = $this->user();
        $token = $this->login($user, $this->device())->assertOk()->json('data.access_token');
        $types = ['content_policy_invalid', 'launcher_runtime_injection_env', 'gate_host_file_present',
            'gate_host_config_tampered', 'sider_exe_unlisted'];
        $events = array_map(fn ($type) => ['type' => $type, 'subject' => 'runtime.dll'], [...$types, 'future_unknown_type']);
        $this->withToken($token)->postJson(route('api.v1.launcher.security-events'), [
            'app_version' => '1.21.118', 'events' => $events,
        ])->assertNoContent();
        $this->assertDatabaseCount('launcher_security_events', 5);
        $this->assertDatabaseHas('launcher_security_events', ['event_type' => 'content_policy_invalid', 'severity' => 3]);
        $this->assertDatabaseHas('launcher_security_events', ['event_type' => 'sider_exe_unlisted', 'severity' => 2]);
    }

    public function test_migration_rolls_back_and_reapplies_without_losing_existing_users(): void
    {
        $user = $this->user();
        $migration = require database_path('migrations/2026_11_13_000001_add_launcher_hardware_fingerprint_and_reactivations.php');
        $migration->down();
        $this->assertFalse(Schema::hasTable('launcher_device_reactivations'));
        $this->assertFalse(Schema::hasColumn('users', 'first_device_hardware_fingerprint'));
        $migration->up();
        $this->assertTrue(Schema::hasColumn('launcher_devices', 'hardware_fingerprint_hash'));
        $this->assertNotNull($user->fresh());
        $this->assertNull($user->fresh()->first_device_hardware_fingerprint);
    }

    private function user(): User
    {
        return User::factory()->create(['password' => 'CorrectHorse123!', 'mobile' => '09120000001']);
    }

    private function login(User $user, array $device, ?string $code = null): \Illuminate\Testing\TestResponse
    {
        return $this->postJson(route('api.v1.auth.login'), [
            'login' => $user->email, 'password' => 'CorrectHorse123!',
            'device' => $device, 'device_transfer_code' => $code,
        ]);
    }

    private function otp(User $user, OtpTypeEnum $type): Otp
    {
        return Otp::query()->create([
            'key' => $user->mobile, 'key_type' => 'mobile', 'type' => $type,
            'code_hash' => app(OtpCodeHasher::class)->make('123456'), 'attempts' => 0,
            'is_used' => false, 'expired_at' => now()->addMinutes(5), 'resend_available_at' => now()->addMinute(),
        ]);
    }

    private function device(?string $hardware = null, ?array $identity = null): array
    {
        if ($identity === null) {
            $key = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1']);
            $pem = openssl_pkey_get_details($key)['key'];
            $der = base64_decode(preg_replace('/-----[^-]+-----|\s+/', '', $pem), true);
            $identity = ['id' => (string) Str::uuid(), 'name' => 'Test PC', 'platform' => 'windows',
                'app_version' => '1.21.118', 'public_key' => base64_encode($der),
                'public_key_fingerprint' => hash('sha256', $der), 'fingerprint_hash' => bin2hex(random_bytes(32))];
            $this->deviceKeys[$identity['id']] = $key;
        }
        if ($hardware === null) {
            unset($identity['hardware_fingerprint_hash']);
        } else {
            $identity['hardware_fingerprint_hash'] = $hardware;
        }
        $identity['proof_timestamp'] = now()->timestamp;
        $identity['proof_nonce'] = base64_encode(random_bytes(32));
        $canonical = implode("\n", ['MWLauncher.DeviceProof.v1', $identity['id'], $identity['fingerprint_hash'],
            $identity['public_key_fingerprint'], (string) $identity['proof_timestamp'], $identity['proof_nonce']]);
        openssl_sign($canonical, $signature, $this->deviceKeys[$identity['id']], OPENSSL_ALGO_SHA256);
        $identity['proof_signature'] = base64_encode($signature);

        return $identity;
    }
}
