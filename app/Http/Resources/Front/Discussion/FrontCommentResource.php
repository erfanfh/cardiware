<?php

namespace App\Http\Resources\Front\Discussion;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FrontCommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->display_name,
            'time' => time_ago($this->created_at) ?? 'همین الان',
            'text' => $this->content,
            'rating' => $this->rating_percent !== null
                ? (int)round($this->rating_percent / 20)
                : 0,
            'likes' => (int)($this->likes_count ?? 0),
            'liked' => (bool)($this->liked_by_current_user ?? false),
            'replies' => FrontCommentReplyResource::collection($this->whenLoaded('approvedReplies')),
        ];
    }
}
