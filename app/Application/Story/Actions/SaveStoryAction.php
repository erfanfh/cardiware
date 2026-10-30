<?php

namespace App\Application\Story\Actions;

use App\Domain\Story\Models\Story;

class SaveStoryAction
{
    public function execute(array $data, ?Story $story = null): Story
    {
        $story ??= new Story;
        $story->fill([
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'caption' => $data['caption'] ?? null,
            'image_id' => $data['image']['id'],
            'thumbnail_id' => $data['thumbnail']['id'] ?? null,
            'link_url' => $data['link_url'] ?? null,
            'link_label' => $data['link_label'] ?? null,
            'duration_seconds' => $data['duration_seconds'],
            'sort_order' => $data['sort_order'],
            'is_active' => $data['is_active'],
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
        ])->save();

        return $story;
    }
}
