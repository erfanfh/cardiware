<?php

namespace App\Application\Content\Support;

use App\Application\Content\Exceptions\PageEditLockedException;
use App\Domain\Content\Models\Page;
use App\Domain\Identity\Models\User;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PageEditLock
{
    public const LIFETIME_SECONDS = 60;

    public function acquire(Page $page, User $user, ?string $existingToken = null): string
    {
        return $this->synchronized($page, function (Page $current) use ($user, $existingToken): string {
            if ($existingToken !== null) {
                $this->assertOwner($current, $user, $existingToken);
            } elseif ($current->isEditLocked() && (int) $current->edit_lock_user_id !== (int) $user->id) {
                $this->throwLocked($current);
            }

            // A fresh page gets a new token, invalidating any older tab of the same admin.
            $token = $existingToken ?? Str::random(64);
            DB::table('pages')->where('id', $current->id)->update([
                'edit_lock_user_id' => $user->id,
                'edit_lock_token' => $token,
                'edit_lock_expires_at' => now()->addSeconds(self::LIFETIME_SECONDS),
            ]);

            return $token;
        });
    }

    public function renew(Page $page, User $user, string $token): void
    {
        $this->synchronized($page, function (Page $current) use ($user, $token): void {
            $this->assertOwner($current, $user, $token);
            $this->extend($current);
        });
    }

    public function release(Page $page, User $user, string $token): void
    {
        // Matching the token prevents a delayed close request from releasing a newer lock.
        DB::table('pages')->where('id', $page->id)
            ->where('edit_lock_user_id', $user->id)
            ->where('edit_lock_token', $token)
            ->update([
                'edit_lock_user_id' => null,
                'edit_lock_token' => null,
                'edit_lock_expires_at' => null,
            ]);
    }

    public function save(Page $page, User $user, string $token, Closure $save): mixed
    {
        return $this->synchronized($page, function (Page $current) use ($user, $token, $save): mixed {
            $this->assertOwner($current, $user, $token);
            $result = $save();
            $this->extend($current);

            return $result;
        });
    }

    public function withoutEditor(Page $page, Closure $write): mixed
    {
        return $this->synchronized($page, function (Page $current) use ($write): mixed {
            if ($current->isEditLocked()) {
                $this->throwLocked($current);
            }

            return $write();
        });
    }

    private function synchronized(Page $page, Closure $callback): mixed
    {
        // Hold the row lock through the write so expiry/takeover cannot race a save.
        return DB::transaction(function () use ($page, $callback): mixed {
            $current = Page::withoutGlobalScopes()->whereKey($page->id)
                ->whereNull('deleted_at')->lockForUpdate()->firstOrFail();

            return $callback($current);
        });
    }

    private function assertOwner(Page $page, User $user, string $token): void
    {
        if (! $page->isEditLocked()
            || (int) $page->edit_lock_user_id !== (int) $user->id
            || $token === ''
            || ! hash_equals((string) $page->edit_lock_token, $token)) {
            throw new PageEditLockedException($page,
                'دسترسی ویرایش این صفحه منقضی شده یا برگه در صفحه دیگری باز شده است. این صفحه دیگر اجازه ذخیره ندارد. پیش از باز کردن دوباره، متن تغییرات خود را کپی کنید.');
        }
    }

    private function extend(Page $page): void
    {
        // Query builder deliberately leaves the content's updated_at untouched.
        DB::table('pages')->where('id', $page->id)->update([
            'edit_lock_expires_at' => now()->addSeconds(self::LIFETIME_SECONDS),
        ]);
    }

    private function throwLocked(Page $page): never
    {
        $name = $page->editLockUser?->display_name ?: 'مدیر دیگر';

        throw new PageEditLockedException($page, "این برگه در حال ویرایش توسط «{$name}» است. پس از پایان ویرایش دوباره تلاش کنید.");
    }
}
