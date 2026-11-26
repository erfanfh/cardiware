Cardiware | Cardiware
Order invoice {{ $receipt->orderNumber }}
Successful payment

Hello {{ $receipt->customerName }},
Thank you for your purchase. Your order was paid successfully.

Order number: {{ $receipt->orderNumber }}
Payment date (Tehran): {{ $receipt->paidAt }}
Payment method: {{ $receipt->paymentMethod }}
@if($receipt->trackingCode)
Payment tracking code: {{ $receipt->trackingCode }}
@endif

Order details (all amounts in toman):
@foreach($receipt->items as $item)
- {{ $item['title'] }}@if($item['parent_title']) (Add-on {{ $item['parent_title'] }})@endif

  Quantity: {{ $item['quantity'] }} | Unit price: {{ $receipt->money($item['unit_price']) }} | Discount: {{ $receipt->money($item['discount']) }} | Final amount: {{ $receipt->money($item['total']) }}
@endforeach

Items subtotal: {{ $receipt->money($receipt->subtotal) }}
Product discounts: {{ $receipt->money($receipt->productDiscount) }}
Coupon discounts: {{ $receipt->money($receipt->couponDiscount) }}@if($receipt->couponCodes) ({{ implode(' + ', $receipt->couponCodes) }})@endif

Tax ({{ $receipt->taxRate }}%): {{ $receipt->money($receipt->tax) }}
Amount paid: {{ $receipt->money($receipt->total) }}

View my orders:
{{ $receipt->ordersUrl }}

To follow up on your purchase, contact support in your account and have your order number ready.
This email was sent automatically; please do not reply.
