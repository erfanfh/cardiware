<?php

namespace App\Application\Commerce\Exceptions;

use DomainException;

class CartItemPriceChangedException extends DomainException
{
    public function __construct()
    {
        parent::__construct('One or more cart item prices have changed.');
    }

    /**
     * Get user-friendly message.
     */
    public function userMessage(): string
    {
        return 'قیمت برخی آیتم‌های سبد خرید تغییر کرده است. لطفا سبد خرید را به‌روزرسانی کنید و دوباره برای پرداخت اقدام کنید.';
    }
}
