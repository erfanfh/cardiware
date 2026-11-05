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

        return [
            'vip' => [
                'title' => (string)get_setting('home', 'subscription_title', 'اشتراک ویژه VIP'),
                'subtitle' => (string)get_setting('home', 'subscription_subtitle', ''),
                'description' => (string)get_setting('home', 'subscription_description', ''),
                'image_path' => get_setting('home', 'subscription_image')?->path,
                'button_title' => (string)get_setting('home', 'subscription_button_title', 'عضویت VIP'),
                'button_url' => (string)get_setting('home', 'subscription_button_url', ''),
            ],
            'projectsTitle' => $projectsBlock['title'],
            'projects' => $projectsBlock['items']->take(8)->values(),
            'topUsers' => collect($leaderboard['leaders'] ?? [])->take(12)->values(),
        ];
    }
}
