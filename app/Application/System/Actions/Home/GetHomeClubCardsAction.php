<?php

namespace App\Application\System\Actions\Home;

use App\Application\Loyalty\Actions\GetAudienceClubLeaderboardAction;
use App\Domain\Loyalty\Models\PointAccount;
use Illuminate\Support\Collection;

class GetHomeClubCardsAction
{
    public function __construct(
        private readonly GetHomeProjectsBlockAction $getHomeProjectsBlockAction,
        private readonly GetAudienceClubLeaderboardAction $getAudienceClubLeaderboardAction,
    ) {}

    /**
     * داده‌های سه‌کارت «باشگاه» صفحه اصلی: اشتراک ویژه، پروژه‌ها و کاربران برتر.
     *
     * @return array{
     *     vip: array{title:string, subtitle:string, button_title:string, button_url:string},
     *     projectsTitle: string,
     *     projects: Collection<int, \App\Domain\Project\Models\Project>,
     *     topUsers: Collection<int, PointAccount>,
     * }
     */
    public function execute(): array
    {
        $projectsBlock = $this->getHomeProjectsBlockAction->execute();
        $leaderboard = $this->getAudienceClubLeaderboardAction->execute(null);

        // محدودیت‌ها از پنل قابل تنظیم‌اند (تنظیمات صفحه اصلی ← کارت‌های باشگاه).
        $projectsLimit = (int) (get_setting('home', 'club_cards_projects_limit', 8) ?: 8);
        $leadersLimit = (int) (get_setting('home', 'club_cards_leaders_limit', 12) ?: 12);

        return [
            'vip' => [
                'title' => (string)get_setting('home', 'subscription_title', 'اشتراک ویژه VIP'),
                'subtitle' => (string)get_setting('home', 'subscription_subtitle', ''),
                'description' => (string)get_setting('home', 'subscription_description', ''),
                'image_path' => get_setting('home', 'subscription_image')?->path,
                'button_title' => (string)get_setting('home', 'subscription_button_title', 'عضویت VIP'),
                'button_url' => (string)get_setting('home', 'subscription_button_url', ''),
                'badge' => (string)get_setting('home', 'club_cards_vip_badge', ''),
            ],
            'projectsTitle' => $projectsBlock['title'],
            'projectsSubtitle' => (string)get_setting('home', 'club_cards_projects_subtitle', ''),
            'projects' => $projectsBlock['items']->take(max(1, min(24, $projectsLimit)))->values(),
            'topUsers' => collect($leaderboard['leaders'] ?? [])->take(max(1, min(50, $leadersLimit)))->values(),
            'leadersTitle' => (string)get_setting('home', 'club_cards_leaders_title', 'کاربران برتر هفته'),
            'leadersSubtitle' => (string)get_setting('home', 'club_cards_leaders_subtitle', ''),
            'cards' => [
                'show_vip' => (bool)get_setting('home', 'club_cards_show_vip', 1),
                'show_projects' => (bool)get_setting('home', 'club_cards_show_projects', 1),
                'show_leaders' => (bool)get_setting('home', 'club_cards_show_leaders', 1),
            ],
        ];
    }
}
