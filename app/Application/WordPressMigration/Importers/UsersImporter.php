<?php

namespace App\Application\WordPressMigration\Importers;

use App\Application\Identity\Actions\GetDefaultRoleAction;
use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\User;
use App\Domain\WordPressMigration\Models\WpMigrationMap;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UsersImporter
{
    public function count(): int
    {
        return DB::connection('wordpress')
            ->table('users')
            ->count();
    }

    public function import(?Closure $progressCallback = null): void
    {
        DB::connection('wordpress')
            ->table('users')
            ->orderBy('ID')
            ->chunk(100, function ($users) use ($progressCallback) {
                foreach ($users as $wpUser) {
                    $this->importUser($wpUser);

                    if ($progressCallback) {
                        $progressCallback();
                    }
                }
            });
    }

    private function importUser(object $wpUser): void
    {
        $exists = WpMigrationMap::query()
            ->where('source_type', 'wordpress_user')
            ->where('source_id', $wpUser->ID)
            ->where('target_type', User::class)
            ->exists();

        if ($exists) {
            return;
        }

        $email = $wpUser->user_email ?: "wp-user-{$wpUser->ID}@example.local";

        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'username' => $this->makeUsername($wpUser),
                'first_name' => $wpUser->display_name ?: $wpUser->user_login,
                'last_name' => '',
                'display_name' => $wpUser->display_name ?: $wpUser->user_login,

                'role_id' => app(GetDefaultRoleAction::class)->execute()->id,
                'status' => UserStatusEnum::ACTIVE,

                'mobile' => $this->generateFakeMobile($wpUser->ID),
                'mobile_verified_at' => now(),

                'email_verified_at' => $wpUser->user_email ? now() : null,

                'password' => bcrypt(Str::random(32)),

                'avatar_id' => null,

                'created_at' => $wpUser->user_registered ?: now(),
                'updated_at' => now(),
            ]
        );

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

        $username = $base;
        $counter = 1;

        while (User::query()->where('username', $username)->exists()) {
            $username = $base . '_' . $counter;
            $counter++;
        }

        return $username;
    }

    private function generateFakeMobile(int $wpUserId): string
    {
        return '09' . str_pad((string)$wpUserId, 9, '0', STR_PAD_LEFT);
    }
}
