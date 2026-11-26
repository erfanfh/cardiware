@extends('emails.front-layout')

@section('title', 'Order invoice ' . $receipt->orderNumber)

@section('content')
    <div style="display:none;font-size:1px;line-height:1px;color:#fff;max-height:0;max-width:0;opacity:0;overflow:hidden">Order payment {{ $receipt->orderNumber }} was successful. Your invoice and purchase details are available from Cardiware.</div>
    <span style="display:inline-block;padding:4px 14px;border-radius:20px;background:#dcfce7;color:#166534;font-size:12px;font-weight:bold">Successful payment</span>
    <h1 style="margin:16px 0 8px;font-size:23px;line-height:1.6;color:#0f172a">Thank you for your purchase!</h1>
    <p style="margin:0 0 20px;font-size:14px">Hello {{ $receipt->customerName }},<br>Your order was paid successfully. View your invoice and purchase details below.</p>

    <table role="presentation" width="100%" cellpadding="10" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;font-size:13px">
        <tr><td style="color:#64748b">Order number</td><td dir="ltr" style="text-align:left;font-weight:bold;color:#0f172a;overflow-wrap:anywhere">{{ $receipt->orderNumber }}</td></tr>
        <tr><td style="color:#64748b">Payment date (Tehran)</td><td dir="ltr" style="text-align:left">{{ $receipt->paidAt }}</td></tr>
        <tr><td style="color:#64748b">Payment method</td><td style="text-align:left">{{ $receipt->paymentMethod }}</td></tr>
        @if($receipt->trackingCode)
            <tr><td style="color:#64748b">Payment tracking code</td><td dir="ltr" style="text-align:left;overflow-wrap:anywhere">{{ $receipt->trackingCode }}</td></tr>
        @endif
    </table>

    <h2 style="margin:28px 0 12px;font-size:16px;color:#0f172a">Order details</h2>
    <table dir="ltr" width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;table-layout:fixed;font-size:12px;text-align:left">
        <thead>
            <tr style="background:#0f172a;color:#fff">
                <th scope="col" style="width:40%;text-align:left">Product / service</th>
                <th scope="col" style="width:12%;text-align:center">Quantity</th>
                <th scope="col" style="width:24%;text-align:left">Unit price</th>
                <th scope="col" style="width:24%;text-align:left">Final amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receipt->items as $item)
                <tr style="background:{{ $loop->even ? '#f8fafc' : '#ffffff' }}">
                    <td style="border-bottom:1px solid #e2e8f0;overflow-wrap:anywhere">
                        <strong>{{ $item['title'] }}</strong>
                        @if($item['parent_title'])
                            <div style="font-size:11px;color:#64748b">Add-on {{ $item['parent_title'] }}</div>
                        @endif
                        @if($item['discount'] > 0)
                            <div style="font-size:11px;color:#15803d">Discount: {{ $receipt->money($item['discount']) }}</div>
                        @endif
                    </td>
                    <td style="border-bottom:1px solid #e2e8f0;text-align:center">{{ number_format($item['quantity']) }}</td>
                    <td style="border-bottom:1px solid #e2e8f0;text-align:left">{{ $receipt->money($item['unit_price']) }}</td>
                    <td style="border-bottom:1px solid #e2e8f0;text-align:left;font-weight:bold">{{ $receipt->money($item['total']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table role="presentation" width="100%" cellpadding="8" cellspacing="0" style="margin-top:18px;font-size:13px">
        <tr><td style="color:#64748b">Items subtotal</td><td style="text-align:left">{{ $receipt->money($receipt->subtotal) }}</td></tr>
        @if($receipt->productDiscount > 0)
            <tr><td style="color:#15803d">Product discounts</td><td style="text-align:left;color:#15803d">{{ $receipt->money($receipt->productDiscount) }}</td></tr>
        @endif
        @if($receipt->couponDiscount > 0)
            <tr><td style="color:#15803d">Coupon discounts @if($receipt->couponCodes)<span dir="ltr">({{ implode(' + ', $receipt->couponCodes) }})</span>@endif</td><td style="text-align:left;color:#15803d">{{ $receipt->money($receipt->couponDiscount) }}</td></tr>
        @endif
        <tr><td style="color:#64748b">Tax @if($receipt->tax > 0)({{ $receipt->taxRate }}%)@endif</td><td style="text-align:left">{{ $receipt->money($receipt->tax) }}</td></tr>
        <tr style="background:#eff6ff;color:#1e40af;font-size:16px;font-weight:bold"><td style="padding:14px 8px">Amount paid</td><td style="padding:14px 8px;text-align:left">{{ $receipt->money($receipt->total) }}</td></tr>
    </table>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:28px">
        <tr><td align="center"><a href="{{ $receipt->ordersUrl }}" style="display:inline-block;padding:12px 28px;background:#2563eb;border-radius:8px;color:#fff;font-size:14px;font-weight:bold;text-decoration:none">View my orders</a></td></tr>
    </table>
    <p style="margin:20px 0 0;font-size:12px;color:#64748b">For purchase follow-up or assistance, contact support in your account and have your order number ready.</p>
@endsection
