<?php

namespace App\Http\Resources\Api\V1\Identity;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'display_name' => $this->display_name ?? null,
            'first_name' => $this->first_name ?? null,
            'last_name' => $this->last_name ?? null,

            'email' => $this->email ?? null,
            'mobile' => $this->mobile ?? null,

            'created_at' => $this->created_at?->toISOString(),
            'jalali_created_at' => jalali_date($this->created_at)
        ];
    }
}
