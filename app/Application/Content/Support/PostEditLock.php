<?php

namespace App\Application\Content\Support;

use App\Application\Content\Exceptions\PostEditLockedException;
use App\Domain\Content\Models\Post;
use App\Domain\Identity\Models\User;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostEditLock
{
    public const LIFETIME_SECONDS = 180;

    public function acquire(Post $post, User $user, ?string $existingToken = null): string
    {
        return $this->synchronized($post, function (Post $current) use ($user, $existingToken): string {
            if ($existingToken !== null) {
                $this->assertOwner($current, $user, $existingToken);
            } elseif ($current->isEditLocked() && (int) $current->edit_lock_user_id !== (int) $user->id) {
                $this->throwLocked($current);
            }

            // A fresh page gets a new token, invalidating any older tab of the same admin.
            $token = $existingToken ?? Str::random(64);
            DB::table('posts')->where('id', $current->id)->update([
                'edit_lock_user_id' => $user->id,
                'edit_lock_token' => $token,
                'edit_lock_expires_at' => now()->addSeconds(self::LIFETIME_SECONDS),
            ]);

            return $token;
        });
    }

    public function renew(Post $post, User $user, string $token): void
    {
        $this->synchronized($post, function (Post $current) use ($user, $token): void {
            $this->assertOwner($current, $user, $token);
            $this->extend($current);
        });
    }

    public function release(Post $post, User $user, string $token): void
    {
        // Matching the token prevents a delayed close request from releasing a newer lock.
        DB::table('posts')->where('id', $post->id)
            ->where('edit_lock_user_id', $user->id)
            ->where('edit_lock_token', $token)
            ->update([
                'edit_lock_user_id' => null,
                'edit_lock_token' => null,
                'edit_lock_expires_at' => null,
            ]);
    }

    public function save(Post $post, User $user, string $token, Closure $save): mixed
    {
        return $this->synchronized($post, function (Post $current) use ($user, $token, $save): mixed {
            $this->assertOwner($current, $user, $token);
            $result = $save();
            $this->extend($current);

            return $result;
        });
    }

    public function withoutEditor(Post $post, Closure $write): mixed
    {
        return $this->synchronized($post, function (Post $current) use ($write): mixed {
            if ($current->isEditLocked()) {
                $this->throwLocked($current);
            }

            return $write();
        });
    }

    private function synchronized(Post $post, Closure $callback): mixed
    {
        // Hold the row lock through the write so expiry/takeover cannot race a save.
        return DB::transaction(function () use ($post, $callback): mixed {
            $current = Post::withoutGlobalScopes()->whereKey($post->id)
                ->whereNull('deleted_at')->lockForUpdate()->firstOrFail();

            return $callback($current);
        });
    }

    private function assertOwner(Post $post, User $user, string $token): void
    {
        if (! $post->isEditLocked()
            || (int) $post->edit_lock_user_id !== (int) $user->id
            || $token === ''
            || ! hash_equals((string) $post->edit_lock_token, $token)) {
            throw new PostEditLockedException($post,
                'دسترسی ویرایش این صفحه منقضی شده یا نوشته در صفحه دیگری باز شده است. این صفحه دیگر اجازه ذخیره ندارد. پیش از باز کردن دوباره، متن تغییرات خود را کپی کنید.');
        }
    }

    private function extend(Post $post): void
    {
        // Query builder deliberately leaves the content's updated_at untouched.
        DB::table('posts')->where('id', $post->id)->update([
            'edit_lock_expires_at' => now()->addSeconds(self::LIFETIME_SECONDS),
        ]);
    }

    private function throwLocked(Post $post): never
    {
        $name = $post->editLockUser?->display_name ?: 'مدیر دیگر';

        throw new PostEditLockedException($post, "این نوشته در حال ویرایش توسط «{$name}» است. پس از پایان ویرایش دوباره تلاش کنید.");
    }
}
