<?php

namespace App\Application\Video\Support;

use App\Application\Video\Exceptions\VideoEditLockedException;
use App\Domain\Identity\Models\User;
use App\Domain\Video\Models\Video;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VideoEditLock
{
    public const LIFETIME_SECONDS = 60;

    public function acquire(Video $video, User $user, ?string $existingToken = null): string
    {
        return $this->synchronized($video, function (Video $current) use ($user, $existingToken): string {
            if ($existingToken !== null) {
                $this->assertOwner($current, $user, $existingToken);
            } elseif ($current->isEditLocked() && (int) $current->edit_lock_user_id !== (int) $user->id) {
                $this->throwLocked($current);
            }

            // A fresh page gets a new token, invalidating any older tab of the same admin.
            $token = $existingToken ?? Str::random(64);
            DB::table('videos')->where('id', $current->id)->update([
                'edit_lock_user_id' => $user->id,
                'edit_lock_token' => $token,
                'edit_lock_expires_at' => now()->addSeconds(self::LIFETIME_SECONDS),
            ]);

            return $token;
        });
    }

    public function renew(Video $video, User $user, string $token): void
    {
        $this->synchronized($video, function (Video $current) use ($user, $token): void {
            $this->assertOwner($current, $user, $token);
            $this->extend($current);
        });
    }

    public function release(Video $video, User $user, string $token): void
    {
        // Matching the token prevents a delayed close request from releasing a newer lock.
        DB::table('videos')->where('id', $video->id)
            ->where('edit_lock_user_id', $user->id)
            ->where('edit_lock_token', $token)
            ->update([
                'edit_lock_user_id' => null,
                'edit_lock_token' => null,
                'edit_lock_expires_at' => null,
            ]);
    }

    public function save(Video $video, User $user, string $token, Closure $save): mixed
    {
        return $this->synchronized($video, function (Video $current) use ($user, $token, $save): mixed {
            $this->assertOwner($current, $user, $token);
            $result = $save();
            $this->extend($current);

            return $result;
        });
    }

    public function withoutEditor(Video $video, Closure $write): mixed
    {
        return $this->synchronized($video, function (Video $current) use ($write): mixed {
            if ($current->isEditLocked()) {
                $this->throwLocked($current);
            }

            return $write();
        });
    }

    private function synchronized(Video $video, Closure $callback): mixed
    {
        // Hold the row lock through the write so expiry/takeover cannot race a save.
        return DB::transaction(function () use ($video, $callback): mixed {
            $current = Video::withoutGlobalScopes()->whereKey($video->id)
                ->whereNull('deleted_at')->lockForUpdate()->firstOrFail();

            return $callback($current);
        });
    }

    private function assertOwner(Video $video, User $user, string $token): void
    {
        if (! $video->isEditLocked()
            || (int) $video->edit_lock_user_id !== (int) $user->id
            || $token === ''
            || ! hash_equals((string) $video->edit_lock_token, $token)) {
            throw new VideoEditLockedException($video,
                'دسترسی ویرایش این صفحه منقضی شده یا ویدیو در صفحه دیگری باز شده است. این صفحه دیگر اجازه ذخیره ندارد. پیش از باز کردن دوباره، متن تغییرات خود را کپی کنید.');
        }
    }

    private function extend(Video $video): void
    {
        // Query builder deliberately leaves the content's updated_at untouched.
        DB::table('videos')->where('id', $video->id)->update([
            'edit_lock_expires_at' => now()->addSeconds(self::LIFETIME_SECONDS),
        ]);
    }

    private function throwLocked(Video $video): never
    {
        $name = $video->editLockUser?->display_name ?: 'مدیر دیگر';

        throw new VideoEditLockedException($video, "این ویدیو در حال ویرایش توسط «{$name}» است. پس از پایان ویرایش دوباره تلاش کنید.");
    }
}
