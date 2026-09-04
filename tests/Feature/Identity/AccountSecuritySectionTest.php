<?php

namespace Tests\Feature\Identity;

use App\Application\Identity\Jobs\SendOtpSmsJob;
use App\Application\Identity\Support\OtpCodeHasher;
use App\Domain\Identity\Enums\OtpKeyTypeEnum;
use App\Domain\Identity\Enums\OtpTypeEnum;
use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\Otp;
use App\Domain\Identity\Models\User;
use App\Livewire\Front\Account\AccountSecuritySection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

final class AccountSecuritySectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('mobile')->unique();
            $table->string('status');
            $table->string('password');
            $table->timestamp('mobile_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('otps', function (Blueprint $table): void {
            $table->id();
            $table->string('key');
            $table->string('key_type');
            $table->string('type');
            $table->string('code_hash', 64)->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expired_at');
            $table->timestamp('resend_available_at')->nullable();
            $table->boolean('is_used')->default(false);
            $table->timestamps();
        });
    }

    public function test_user_can_request_a_dedicated_password_change_otp_without_refresh(): void
    {
        Queue::fake();

        $user = $this->createUser();

        $component = Livewire::actingAs($user)
            ->test(AccountSecuritySection::class)
            ->call('switchMethod', 'otp')
            ->assertSet('method', 'otp')
            ->assertSet('otpSent', false)
            ->call('requestOtp')
            ->assertSet('otpSent', true)
            ->assertDispatched('account-security-otp-timing-updated')
            ->assertSee('کد برای 0912***6789 ارسال شد');

        $this->assertGreaterThan(now()->timestamp, $component->get('otpResendAvailableAt'));

        $this->assertDatabaseHas('otps', [
            'key' => $user->mobile,
            'type' => OtpTypeEnum::ACCOUNT_PASSWORD_CHANGE->value,
            'is_used' => false,
        ]);

        Queue::assertPushed(
            SendOtpSmsJob::class,
            fn (SendOtpSmsJob $job): bool => $job->mobile === $user->mobile
        );
    }

    public function test_user_can_change_password_with_a_valid_account_otp(): void
    {
        $user = $this->createUser();
        $otp = $this->createOtp($user, code: '123456');

        Livewire::actingAs($user)
            ->test(AccountSecuritySection::class)
            ->set('method', 'otp')
            ->set('otpSent', true)
            ->set('otp_code', '۱۲۳۴۵۶')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('saveWithOtp')
            ->assertHasNoErrors()
            ->assertSet('method', 'current_password')
            ->assertSet('otpSent', false)
            ->assertSet('password', '')
            ->assertSee('با تایید شماره موبایل با موفقیت تغییر کرد');

        $this->assertTrue(Hash::check('new-password', $user->fresh()->getAuthPassword()));
        $this->assertTrue($otp->fresh()->is_used);
    }

    public function test_an_otp_issued_for_login_cannot_change_the_account_password(): void
    {
        $user = $this->createUser();
        $otp = $this->createOtp(
            user: $user,
            code: '123456',
            type: OtpTypeEnum::LOGIN,
        );

        Livewire::actingAs($user)
            ->test(AccountSecuritySection::class)
            ->set('method', 'otp')
            ->set('otpSent', true)
            ->set('otp_code', '123456')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('saveWithOtp')
            ->assertHasErrors('otp_code');

        $this->assertTrue(Hash::check('old-password', $user->fresh()->getAuthPassword()));
        $this->assertFalse($otp->fresh()->is_used);
    }

    public function test_an_incorrect_code_is_counted_and_does_not_change_the_password(): void
    {
        $user = $this->createUser();
        $otp = $this->createOtp($user, code: '123456');

        Livewire::actingAs($user)
            ->test(AccountSecuritySection::class)
            ->set('method', 'otp')
            ->set('otpSent', true)
            ->set('otp_code', '654321')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('saveWithOtp')
            ->assertHasErrors('otp_code');

        $this->assertSame(1, $otp->fresh()->attempts);
        $this->assertFalse($otp->fresh()->is_used);
        $this->assertTrue(Hash::check('old-password', $user->fresh()->getAuthPassword()));
    }

    public function test_reusing_the_current_password_does_not_consume_a_valid_otp(): void
    {
        $user = $this->createUser();
        $otp = $this->createOtp($user, code: '123456');

        Livewire::actingAs($user)
            ->test(AccountSecuritySection::class)
            ->set('method', 'otp')
            ->set('otpSent', true)
            ->set('otp_code', '123456')
            ->set('password', 'old-password')
            ->set('password_confirmation', 'old-password')
            ->call('saveWithOtp')
            ->assertHasErrors('password');

        $this->assertFalse($otp->fresh()->is_used);
        $this->assertTrue(Hash::check('old-password', $user->fresh()->getAuthPassword()));
    }

    public function test_the_existing_current_password_flow_still_changes_the_password(): void
    {
        $user = $this->createUser();

        Livewire::actingAs($user)
            ->test(AccountSecuritySection::class)
            ->set('current_password', 'old-password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('رمز عبور شما با موفقیت تغییر کرد');

        $this->assertTrue(Hash::check('new-password', $user->fresh()->getAuthPassword()));
    }

    private function createUser(): User
    {
        return User::query()->create([
            'mobile' => '09123456789',
            'status' => UserStatusEnum::ACTIVE,
            'password' => 'old-password',
            'mobile_verified_at' => now(),
        ]);
    }

    private function createOtp(
        User $user,
        string $code,
        OtpTypeEnum $type = OtpTypeEnum::ACCOUNT_PASSWORD_CHANGE,
    ): Otp {
        return Otp::query()->create([
            'key' => $user->mobile,
            'key_type' => OtpKeyTypeEnum::MOBILE,
            'type' => $type,
            'code_hash' => app(OtpCodeHasher::class)->make($code),
            'attempts' => 0,
            'expired_at' => now()->addMinutes(5),
            'resend_available_at' => now()->addMinutes(5),
            'is_used' => false,
        ]);
    }
}
