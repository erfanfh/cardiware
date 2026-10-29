<?php

namespace App\Application\Content\Actions;

use App\Application\Shared\Cache\PublicReadCache;
use App\Domain\Catalog\Enums\ProductStatusEnum;
use App\Domain\Catalog\Models\Product;
use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\User;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Organization\Models\TeamMember;
use App\Domain\Project\Enums\ProjectStatusEnum;
use App\Domain\Project\Models\Project;
use App\Infrastructure\Services\Setting\Groups\AboutUsSettings;
use Illuminate\Support\Str;

class GetAboutUsPageDataAction
{
    public function __construct(
        protected AboutUsSettings $settings,
    ) {}

    /** @return array<string, mixed> */
    public function execute(): array
    {
        $settings = $this->settings->values();
        $settings['what_we_build_items'] = collect($settings['what_we_build_items'] ?? [])
            ->map(function (array $item): array {
                $item['url'] = $this->contentUrl($item['url'] ?? null);

                return $item;
            })
            ->all();
        $projectLimit = max(1, min(6, (int) ($settings['projects_count'] ?? 3)));
        $teamMemberLimit = max(1, min(12, (int) ($settings['team_members_count'] ?? 6)));

        $data = app(PublicReadCache::class)->remember(
            'about-us', [$projectLimit, $teamMemberLimit],
            [Project::class, Product::class, User::class, TeamMember::class, MediaFile::class],
            fn () => $this->queryData($projectLimit, $teamMemberLimit),
            [Product::class => ['published_at']],
        );

        return [...$data, 'aboutUs' => $settings, 'aboutUsContacts' => $this->contacts($settings)];
    }

    private function queryData(int $projectLimit, int $teamMemberLimit): array
    {
        $projectStats = Project::query()
            ->where('status', ProjectStatusEnum::ACTIVE)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN progress_percent BETWEEN 1 AND 99 THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN progress_percent = 100 THEN 1 ELSE 0 END) as completed
            ')
            ->first();

        $totalProjects = (int) ($projectStats?->total ?? 0);
        $completedProjects = (int) ($projectStats?->completed ?? 0);

        return [
            'aboutUsStats' => [
                'total_projects' => $totalProjects,
                'in_progress_projects' => (int) ($projectStats?->in_progress ?? 0),
                'completed_projects' => $completedProjects,
                'completion_percentage' => $totalProjects > 0
                    ? (int) round(($completedProjects / $totalProjects) * 100)
                    : 0,
                'active_products' => Product::query()
                    ->where('status', ProductStatusEnum::ACTIVE)
                    ->where('published_at', '<=', now())
                    ->count(),
                'active_users' => User::query()
                    ->where('status', UserStatusEnum::ACTIVE)
                    ->count(),
            ],
            'aboutUsProjects' => Project::query()
                ->where('status', ProjectStatusEnum::ACTIVE)
                ->latest('updated_at')
                ->limit($projectLimit)
                ->get(),
            'aboutUsTeamMembers' => TeamMember::query()
                ->with(['picture.primaryVariant', 'picture.variants'])
                ->oldest('id')
                ->limit($teamMemberLimit)
                ->get(),
        ];
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array<int, array{key:string, label:string, value:string, url:?string, external:bool}>
     */
    protected function contacts(array $settings): array
    {
        $settings = $this->withFooterContactFallbacks($settings);

        $contacts = [
            $this->contact(
                'telegram',
                'تلگرام',
                $settings['contact_telegram'] ?? null,
                fn (string $value): string => $this->socialUrl($value, 'https://t.me/'),
                true,
            ),
            $this->contact(
                'instagram',
                'اینستاگرام',
                $settings['contact_instagram'] ?? null,
                fn (string $value): string => $this->socialUrl($value, 'https://instagram.com/'),
                true,
            ),
            $this->contact(
                'email',
                'ایمیل',
                $settings['contact_email'] ?? null,
                fn (string $value): string => 'mailto:'.$value,
            ),
            $this->contact(
                'phone',
                'شماره تماس',
                $settings['contact_phone'] ?? null,
                fn (string $value): string => 'tel:'.preg_replace('/[^0-9+]/', '', $value),
            ),
            $this->contact(
                'hours',
                'ساعات پاسخ‌گویی',
                $settings['contact_hour'] ?? null,
            ),
        ];

        return array_values(array_filter($contacts));
    }

    /**
     * @param  (callable(string): string)|null  $urlResolver
     * @return array{key:string, label:string, value:string, url:?string, external:bool}|null
     */
    protected function contact(
        string $key,
        string $label,
        mixed $value,
        ?callable $urlResolver = null,
        bool $external = false,
    ): ?array {
        $value = is_string($value) ? trim($value) : '';

        if ($value === '') {
            return null;
        }

        return [
            'key' => $key,
            'label' => $label,
            'value' => $value,
            'url' => $urlResolver ? $urlResolver($value) : null,
            'external' => $external,
        ];
    }

    protected function socialUrl(string $value, string $baseUrl): string
    {
        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        return $baseUrl.ltrim($value, '@/');
    }

    /** @param array<string, mixed> $settings */
    protected function withFooterContactFallbacks(array $settings): array
    {
        $fallbacks = [
            'contact_telegram' => 'telegram',
            'contact_instagram' => 'instagram',
            'contact_email' => 'email',
            'contact_phone' => 'phone',
        ];

        foreach ($fallbacks as $aboutUsKey => $footerKey) {
            if (! filled($settings[$aboutUsKey] ?? null)) {
                $settings[$aboutUsKey] = get_setting('footer', $footerKey);
            }
        }

        return $settings;
    }

    protected function contentUrl(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : '';

        if (
            $value === ''
            || Str::startsWith($value, '//')
            || ! Str::startsWith($value, ['/', 'http://', 'https://'])
        ) {
            return null;
        }

        return $value;
    }
}
