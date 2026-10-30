<?php

namespace App\Http\Controllers\Front\Landings;

use App\Support\Landings;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * صفحات جزئیات محصول — /{landing}/{page} و /{landing}/leagues/{league}[/{$team}]
 * نوع‌های صفحه: leagues | stadiums | trophies | faces (از config کاتالوگ مشترک)
 */
class LandingPageController
{
    public function show(string $landing, string $page): View
    {
        $locale = Landings::localeFromRequest();

        $data = Landings::resolve(config("product-landings.landings.{$landing}"), $locale);
        abort_if($data === null, 404);

        $pageConfig = $data['pages'][$page] ?? null;
        abort_if($pageConfig === null, 404);

        return view('front.landings.detail', [
            'slug' => $landing,
            'pageSlug' => $page,
            'data' => $data,
            'pageData' => $this->enrichPage($page, $locale),
            'locale' => $locale,
        ]);
    }

    public function league(string $landing, string $league): View
    {
        $locale = Landings::localeFromRequest();

        $data = Landings::resolve(config("product-landings.landings.{$landing}"), $locale);
        abort_if($data === null, 404);

        $rawLeague = config("landing-catalog.leagues.{$league}");
        abort_if($rawLeague === null, 404);

        $leagueData = Landings::resolve($rawLeague, $locale);

        // slug پایدار هر تیم از نام انگلیسی
        $teamSlugs = array_map(
            fn ($t) => Str::slug($t['name']['en'] ?? ''),
            $rawLeague['teams'] ?? []
        );

        return view('front.landings.league', [
            'slug' => $landing,
            'leagueSlug' => $league,
            'data' => $data,
            'league' => $leagueData,
            'teamSlugs' => $teamSlugs,
            'locale' => $locale,
        ]);
    }

    /**
     * صفحه اختصاصی هر تیم — /{landing}/leagues/{league}/{team}
     * slug تیم از نام انگلیسی ساخته می‌شود (پایدار و خوانا در URL).
     */
    public function team(string $landing, string $league, string $team): View
    {
        $locale = Landings::localeFromRequest();

        $data = Landings::resolve(config("product-landings.landings.{$landing}"), $locale);
        abort_if($data === null, 404);

        // تیم را روی config خام (دوزبانه) پیدا کن تا slug پایدار باشد
        $rawLeague = config("landing-catalog.leagues.{$league}");
        abort_if($rawLeague === null, 404);

        $rawTeam = collect($rawLeague['teams'] ?? [])
            ->first(fn ($t) => Str::slug($t['name']['en'] ?? '') === $team);
        abort_if($rawTeam === null, 404);

        $leagueData = Landings::resolve($rawLeague, $locale);
        $teamData = Landings::resolve($rawTeam, $locale);

        // دیتای جزئیات: از config تیم یا پیش‌فرض عمومی (resolve تا رشته شود)
        $detail = $teamData['detail'] ?? null;
        $teamData['stats'] = Landings::resolve($detail['stats'] ?? [
            ['label' => ['fa' => 'چهره واقعی بازیکن', 'en' => 'Real player faces'], 'value' => ['fa' => 'عده‌ای', 'en' => 'Partial']],
            ['label' => ['fa' => 'استادیوم واقعی', 'en' => 'Real stadium'], 'value' => ['fa' => 'دارد', 'en' => 'Yes']],
            ['label' => ['fa' => 'چهره مربی', 'en' => 'Coach face'], 'value' => ['fa' => '—', 'en' => '—']],
        ], $locale);
        $teamData['gallery'] = Landings::resolve($detail['gallery'] ?? [
            ['src' => 'images/landings/shared/g2-gameplay.svg', 'caption' => ['fa' => 'درون بازی', 'en' => 'In-game']],
            ['src' => 'images/landings/shared/g4-faces.svg', 'caption' => ['fa' => 'بازیکنان', 'en' => 'Players']],
            ['src' => 'images/landings/shared/g5-broadcast.svg', 'caption' => ['fa' => 'صحنه مسابقه', 'en' => 'Match scene']],
        ], $locale);
        $teamData['description'] = $detail['description'] ?? null;
        $teamData['spinner'] = $detail['spinner'] ?? null;
        $teamData['kit3d'] = $detail['kit3d'] ?? null;

        /*
         * موتور بلوکی محتوا — ترتیب و ترکیب هر بخش از config می‌آید؛
         * بدون blocks، چیدمان پیش‌فرض (اسپینر/کیت/گالری) ساخته می‌شود.
         */
        $defaultBlocks = [];
        if (!empty($teamData['kit3d'])) {
            $defaultBlocks[] = ['type' => 'kit3d'] + $teamData['kit3d'];
        }
        if (!empty($teamData['spinner'])) {
            $defaultBlocks[] = ['type' => 'spinner'] + $teamData['spinner'];
        }
        $defaultBlocks[] = [
            'type' => 'gallery',
            'title' => ['fa' => 'اسکرین‌شات‌ها', 'en' => 'Screenshots'],
            'images' => $teamData['gallery'],
        ];
        $teamData['blocks'] = Landings::resolve($detail['blocks'] ?? $defaultBlocks, $locale);
        $teamData['tags'] = Landings::resolve($detail['tags'] ?? [
            ['fa' => 'کیت فصل جاری', 'en' => 'Current-season kit'],
            ['fa' => 'ترکیب به‌روز', 'en' => 'Updated squad'],
        ], $locale);

        return view('front.landings.team', [
            'slug' => $landing,
            'leagueSlug' => $league,
            'teamSlug' => $team,
            'data' => $data,
            'league' => $leagueData,
            'team' => $teamData,
            'locale' => $locale,
        ]);
    }

    /**
     * صفحات نوع leagues/stadiums/trophies دیتای خود را از کاتالوگ مشترک می‌گیرند.
     */
    private function enrichPage(string $page, string $locale): array
    {
        $config = Landings::resolve(config("product-landings.pages.{$page}"), $locale) ?? [];

        $catalogMap = [
            'leagues' => 'leagues',
            'stadiums' => 'stadiums',
            'trophies' => 'trophies',
        ];

        if (isset($catalogMap[$page])) {
            $catalog = Landings::resolve(config('landing-catalog.'.$catalogMap[$page]), $locale);
            $config['catalog'] = $catalog;
        }

        return $config;
    }
}
