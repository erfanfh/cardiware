<?php

namespace App\Application\Commerce\Contracts;

interface ProductReportablePurchasable
{
    public function getReportProductId(): ?int;

    public function getReportStoreId(): ?int;
}
