<?php

namespace App\Domain\Discussion\Contracts;

interface HasVerifiedDiscussionBuyer
{
    public function hasVerifiedDiscussionPurchase(int $userId): bool;
}
