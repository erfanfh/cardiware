<?php

namespace App\Application\Catalog\Actions\ProductInformation;

use App\Domain\Catalog\Models\ProductSaleItemManifest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Gates BOTH viewing and changing content_key_value behind a passphrase that is
 * separate from any admin's own login -- because that field is a real secret (the
 * raw key protecting an encrypted game database), not an ordinary setting any admin
 * with product-edit access should be trusted to read or silently overwrite.
 */
final class RevealContentKeyAction
{
    /** @return array{ok: true, content_key_id: ?string, content_key_value: ?string}|array{ok: false, message: string, retry_after_seconds?: int} */
    public function execute(int $saleItemId, string $vaultPassword, int $adminUserId): array
    {
        $throttleKey = "content-key-vault:{$adminUserId}";

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $retryAfterSeconds = max(1, RateLimiter::availableIn($throttleKey));

            Log::warning('launcher.admin.content_key_vault.rate_limited', [
                'admin_user_id' => $adminUserId,
            ]);

            return [
                'ok' => false,
                'message' => "تعداد تلاش‌های نادرست بیش از حد مجاز است. {$retryAfterSeconds} ثانیه دیگر تلاش کنید.",
                'retry_after_seconds' => $retryAfterSeconds,
            ];
        }

        $expectedHash = (string)config('launcher_security.content_key_vault_password_hash');

        if (blank($expectedHash)) {
            Log::error('launcher.admin.content_key_vault.not_configured');

            return [
                'ok' => false,
                'message' => 'رمز صندوق کلید محتوا هنوز روی سرور تنظیم نشده است.',
            ];
        }

        if (!Hash::check($vaultPassword, $expectedHash)) {
            RateLimiter::hit($throttleKey, 300);

            Log::warning('launcher.admin.content_key_vault.wrong_password', [
                'admin_user_id' => $adminUserId,
            ]);

            return [
                'ok' => false,
                'message' => 'رمز صندوق کلید محتوا نادرست است.',
            ];
        }

        RateLimiter::clear($throttleKey);

        $manifest = ProductSaleItemManifest::query()
            ->where('product_sale_item_id', $saleItemId)
            ->first(['content_key_id', 'content_key_value']);

        Log::info('launcher.admin.content_key_vault.revealed', [
            'admin_user_id' => $adminUserId,
            'product_sale_item_id' => $saleItemId,
        ]);

        return [
            'ok' => true,
            'content_key_id' => $manifest?->content_key_id,
            'content_key_value' => $manifest?->content_key_value,
        ];
    }
}
