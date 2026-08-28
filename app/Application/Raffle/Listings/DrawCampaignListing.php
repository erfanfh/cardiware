<?php

namespace App\Application\Raffle\Listings;

use App\Infrastructure\Support\Listings\BaseListing;

class DrawCampaignListing extends BaseListing
{
    public function filters(): array
    {
        return ['id','title','slug','status','starts_at_from','starts_at_to','closes_at_from','closes_at_to','created_at_from','created_at_to'];
    }

    protected function allowedSorts(): array
    {
        return [
            'created_at' => 'created_at',
            'starts_at' => 'starts_at',
            'closes_at' => 'closes_at',
            'active_tickets_count' => 'active_tickets_count',
            'participants_count' => 'participants_count',
        ];
    }

    protected function filterId(mixed $value): void { $this->query->where('id', $value); }
    protected function filterTitle(mixed $value): void { $this->query->where('title', 'like', '%'.$value.'%'); }
    protected function filterSlug(mixed $value): void { $this->query->where('slug', 'like', '%'.$value.'%'); }
    protected function filterStatus(mixed $value): void { $this->query->where('status', $value); }
    protected function filterStartsAtFrom(mixed $value): void { $this->query->where('starts_at', '>=', $value); }
    protected function filterStartsAtTo(mixed $value): void { $this->query->where('starts_at', '<=', $value); }
    protected function filterClosesAtFrom(mixed $value): void { $this->query->where('closes_at', '>=', $value); }
    protected function filterClosesAtTo(mixed $value): void { $this->query->where('closes_at', '<=', $value); }
    protected function filterCreatedAtFrom(mixed $value): void { $this->query->whereDate('created_at', '>=', $value); }
    protected function filterCreatedAtTo(mixed $value): void { $this->query->whereDate('created_at', '<=', $value); }
}
