<?php

namespace App\Livewire\Front\Home;

use App\Application\Content\Actions\GetPostFeaturesAction;
use App\Application\Home\Actions\GetHomePostTabsAction;
use App\Domain\Content\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class FreePosts extends Component
{
    public Collection $postTabs;
    public ?string $activeTabKey;
    public string $mainTitle = '';
    public string $list1Title = '';
    public string $list2Title = '';
    public Collection $mainPosts;
    public Collection $list1Posts;
    public Collection $list2Posts;

    public function mount(): void
    {
        $this->postTabs = app(GetHomePostTabsAction::class)->execute();
        $this->activeTabKey = $this->postTabs[0]['key'] ?? null;
        $this->changeTab($this->activeTabKey);
    }

    public function changeTab(?string $key): void
    {
        $this->activeTabKey = $key;
        $activeItem = collect($this->postTabs)->where('key', $key)->first() ?? null;

        if (null === $activeItem)
            return;

        $this->mainTitle = $activeItem['main_title'] ?? '';
        $this->list1Title = $activeItem['list_box_1_title'] ?? '';
        $this->list2Title = $activeItem['list_box_2_title'] ?? '';

        $this->mainPosts = Post::with(['picture.primaryVariant', 'featureAssignments.featureDefinition', 'categories'])
            ->whereHas('categories', function ($query) use ($activeItem) {
                $query->whereIn('taxonomy_terms.id', $activeItem['main_category_ids']);
            })
            ->latest('published_at')
            ->limit($activeItem['main_count'])
            ->get()
            ->map(function ($post) {
                $post->features = app(GetPostFeaturesAction::class)->execute($post);
                return $post;
            });

        $this->list1Posts = Post::whereHas('categories', function ($query) use ($activeItem) {
            $query->whereIn('taxonomy_terms.id', $activeItem['list_box_1_category_ids']);
        })
            ->latest('published_at')
            ->limit($activeItem['list_box_1_count'])
            ->get();

        $this->list2Posts = Post::whereHas('categories', function ($query) use ($activeItem) {
            $query->whereIn('taxonomy_terms.id', $activeItem['list_box_2_category_ids']);
        })
            ->latest('published_at')
            ->limit($activeItem['list_box_2_count'])
            ->get();
    }

    public function render(): View
    {
        return view('livewire.front.home.free-posts');
    }
}
