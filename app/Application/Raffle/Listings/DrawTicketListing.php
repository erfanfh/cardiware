<?php

namespace App\Application\Raffle\Listings;

use App\Infrastructure\Support\Listings\BaseListing;
use Illuminate\Database\Eloquent\Builder;

class DrawTicketListing extends BaseListing
{
    public function filters(): array
    {
        return ['code','status','user_id','user_query','grant_id','issued_at_from','issued_at_to'];
    }

    protected function allowedSorts(): array
    {
        return ['issued_at' => 'issued_at', 'id' => 'id'];
    }

    public function perPage(): int { return 20; }

    protected function filterCode(mixed $value): void { $this->query->where('code', 'like', '%'.trim((string)$value).'%'); }
    protected function filterStatus(mixed $value): void { $this->query->where('status', $value); }
    protected function filterUserId(mixed $value): void { $this->query->where('user_id', (int)$value); }
    protected function filterGrantId(mixed $value): void { $this->query->where('ticket_grant_id', (int)$value); }
    protected function filterUserQuery(mixed $value): void
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
    protected function filterIssuedAtFrom(mixed $value): void { $this->query->where('issued_at', '>=', $value); }
    protected function filterIssuedAtTo(mixed $value): void { $this->query->where('issued_at', '<=', $value); }
}
