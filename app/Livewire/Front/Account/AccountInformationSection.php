<?php

namespace App\Livewire\Front\Account;

use App\Application\Media\Actions\DeleteMediaFileFromLibraryAction;
use App\Application\Media\Actions\UploadImageToLibraryAction;
use App\Application\Shared\Cache\Invalidators\UserCacheInvalidator;
use App\Domain\Identity\Models\User;
use App\Domain\Identity\PresetAvatars;
use App\Rules\MobileRule;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Morilog\Jalali\Jalalian;
use Throwable;

class AccountInformationSection extends Component
{
    use WithFileUploads;

    public $customAvatar = null;
    public ?string $customAvatarErrorMessage = null;

    public string $first_name = '';
    public string $last_name = '';
    public string $display_name = '';
    public ?string $mobile = null;
    public ?string $email = null;
    public ?string $preset_avatar = null;

    public ?string $errorMessage = null;
    public ?string $avatarErrorMessage = null;

    public function mount(): void
    {
        $this->fillFromUser();
    }

    /**
     * Fill form data from authenticated user.
     */
    public function fillFromUser(): void
    {
        /** @var User $user */
        $user = auth()->user();

        $this->first_name = (string)$user->first_name;
        $this->last_name = (string)$user->last_name;
        $this->display_name = (string)($user->display_name ?: trim($user->first_name . ' ' . $user->last_name));
        $this->mobile = $user->mobile;
        $this->email = $user->email;
        $this->preset_avatar = $user->preset_avatar === null ? null : (string) $user->preset_avatar;
    }

    /**
     * Validation rules.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        /** @var User $user */
        $user = auth()->user();

        return [
            'first_name' => ['required', 'string', 'min:2', 'max:80'],
            'last_name' => ['required', 'string', 'min:2', 'max:80'],
            'display_name' => ['nullable', 'string', 'min:2', 'max:120'],

            'mobile' => [
                'required',
                'string',
                'max:20',
                new MobileRule(),
                Rule::unique('users', 'mobile')->ignore($user->id),
            ],

            'email' => [
                'nullable',
                'email',
                'max:190',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function avatarRules(): array
    {
        return ['preset_avatar' => ['required', Rule::in(PresetAvatars::ids())]];
    }

    /**
     * Validation messages.
     *
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'first_name.required' => 'نام را وارد کنید.',
            'first_name.min' => 'نام باید حداقل ۲ کاراکتر باشد.',
            'first_name.max' => 'نام نمی‌تواند بیشتر از ۸۰ کاراکتر باشد.',

            'last_name.required' => 'نام خانوادگی را وارد کنید.',
            'last_name.min' => 'نام خانوادگی باید حداقل ۲ کاراکتر باشد.',
            'last_name.max' => 'نام خانوادگی نمی‌تواند بیشتر از ۸۰ کاراکتر باشد.',

            'display_name.min' => 'نام نمایشی باید حداقل ۲ کاراکتر باشد.',
            'display_name.max' => 'نام نمایشی نمی‌تواند بیشتر از ۱۲۰ کاراکتر باشد.',

            'mobile.required' => 'شماره موبایل را وارد کنید.',
            'mobile.unique' => 'این شماره موبایل قبلا ثبت شده است.',
            'mobile.max' => 'شماره موبایل معتبر نیست.',

            'email.email' => 'ایمیل وارد شده معتبر نیست.',
            'email.unique' => 'این ایمیل قبلا ثبت شده است.',
            'email.max' => 'ایمیل نمی‌تواند بیشتر از ۱۹۰ کاراکتر باشد.',
            'preset_avatar.in' => 'آواتار انتخاب‌شده معتبر نیست.',
            'preset_avatar.required' => 'لطفاً یک آواتار انتخاب کنید.',
        ];
    }

    public function saveAvatar(): void
    {
        $this->avatarErrorMessage = null;
        $validated = $this->validate($this->avatarRules());

        try {
            /** @var User $user */
            $user = auth()->user();
            $user->forceFill(['preset_avatar' => (int) $validated['preset_avatar']])->save();

            session()->flash('account_information_success', 'آواتار پروفایل با موفقیت ذخیره شد.');
            $this->redirectRoute('account.information');
        } catch (Throwable $exception) {
            report($exception);
            $this->avatarErrorMessage = 'ذخیره آواتار با خطا مواجه شد. لطفاً دوباره تلاش کنید.';
        }
    }

    public function saveCustomAvatar(
        UploadImageToLibraryAction $uploadImage,
        DeleteMediaFileFromLibraryAction $deleteImage,
    ): void {
        $this->customAvatarErrorMessage = null;

        /** @var User $user */
        $user = auth()->user();
        abort_unless($user?->hasActiveVipSubscription(), 403);

        $this->validate([
            'customAvatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:1024'],
        ], [
            'customAvatar.required' => 'لطفاً یک تصویر انتخاب کنید.',
            'customAvatar.image' => 'فایل انتخاب‌شده باید تصویر باشد.',
            'customAvatar.mimes' => 'فرمت تصویر باید JPG، PNG یا WebP باشد.',
            'customAvatar.extensions' => 'فرمت تصویر باید JPG، PNG یا WebP باشد.',
            'customAvatar.max' => 'حجم تصویر نباید بیشتر از ۱ مگابایت باشد.',
        ]);

        $media = null;
        try {
            $media = $uploadImage->execute($this->customAvatar, $user->getKey());
            $user->forceFill(['avatar_id' => $media->getKey(), 'preset_avatar' => null])->save();
        } catch (Throwable $exception) {
            if ($media && !User::query()->where('avatar_id', $media->getKey())->exists()) {
                $deleteImage->execute($media);
            }

            report($exception);
            $this->customAvatarErrorMessage = 'ذخیره عکس پروفایل با خطا مواجه شد. لطفاً دوباره تلاش کنید.';

            return;
        }

        app(UserCacheInvalidator::class)->flushAccountForUser($user);
        session()->flash('account_information_success', 'عکس پروفایل اختصاصی با موفقیت ذخیره شد.');
        $this->redirectRoute('account.information');
    }

    #[Computed]
    public function vipActive(): bool
    {
        return (bool) auth()->user()?->hasActiveVipSubscription();
    }

    #[Computed]
    public function hasCustomAvatar(): bool
    {
        $user = auth()->user();

        return $user?->avatar !== null && $user->preset_avatar === null;
    }

    /**
     * Save account information.
     */
    public function save(): void
    {
        $this->errorMessage = null;

        $this->mobile = $this->normalizeMobile($this->mobile);
        $this->email = $this->email ? mb_strtolower(trim($this->email)) : null;
        $this->first_name = trim($this->first_name);
        $this->last_name = trim($this->last_name);
        $this->display_name = trim($this->display_name);

        $validated = $this->validate();

        try {
            DB::transaction(function () use ($validated) {
                /** @var User $user */
                $user = auth()->user();

                $oldMobile = $user->mobile;
                $oldEmail = $user->email;

                $displayName = $validated['display_name']
                    ?: trim($validated['first_name'] . ' ' . $validated['last_name']);

                $payload = [
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'display_name' => $displayName,
                    'mobile' => $validated['mobile'],
                    'email' => $validated['email'],
                ];

                if ($oldMobile !== $validated['mobile']) {
                    $payload['mobile_verified_at'] = null;
                }

                if ($oldEmail !== $validated['email']) {
                    $payload['email_verified_at'] = null;
                }

                $user->forceFill($payload)->save();

                auth()->setUser($user->refresh());
            });

            app(UserCacheInvalidator::class)->flushAccountForUser(auth()->user());

            $this->fillFromUser();

            session()->flash('account_information_success', 'اطلاعات حساب با موفقیت ذخیره شد.');
            $this->redirectRoute('account.information');
        } catch (Throwable $exception) {
            report($exception);

            $this->errorMessage = 'ذخیره اطلاعات حساب با خطا مواجه شد. لطفا دوباره تلاش کنید.';
        }
    }

    /**
     * Resolve username label.
     */
    public function usernameLabel(): string
    {
        /** @var User $user */
        $user = auth()->user();

        return $user->username
            ? ltrim($user->username, '@')
            : 'ثبت نشده';
    }

    /**
     * Resolve joined date.
     */
    public function joinedAtLabel(): string
    {
        /** @var User $user */
        $user = auth()->user();

        if (!$user->created_at) {
            return '—';
        }

        if (class_exists(Jalalian::class)) {
            return Jalalian::fromDateTime($user->created_at)->format('Y/m/d');
        }

        return $user->created_at->format('Y/m/d');
    }

    /**
     * Resolve account type label.
     */
    public function accountTypeLabel(): string
    {
        return $this->vipExpiresAt ? 'کاربر VIP' : 'کاربر عادی';
    }

    /**
     * Resolve account type badge class.
     */
    public function accountTypeClass(): string
    {
        return $this->vipExpiresAt
            ? 'border-amber-300 bg-amber-100 text-amber-900 dark:border-amber-500/40 dark:bg-amber-500/15 dark:text-amber-300'
            : 'border-slate-300 bg-white text-slate-700 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300';
    }

    #[Computed]
    public function vipExpiresAt(): ?Carbon
    {
        return auth()->user()?->vipExpiresAt();
    }

    /**
     * Resolve email verification label.
     */
    public function emailVerificationLabel(): string
    {
        /** @var User $user */
        $user = auth()->user();

        return $user->email_verified_at ? 'تأیید شده' : 'تأیید نشده';
    }

    /**
     * Resolve email verification class.
     */
    public function emailVerificationClass(): string
    {
        /** @var User $user */
        $user = auth()->user();

        return $user->email_verified_at
            ? 'border border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300'
            : 'border border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300';
    }

    /**
     * Resolve mobile verification label.
     */
    public function mobileVerificationLabel(): string
    {
        /** @var User $user */
        $user = auth()->user();

        return $user->mobile_verified_at ? 'تأیید شده' : 'تأیید نشده';
    }

    /**
     * Resolve mobile verification class.
     */
    public function mobileVerificationClass(): string
    {
        /** @var User $user */
        $user = auth()->user();

        return $user->mobile_verified_at
            ? 'border border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300'
            : 'border border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300';
    }

    /**
     * Resolve user status label.
     */
    public function userStatusLabel(): string
    {
        /** @var User $user */
        $user = auth()->user();

        return method_exists($user->status, 'label')
            ? $user->status->label()
            : $user->status->value;
    }

    /**
     * Normalize mobile number.
     */
    private function normalizeMobile(?string $mobile): ?string
    {
        if ($mobile === null) {
            return null;
        }

        $mobile = trim($mobile);

        if (function_exists('normalize_number')) {
            return normalize_number($mobile);
        }

        return preg_replace('/\s+/', '', $mobile);
    }

    public function render(): View
    {
        return view('livewire.front.account.account-information-section');
    }
}
