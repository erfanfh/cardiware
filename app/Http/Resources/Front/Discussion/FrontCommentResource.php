<?php

namespace App\Http\Resources\Front\Discussion;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FrontCommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->display_name,
            'avatar' => mb_substr($this->display_name, 0, 1),
            'time' => $this->created_at->diffForHumans(),
            'text' => $this->content,
            'rating' => $this->rating_percent
                ? (int)round($this->rating_percent / 20)
                : 0,
            'likes_count' => $this->likes_count ?? 0,
            'dislikes_count' => $this->dislikes_count ?? 0,
            'reaction' => $this->current_user_reaction ?? null,
            'replies_count' => $this->replies_count ?? 0,
            'replies' => FrontCommentResource::collection($this->whenLoaded('replies')),
        ];
    }
}
