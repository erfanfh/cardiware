<?php

namespace Tests\Feature\Identity;

use App\Application\Commerce\Actions\Cart\GetCurrentActiveCartAction;
use App\Application\Identity\Actions\Auth\LoginUserWithEmailAction;
use App\Application\Identity\Actions\Otp\CreateOtpAction;
use App\Application\Identity\Exceptions\InvalidCredentialsException;
use App\Application\Identity\Jobs\SendLoginAlertSmsJob;
use App\Application\Identity\Support\AuthRateLimitKeyFactory;
use App\Application\Shared\Jobs\SendEmailJob;
use App\Domain\Commerce\Enums\CartStatusEnum;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Identity\Enums\AuthFlowStepEnum;
use App\Domain\Identity\Enums\AuthRateLimitScopeEnum;
use App\Domain\Identity\Enums\OtpTypeEnum;
use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\User;
use App\Livewire\Front\Identity\AuthModal;
use App\Livewire\Front\Identity\User\Auth\AuthFlow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class EmailLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        $this->withoutVite();
        $this->startSession();
        request()->setLaravelSession(app('session.store'));
        // Livewire component tests skip the StartSession middleware on each request.
        $this->app->rebinding('request', static function ($app, $request): void {
            $request->setLaravelSession($app['session.store']);
        });
    }

    public static function components(): array
    {
        return [[AuthFlow::class], [AuthModal::class]];
    }

    #[DataProvider('components')]
    public function test_email_form_opens_from_the_first_step_and_back_clears_its_fields(string $component): void
    {
        Livewire::test($component)
            ->assertSet('step', AuthFlowStepEnum::ENTER_MOBILE)
            ->assertSee('ورود با ایمیل')
            ->assertDontSeeHtml('wire:submit.prevent="loginWithEmail"')
            ->set('mobile', '09123456789')
            ->call('goToEmailLogin')
            ->assertSet('step', AuthFlowStepEnum::LOGIN_EMAIL)
            ->assertSeeHtml('wire:submit.prevent="loginWithEmail"')
            ->set('email', 'person@example.com')
            ->set('password', 'secret')
            ->call('goToEnterMobile')
            ->assertSet('step', AuthFlowStepEnum::ENTER_MOBILE)
            ->assertSet('mobile', '09123456789')
            ->assertSet('resolvedMobile', '')
            ->assertSet('email', null)
            ->assertSet('password', '');
    }

    #[DataProvider('components')]
    public function test_email_login_authenticates_and_redirects_using_existing_completion(string $component): void
    {
        $user = User::factory()->create(['email' => 'person@example.com']);
        $oldSessionId = session()->getId();

        Livewire::test($component, $component === AuthModal::class ? ['redirectUrl' => '/cart'] : [])
            ->call('goToEmailLogin')
            ->set('email', '  PERSON@EXAMPLE.COM  ')
            ->set('password', 'password')
            ->call('loginWithEmail')
            ->assertHasNoErrors()
            ->assertSet('password', '')
            ->assertRedirect($component === AuthModal::class ? '/cart' : route('account.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($oldSessionId, session()->getId());
    }

    public static function failures(): array
    {
        $cases = [];

        foreach ([AuthFlow::class, AuthModal::class] as $component) {
            foreach (['missing', 'wrong-password', 'inactive', 'deleted'] as $reason) {
                $cases[] = [$component, $reason];
            }
        }

        return $cases;
    }

    #[DataProvider('failures')]
    public function test_failed_logins_have_the_same_generic_error_and_never_authenticate(string $component, string $reason): void
    {
        if ($reason !== 'missing') {
            $user = User::factory()->create([
                'email' => 'person@example.com',
                'status' => $reason === 'inactive' ? UserStatusEnum::INACTIVE : UserStatusEnum::ACTIVE,
            ]);

            if ($reason === 'deleted') {
                $user->delete();
            }
        }

        $result = Livewire::test($component)
            ->call('goToEmailLogin')
            ->set('email', 'person@example.com')
            ->set('password', $reason === 'wrong-password' ? 'wrong' : 'password')
            ->call('loginWithEmail')
            ->assertHasErrors('emailLogin')
            ->assertNoRedirect();

        $this->assertSame(['emailLogin' => ['اطلاعات وارد شده صحیح نیست.']], $result->errors()->messages());
        $this->assertGuest();
        Queue::assertNothingPushed();
    }

    #[DataProvider('components')]
    public function test_validation_errors_are_generic_and_do_not_query_email_existence(string $component): void
    {
        $result = Livewire::test($component)
            ->call('goToEmailLogin')
            ->set('email', 'invalid-email')
            ->call('loginWithEmail')
            ->assertHasErrors(['email', 'password'])
            ->assertNoRedirect();

        foreach ($result->errors()->all() as $message) {
            $this->assertSame('اطلاعات وارد شده صحیح نیست.', $message);
        }

        $this->assertGuest();
    }

    #[DataProvider('components')]
    public function test_rate_limited_login_has_the_same_generic_error_even_with_valid_credentials(string $component): void
    {
        $user = User::factory()->create(['email' => 'person@example.com']);
        config(['auth-flow.rate_limits.login_password.max_attempts' => 1]);

        $result = Livewire::test($component)
            ->call('goToEmailLogin')
            ->set('email', $user->email)
            ->set('password', 'wrong')
            ->call('loginWithEmail')
            ->set('password', 'password')
            ->call('loginWithEmail')
            ->assertHasErrors('emailLogin');

        $this->assertSame(['emailLogin' => ['اطلاعات وارد شده صحیح نیست.']], $result->errors()->messages());
        $this->assertGuest();
    }

    public function test_unknown_and_registered_emails_both_consume_the_login_rate_limit(): void
    {
        User::factory()->create(['email' => 'person@example.com']);

        foreach (['person@example.com', 'unknown@example.com'] as $email) {
            try {
                app(LoginUserWithEmailAction::class)->execute($email, 'wrong');
                $this->fail('Invalid credentials must be rejected.');
            } catch (InvalidCredentialsException $exception) {
                $this->assertSame('اطلاعات وارد شده صحیح نیست.', $exception->getMessage());
            }

            $key = app(AuthRateLimitKeyFactory::class)->make(AuthRateLimitScopeEnum::LOGIN_PASSWORD, $email);
            $this->assertSame(1, RateLimiter::attempts($key));
        }
    }

    public function test_modal_email_login_captures_the_guest_cart_before_authentication_and_claims_it(): void
    {
        $user = User::factory()->create(['email' => 'person@example.com']);
        $cart = Cart::query()->create(['session_id' => 'guest-session', 'status' => CartStatusEnum::ACTIVE]);

        $this->mock(GetCurrentActiveCartAction::class)
            ->shouldReceive('execute')->once()->andReturnUsing(function () use ($cart) {
                $this->assertGuest();

                return $cart;
            });

        Livewire::test(AuthModal::class, [
            'claimCurrentCart' => true,
            'openCheckoutAfterAuth' => true,
            'redirectUrl' => '/cart',
        ])
            ->call('goToEmailLogin')
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('loginWithEmail')
            ->assertHasNoErrors()
            ->assertRedirect('/cart');

        $this->assertAuthenticatedAs($user);
        $this->assertSame($user->id, $cart->fresh()->user_id);
        $this->assertNull($cart->fresh()->session_id);
        $this->assertTrue(session('open_checkout_after_auth'));
    }

    #[DataProvider('components')]
    public function test_switching_back_to_mobile_preserves_existing_password_login(string $component): void
    {
        $user = User::factory()->create(['mobile' => '09123456789']);

        Livewire::test($component)
            ->call('goToEmailLogin')
            ->call('goToEnterMobile')
            ->set('mobile', $user->mobile)
            ->call('startAuthFlow')
            ->assertSet('step', AuthFlowStepEnum::LOGIN_PASSWORD)
            ->set('password', 'password')
            ->call('loginWithPassword')
            ->assertHasNoErrors()
            ->assertRedirect();

        $this->assertAuthenticatedAs($user);
    }

    #[DataProvider('components')]
    public function test_existing_mobile_otp_login_still_works(string $component): void
    {
        $user = User::factory()->create(['mobile' => '09123456789']);
        app(CreateOtpAction::class)->execute($user->mobile, '123456', OtpTypeEnum::LOGIN);

        Livewire::test($component)
            ->set('mobile', $user->mobile)
            ->call('startAuthFlow')
            ->call('requestLoginOtp')
            ->assertSet('step', AuthFlowStepEnum::VERIFY_LOGIN_OTP)
            ->set('otpCode', '123456')
            ->call('verifyLoginOtp')
            ->assertHasNoErrors()
            ->assertRedirect();

        $this->assertAuthenticatedAs($user);
    }

    public function test_email_login_sends_existing_role_login_alerts_and_clears_failed_attempts(): void
    {
        $user = User::factory()->create(['email' => 'person@example.com']);
        $user->role->update(['login_email_enabled' => true, 'login_sms_enabled' => true]);
        $this->startSession();
        request()->setLaravelSession(app('session.store'));
        $key = app(AuthRateLimitKeyFactory::class)->make(AuthRateLimitScopeEnum::LOGIN_PASSWORD, $user->email);
        RateLimiter::hit($key);

        app(LoginUserWithEmailAction::class)->execute($user->email, 'password');

        $this->assertAuthenticatedAs($user);
        $this->assertSame(0, RateLimiter::attempts($key));
        Queue::assertPushed(SendEmailJob::class, 1);
        Queue::assertPushed(SendLoginAlertSmsJob::class, 1);
    }
}
