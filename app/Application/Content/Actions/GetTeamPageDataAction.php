<?php

namespace App\Application\Content\Actions;

use App\Domain\Catalog\Enums\ProductStatusEnum;
use App\Domain\Catalog\Models\Product;
use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Content\Models\Post;
use App\Domain\Organization\Models\TeamMember;
use App\Domain\Video\Enums\VideoStatusEnum;
use App\Domain\Video\Models\Video;
use App\Infrastructure\Services\Setting\Groups\TeamSettings;
use Illuminate\Support\Collection;

class GetTeamPageDataAction
{
    public function __construct(
        protected TeamSettings $settings,
    ) {}

    /** @return array<string, mixed> */
    public function execute(): array
    {
        $settings = $this->settings->values();
        $memberIds = collect($settings['member_ids'] ?? [])
            ->filter(fn (mixed $id): bool => is_numeric($id) && (int) $id > 0)
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();

        $members = $this->selectedMembers($memberIds);

        $settings['cta_primary_button_url'] = $this->safeUrl($settings['cta_primary_button_url'] ?? null);
        $settings['cta_secondary_button_url'] = $this->safeUrl($settings['cta_secondary_button_url'] ?? null);

        return [
            'teamPage' => $settings,
            'teamMembers' => $members,
            'teamStats' => [
                'members' => $members->count(),
                'products' => Product::withoutGlobalScopes()
                    ->where('status', ProductStatusEnum::ACTIVE)
                    ->where('published_at', '<=', now())
                    ->count(),
                'content' => Post::withoutGlobalScopes()
                    ->where('status', PostStatusEnum::ACTIVE)
                    ->where('published_at', '<=', now())
                    ->count()
                    + Video::withoutGlobalScopes()
                        ->where('status', VideoStatusEnum::ACTIVE)
                        ->where('published_at', '<=', now())
                        ->count(),
            ],
        ];
    }

    /** @param Collection<int, int> $memberIds */
    protected function selectedMembers(Collection $memberIds): Collection
    {
        if ($memberIds->isEmpty()) {
            return collect();
        }

        $positions = $memberIds->flip();

        return TeamMember::query()
            ->with(['picture.primaryVariant', 'picture.variants'])
            ->whereKey($memberIds->all())
            ->get()
            ->sortBy(fn (TeamMember $member): int => (int) $positions->get($member->getKey(), PHP_INT_MAX))
            ->values();
    }

    protected function safeUrl(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : '';

        if ($value === '' || ! preg_match('/^(https?:\/\/|\/(?!\/)|mailto:)/i', $value)) {
            return null;
        }

        return $value;
    }
}
