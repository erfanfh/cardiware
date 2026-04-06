<?php

namespace App\Http\Resources\Front\Discussion;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FrontCommentReplyResource extends JsonResource
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
            'time' => $this->created_at?->diffForHumans() ?? 'همین الان',
            'text' => $this->content,
        ];
    }
}
