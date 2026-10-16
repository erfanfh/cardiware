مودینگ‌وی | ModdingWay
فاکتور سفارش {{ $receipt->orderNumber }}
پرداخت موفق

سلام {{ $receipt->customerName }}،
از خرید شما متشکریم. سفارش شما با موفقیت پرداخت شد.

شماره سفارش: {{ $receipt->orderNumber }}
تاریخ پرداخت (تهران): {{ $receipt->paidAt }}
روش پرداخت: {{ $receipt->paymentMethod }}
@if($receipt->trackingCode)
کد پیگیری پرداخت: {{ $receipt->trackingCode }}
@endif

جزئیات سفارش (تمام مبالغ به تومان):
@foreach($receipt->items as $item)
- {{ $item['title'] }}@if($item['parent_title']) (افزونهٔ {{ $item['parent_title'] }})@endif

  تعداد: {{ $item['quantity'] }} | قیمت واحد: {{ $receipt->money($item['unit_price']) }} | تخفیف: {{ $receipt->money($item['discount']) }} | مبلغ نهایی: {{ $receipt->money($item['total']) }}
@endforeach

جمع اقلام: {{ $receipt->money($receipt->subtotal) }}
تخفیف محصولات: {{ $receipt->money($receipt->productDiscount) }}
تخفیف کدهای خرید: {{ $receipt->money($receipt->couponDiscount) }}@if($receipt->couponCodes) ({{ implode(' + ', $receipt->couponCodes) }})@endif

مالیات ({{ $receipt->taxRate }}٪): {{ $receipt->money($receipt->tax) }}
مبلغ پرداخت‌شده: {{ $receipt->money($receipt->total) }}

مشاهده سفارش‌های من:
{{ $receipt->ordersUrl }}

برای پیگیری خرید، از بخش پشتیبانی حساب کاربری اقدام کنید و شماره سفارش را همراه داشته باشید.
این ایمیل به‌صورت خودکار ارسال شده است؛ لطفاً به آن پاسخ ندهید.
