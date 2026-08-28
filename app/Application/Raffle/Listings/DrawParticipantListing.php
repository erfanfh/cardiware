<?php

namespace App\Application\Raffle\Listings;

use App\Infrastructure\Support\Listings\BaseListing;
use Illuminate\Database\Eloquent\Builder;

class DrawParticipantListing extends BaseListing
{
    public function filters(): array
    {
        return ['user_id','query','active_tickets_from','active_tickets_to'];
    }

    protected function allowedSorts(): array
    {
        return [
            'active_tickets_count' => 'active_tickets_count',
            'total_tickets_count' => 'total_tickets_count',
            'latest_ticket_at' => 'latest_ticket_at',
        ];
    }

    public function perPage(): int { return 20; }

    protected function filterUserId(mixed $value): void { $this->query->where('user_id', (int)$value); }
    protected function filterQuery(mixed $value): void
    {
        $term = trim((string)$value);
        $this->query->whereHas('user', function (Builder $query) use ($term): void {
            $query->where(function (Builder $q) use ($term): void {
                $q->where('display_name', 'like', "%{$term}%")
                    ->orWhere('mobile', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$term}%"]);
            });
        });
    }
    protected function filterActiveTicketsFrom(mixed $value): void { $this->query->havingRaw('SUM(status = ?) >= ?', ['active', (int)$value]); }
    protected function filterActiveTicketsTo(mixed $value): void { $this->query->havingRaw('SUM(status = ?) <= ?', ['active', (int)$value]); }
}
