<?php

namespace App\Livewire\Front\Home;

use App\Application\System\Actions\Home\GetHomeFreePostsTabDataAction;
use App\Application\System\Actions\Home\GetHomePostTabsAction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\Attributes\Locked;

class FreePosts extends Component
{
    #[Locked]
    public Collection $postTabs;
    public ?string $activeTabKey;
    public string $mainTitle = '';
    public string $list1Title = '';
    public string $list2Title = '';
    public Collection $mainPosts;
    public Collection $list1Posts;
    public Collection $list2Posts;

    /** تعداد کارت‌های بزرگ (۳ پایه؛ پین‌شده‌ها اضافه می‌شوند) */
    public int $heroCount = 3;

    public function mount(): void
    {
        $this->postTabs = app(GetHomePostTabsAction::class)->execute();
        $this->activeTabKey = $this->postTabs[0]['key'] ?? null;

        $this->mainPosts = collect();
        $this->list1Posts = collect();
        $this->list2Posts = collect();

        $this->changeTab($this->activeTabKey);
    }

    public function changeTab(?string $key): void
    {
        $activeItem = collect($this->postTabs)
            ->where('key', $key)
            ->first();

        if ($activeItem === null) {
            return;
        }

        $this->activeTabKey = $key;
        $this->mainTitle = $activeItem['main_title'] ?? '';
        $this->list1Title = $activeItem['list_box_1_title'] ?? '';
        $this->list2Title = $activeItem['list_box_2_title'] ?? '';

        $data = app(GetHomeFreePostsTabDataAction::class)->execute($activeItem);

        $this->heroCount = $activeItem['hero_count'];
        $this->mainPosts = $data['mainPosts'];
        $this->list1Posts = $data['list1Posts'];
        $this->list2Posts = $data['list2Posts'];
    }

    public function render(): View
    {
        return view('livewire.front.home.free-posts');
    }
}
