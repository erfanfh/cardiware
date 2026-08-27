<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SetContentKeyVaultPasswordCommand extends Command
{
    protected $signature = 'launcher:set-content-key-vault-password';

    protected $description = 'Hash a new content-key vault passphrase for the admin panel '
        . '(the resulting value goes in CONTENT_KEY_VAULT_PASSWORD_HASH, never the plaintext).';

    public function handle(): int
    {
        $this->line('این رمز فقط برای باز کردن بخش «محافظت محتوا» در پنل ادمینه -- ربطی به رمز ورود شما نداره.');
        $this->line('این رمز جایی روی سرور به‌صورت متن ساده ذخیره نمی‌شه، فقط هش آن.');
        $this->newLine();

        $password = $this->secret('رمز جدید صندوق کلید محتوا را وارد کنید (نمایش داده نمی‌شود)');

        if (blank($password) || Str::length($password) < 12) {
            $this->error('رمز باید حداقل ۱۲ کاراکتر باشد.');
            return self::FAILURE;
        }

        $confirmation = $this->secret('همان رمز را دوباره وارد کنید');

        if (!hash_equals($password, (string)$confirmation)) {
            $this->error('دو مقدار وارد‌شده یکی نبودند.');
            return self::FAILURE;
        }

        $hash = Hash::make($password);

        $this->newLine();
        $this->info('این خط را در فایل .env سرور (نه هیچ‌جای دیگر) قرار دهید:');
        $this->newLine();
        $this->line("CONTENT_KEY_VAULT_PASSWORD_HASH=\"{$hash}\"");
        $this->newLine();
        $this->warn('خودِ رمز را جایی ذخیره نکردیم -- اگر فراموشش کنید، باید همین دستور را دوباره اجرا کنید.');

        return self::SUCCESS;
    }
}
