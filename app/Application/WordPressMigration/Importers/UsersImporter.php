<?php

namespace App\Application\WordPressMigration\Importers;

use App\Application\Identity\Actions\GetDefaultRoleAction;
use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\User;
use App\Domain\WordPressMigration\Models\WpMigrationMap;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UsersImporter
{
    public function import(): void
    {
        DB::connection('wordpress')
            ->table('users')
            ->orderBy('ID')
            ->chunk(100, function ($users) {
                foreach ($users as $wpUser) {
                    $this->importUser($wpUser);
                }
            });
    }

    private function importUser(object $wpUser): void
    {
        // ⛔ اگر قبلاً migrate شده، رد کن
        $exists = WpMigrationMap::query()
            ->where('source_type', 'wordpress_user')
            ->where('source_id', $wpUser->ID)
            ->where('target_type', User::class)
            ->exists();

        if ($exists) {
            return;
        }

        // ایمیل fallback
        $email = $wpUser->user_email ?: "wp-user-{$wpUser->ID}@example.local";

        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'username' => $this->makeUsername($wpUser),
                'first_name' => $wpUser->display_name ?: $wpUser->user_login,
                'last_name' => '',
                'display_name' => $wpUser->display_name ?: $wpUser->user_login,

                // 👇 اینا مهمه
                'role_id' => app(GetDefaultRoleAction::class)->execute()->id, // فعلاً default (بعداً میشه mapping کرد)
                'status' => UserStatusEnum::ACTIVE,

                'mobile' => '',
                'mobile_verified_at' => null,

                'email_verified_at' => $wpUser->user_email ? now() : null,

                'password' => bcrypt(Str::random(32)),

                'avatar_id' => null,

                'created_at' => $wpUser->user_registered ?: now(),
                'updated_at' => now(),
            ]
        );

        // ثبت mapping
        WpMigrationMap::query()->create([
            'source_type' => 'wordpress_user',
            'source_id' => $wpUser->ID,
            'target_type' => User::class,
            'target_id' => $user->id,
            'meta' => [
                'user_login' => $wpUser->user_login,
                'user_nicename' => $wpUser->user_nicename,
                'user_url' => $wpUser->user_url,
            ],
        ]);
    }

    private function makeUsername(object $wpUser): string
    {
        $base = $wpUser->user_login ?: 'user_' . $wpUser->ID;

        // جلوگیری از تکراری شدن
        $username = $base;
        $counter = 1;

        while (User::query()->where('username', $username)->exists()) {
            $username = $base . '_' . $counter;
            $counter++;
        }

        return $username;
    }
}
