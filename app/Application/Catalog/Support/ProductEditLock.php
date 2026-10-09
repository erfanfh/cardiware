<?php

namespace App\Application\Catalog\Support;

use App\Application\Catalog\Exceptions\ProductEditLockedException;
use App\Domain\Catalog\Models\Product;
use App\Domain\Identity\Models\User;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductEditLock
{
    public const LIFETIME_SECONDS = 60;

    public function acquire(Product $product, User $user, ?string $existingToken = null): string
    {
        return $this->synchronized($product, function (Product $current) use ($user, $existingToken): string {
            if ($existingToken !== null) {
                $this->assertOwner($current, $user, $existingToken);
            } elseif ($current->isEditLocked() && (int) $current->edit_lock_user_id !== (int) $user->id) {
                $this->throwLocked($current);
            }

            // A fresh page gets a new token, invalidating any older tab of the same admin.
            $token = $existingToken ?? Str::random(64);
            DB::table('products')->where('id', $current->id)->update([
                'edit_lock_user_id' => $user->id,
                'edit_lock_token' => $token,
                'edit_lock_expires_at' => now()->addSeconds(self::LIFETIME_SECONDS),
            ]);

            return $token;
        });
    }

    public function renew(Product $product, User $user, string $token): void
    {
        $this->synchronized($product, function (Product $current) use ($user, $token): void {
            $this->assertOwner($current, $user, $token);
            $this->extend($current);
        });
    }

    public function release(Product $product, User $user, string $token): void
    {
        // Matching the token prevents a delayed close request from releasing a newer lock.
        DB::table('products')->where('id', $product->id)
            ->where('edit_lock_user_id', $user->id)
            ->where('edit_lock_token', $token)
            ->update([
                'edit_lock_user_id' => null,
                'edit_lock_token' => null,
                'edit_lock_expires_at' => null,
            ]);
    }

    public function save(Product $product, User $user, string $token, Closure $save): mixed
    {
        return $this->synchronized($product, function (Product $current) use ($user, $token, $save): mixed {
            $this->assertOwner($current, $user, $token);
            $result = $save();
            $this->extend($current);

            return $result;
        });
    }

    public function withoutEditor(Product $product, Closure $write): mixed
    {
        return $this->synchronized($product, function (Product $current) use ($write): mixed {
            if ($current->isEditLocked()) {
                $this->throwLocked($current);
            }

            return $write();
        });
    }

    private function synchronized(Product $product, Closure $callback): mixed
    {
        // Hold the row lock through the write so expiry/takeover cannot race a save.
        return DB::transaction(function () use ($product, $callback): mixed {
            $current = Product::withoutGlobalScopes()->whereKey($product->id)
                ->whereNull('deleted_at')->lockForUpdate()->firstOrFail();

            return $callback($current);
        });
    }

    private function assertOwner(Product $product, User $user, string $token): void
    {
        if (! $product->isEditLocked()
            || (int) $product->edit_lock_user_id !== (int) $user->id
            || $token === ''
            || ! hash_equals((string) $product->edit_lock_token, $token)) {
            throw new ProductEditLockedException($product,
                'دسترسی ویرایش این صفحه منقضی شده یا محصول در صفحه دیگری باز شده است. این صفحه دیگر اجازه ذخیره ندارد. پیش از باز کردن دوباره، متن تغییرات خود را کپی کنید.');
        }
    }

    private function extend(Product $product): void
    {
        // Query builder deliberately leaves the content's updated_at untouched.
        DB::table('products')->where('id', $product->id)->update([
            'edit_lock_expires_at' => now()->addSeconds(self::LIFETIME_SECONDS),
        ]);
    }

    private function throwLocked(Product $product): never
    {
        $name = $product->editLockUser?->display_name ?: 'مدیر دیگر';

        throw new ProductEditLockedException($product, "این محصول در حال ویرایش توسط «{$name}» است. پس از پایان ویرایش دوباره تلاش کنید.");
    }
}
