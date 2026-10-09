<?php

namespace App\Application\Project\Support;

use App\Application\Project\Exceptions\ProjectEditLockedException;
use App\Domain\Identity\Models\User;
use App\Domain\Project\Models\Project;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectEditLock
{
    public const LIFETIME_SECONDS = 60;

    public function acquire(Project $project, User $user, ?string $existingToken = null): string
    {
        return $this->synchronized($project, function (Project $current) use ($user, $existingToken): string {
            if ($existingToken !== null) {
                $this->assertOwner($current, $user, $existingToken);
            } elseif ($current->isEditLocked() && (int) $current->edit_lock_user_id !== (int) $user->id) {
                $this->throwLocked($current);
            }

            // A fresh page gets a new token, invalidating any older tab of the same admin.
            $token = $existingToken ?? Str::random(64);
            DB::table('projects')->where('id', $current->id)->update([
                'edit_lock_user_id' => $user->id,
                'edit_lock_token' => $token,
                'edit_lock_expires_at' => now()->addSeconds(self::LIFETIME_SECONDS),
            ]);

            return $token;
        });
    }

    public function renew(Project $project, User $user, string $token): void
    {
        $this->synchronized($project, function (Project $current) use ($user, $token): void {
            $this->assertOwner($current, $user, $token);
            $this->extend($current);
        });
    }

    public function release(Project $project, User $user, string $token): void
    {
        // Matching the token prevents a delayed close request from releasing a newer lock.
        DB::table('projects')->where('id', $project->id)
            ->where('edit_lock_user_id', $user->id)
            ->where('edit_lock_token', $token)
            ->update([
                'edit_lock_user_id' => null,
                'edit_lock_token' => null,
                'edit_lock_expires_at' => null,
            ]);
    }

    public function save(Project $project, User $user, string $token, Closure $save): mixed
    {
        return $this->synchronized($project, function (Project $current) use ($user, $token, $save): mixed {
            $this->assertOwner($current, $user, $token);
            $result = $save();
            $this->extend($current);

            return $result;
        });
    }

    public function withoutEditor(Project $project, Closure $write): mixed
    {
        return $this->synchronized($project, function (Project $current) use ($write): mixed {
            if ($current->isEditLocked()) {
                $this->throwLocked($current);
            }

            return $write();
        });
    }

    private function synchronized(Project $project, Closure $callback): mixed
    {
        // Hold the row lock through the write so expiry/takeover cannot race a save.
        return DB::transaction(function () use ($project, $callback): mixed {
            $current = Project::withoutGlobalScopes()->whereKey($project->id)
                ->whereNull('deleted_at')->lockForUpdate()->firstOrFail();

            return $callback($current);
        });
    }

    private function assertOwner(Project $project, User $user, string $token): void
    {
        if (! $project->isEditLocked()
            || (int) $project->edit_lock_user_id !== (int) $user->id
            || $token === ''
            || ! hash_equals((string) $project->edit_lock_token, $token)) {
            throw new ProjectEditLockedException($project,
                'دسترسی ویرایش این صفحه منقضی شده یا پروژه در صفحه دیگری باز شده است. این صفحه دیگر اجازه ذخیره ندارد. پیش از باز کردن دوباره، متن تغییرات خود را کپی کنید.');
        }
    }

    private function extend(Project $project): void
    {
        // Query builder deliberately leaves the content's updated_at untouched.
        DB::table('projects')->where('id', $project->id)->update([
            'edit_lock_expires_at' => now()->addSeconds(self::LIFETIME_SECONDS),
        ]);
    }

    private function throwLocked(Project $project): never
    {
        $name = $project->editLockUser?->display_name ?: 'مدیر دیگر';

        throw new ProjectEditLockedException($project, "این پروژه در حال ویرایش توسط «{$name}» است. پس از پایان ویرایش دوباره تلاش کنید.");
    }
}
