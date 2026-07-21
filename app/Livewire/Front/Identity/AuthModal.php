<?php

namespace App\Livewire\Front\Identity;

use App\Application\Commerce\Actions\Cart\ClaimGuestCartForUserAction;
use App\Application\Commerce\Actions\Cart\GetCurrentActiveCartAction;
use App\Application\Identity\Actions\Auth\LoginUserByIdentifierAction;
use App\Application\Identity\Actions\Auth\RegisterUserAction;
use App\Domain\Identity\Models\User;
use App\Rules\MobileRule;
use App\Rules\ValidPassword;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Throwable;

class AuthModal extends Component
{
    public string $name = 'auth-modal';

    public string $mode = 'login';

    public bool $claimCurrentCart = false;

    public bool $openCheckoutAfterAuth = false;

    public string $redirectUrl = '';

    public ?string $identifier = null;

    public ?string $firstName = null;

    public ?string $lastName = null;

    public ?string $mobile = null;

    public ?string $email = null;

    public ?string $password = null;

    public ?string $authError = null;

    public function mount(
        string  $name = 'auth-modal',
        bool    $claimCurrentCart = false,
        bool    $openCheckoutAfterAuth = false,
        ?string $redirectUrl = null,
    ): void
    {
        $this->name = $name;
        $this->claimCurrentCart = $claimCurrentCart;
        $this->openCheckoutAfterAuth = $openCheckoutAfterAuth;
        $this->redirectUrl = $redirectUrl ?: url()->current();
    }

    public function setMode(string $mode): void
    {
        if (!in_array($mode, ['login', 'register'], true)) {
            return;
        }

        $this->mode = $mode;
        $this->authError = null;
        $this->password = null;

        $this->resetErrorBag();
    }

    public function submit(
        LoginUserByIdentifierAction $loginUserByIdentifierAction,
        RegisterUserAction          $registerUserAction,
        GetCurrentActiveCartAction  $getCurrentActiveCartAction,
        ClaimGuestCartForUserAction $claimGuestCartForUserAction,
    ): RedirectResponse|Redirector|null
    {
        $this->authError = null;

        $this->normalizeInputs();

        $this->validate(
            $this->rules(),
            $this->messages(),
            $this->attributes(),
        );

        $guestCart = $this->claimCurrentCart
            ? $getCurrentActiveCartAction->execute()
            : null;

        try {
            $user = $this->mode === 'login'
                ? $loginUserByIdentifierAction->execute(
                    identifier: (string)$this->identifier,
                    password: (string)$this->password,
                )
                : $this->register(
                    registerUserAction: $registerUserAction,
                );

            if ($this->claimCurrentCart) {
                $claimGuestCartForUserAction->execute(
                    user: $user,
                    guestCart: $guestCart,
                );
            }

            if ($this->openCheckoutAfterAuth) {
                session()->flash('open_checkout_after_auth', true);
            }

            return redirect()->to($this->redirectUrl);
        } catch (DomainException $exception) {
            $this->authError = $this->domainExceptionMessage($exception);
            return null;
        } catch (QueryException $exception) {
            $this->authError = $this->queryExceptionMessage($exception);
            return null;
        } catch (Throwable $exception) {
            report($exception);

            $this->authError = 'خطایی در ورود یا ثبت نام رخ داد. لطفا چند لحظه بعد دوباره تلاش کنید.';
            return null;
        }
    }

    private function register(RegisterUserAction $registerUserAction): User
    {
        $mobile = (string)$this->mobile;
        $email = mb_strtolower(trim((string)$this->email));

        if (User::query()->where('mobile', $mobile)->exists()) {
            throw new DomainException('Mobile already exists.');
        }

        if (User::query()->where('email', $email)->exists()) {
            throw new DomainException('Email already exists.');
        }

        $user = $registerUserAction->execute(
            firstName: trim((string)$this->firstName),
            lastName: trim((string)$this->lastName),
            displayName: null,
            mobile: $mobile,
            email: $email,
            password: (string)$this->password,
        );

        Auth::login($user);

        request()->session()->regenerate();

        if (function_exists('handle_successful_login')) {
            handle_successful_login();
        }

        return $user;
    }

    private function rules(): array
    {
        if ($this->mode === 'login') {
            return [
                'identifier' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string'],
            ];
        }

        return [
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'string', new MobileRule()],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['required', 'string', new ValidPassword()],
        ];
    }

    private function messages(): array
    {
        return [
            'identifier.required' => 'موبایل یا ایمیل خود را وارد کنید.',
            'identifier.string' => 'موبایل یا ایمیل وارد شده معتبر نیست.',
            'identifier.max' => 'موبایل یا ایمیل نباید بیشتر از ۲۵۵ کاراکتر باشد.',

            'firstName.required' => 'نام را وارد کنید.',
            'firstName.string' => 'نام وارد شده معتبر نیست.',
            'firstName.max' => 'نام نباید بیشتر از ۲۵۵ کاراکتر باشد.',

            'lastName.required' => 'نام خانوادگی را وارد کنید.',
            'lastName.string' => 'نام خانوادگی وارد شده معتبر نیست.',
            'lastName.max' => 'نام خانوادگی نباید بیشتر از ۲۵۵ کاراکتر باشد.',

            'mobile.required' => 'شماره موبایل را وارد کنید.',
            'mobile.string' => 'شماره موبایل وارد شده معتبر نیست.',

            'email.email' => 'ایمیل وارد شده معتبر نیست.',
            'email.max' => 'ایمیل نباید بیشتر از ۲۵۵ کاراکتر باشد.',

            'password.required' => 'رمز عبور را وارد کنید.',
            'password.string' => 'رمز عبور وارد شده معتبر نیست.',
            'password.min' => 'رمز عبور باید حداقل ۸ کاراکتر باشد.',
        ];
    }

    private function attributes(): array
    {
        return [
            'identifier' => 'موبایل یا ایمیل',
            'firstName' => 'نام',
            'lastName' => 'نام خانوادگی',
            'mobile' => 'شماره موبایل',
            'email' => 'ایمیل',
            'password' => 'رمز عبور',
        ];
    }

    private function normalizeInputs(): void
    {
        if ($this->mode === 'register') {
            $this->mobile = normalize_number(
                trim((string)$this->mobile)
            );
        }
    }

    private function domainExceptionMessage(DomainException $exception): string
    {
        return match ($exception->getMessage()) {
            'Invalid login credentials.' => 'موبایل، ایمیل یا رمز عبور اشتباه است.',
            'Mobile already exists.' => 'با این شماره موبایل قبلا حساب کاربری ساخته شده است. لطفا وارد حساب شوید.',
            'Email already exists.' => 'با این ایمیل قبلا حساب کاربری ساخته شده است. لطفا وارد حساب شوید.',
            default => 'امکان ورود یا ثبت نام وجود ندارد. لطفا اطلاعات را بررسی کنید.',
        };
    }

    private function queryExceptionMessage(QueryException $exception): string
    {
        $message = strtolower($exception->getMessage());

        if (str_contains($message, 'mobile')) {
            return 'با این شماره موبایل قبلا حساب کاربری ساخته شده است. لطفا وارد حساب شوید.';
        }

        if (str_contains($message, 'email')) {
            return 'با این ایمیل قبلا حساب کاربری ساخته شده است. لطفا وارد حساب شوید.';
        }

        return 'امکان ساخت حساب کاربری وجود ندارد. لطفا اطلاعات را بررسی کنید.';
    }

    public function render(): View
    {
        return view('livewire.front.identity.auth-modal');
    }
}
