<?php

namespace App\Http\Resources\Api\V1\Library;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductSaleItemFileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'sort_order' => $this->sort_order,

            'download_limit' => $this->getAttribute(
                'resolved_download_limit'
            ),

            'downloads_count' => (int)$this->getAttribute(
                'downloads_count'
            ),

            'remaining_downloads' => $this->getAttribute(
                'remaining_downloads'
            ),

            'can_download' => (bool)$this->getAttribute(
                'can_download'
            ),
        ];
    }
}
