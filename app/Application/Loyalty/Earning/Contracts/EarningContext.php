<?php

namespace App\Application\Loyalty\Earning\Contracts;

use App\Domain\Identity\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

interface EarningContext
{
    public function user(): User;

    public function occurredAt(): CarbonImmutable;

    public function reference(): ?Model;
}
