<?php

namespace App\Application\Content\Actions;

use App\Domain\Content\Enums\PostInformationBoxTypeEnum;
use App\Domain\Content\Models\Post;

class SyncPostInformationBoxesAction
{
    public function execute(Post $post, ?array $informationBoxes): void
    {
        $informationBoxes ??= [];

        foreach (PostInformationBoxTypeEnum::cases() as $type) {
            $data = $informationBoxes[$type->value] ?? [];

            $title = $data['title'] ?? null;
            $body = $data['body'] ?? null;

            if (blank($title) && blank($body)) {
                $post->informationBoxes()
                    ->where('type', $type->value)
                    ->get()->each->delete();

                continue;
            }

            $post->informationBoxes()->updateOrCreate(
                [
                    'type' => $type->value,
                ],
                [
                    'title' => $title,
                    'body' => $body,
                ]
            );
        }
    }
}
