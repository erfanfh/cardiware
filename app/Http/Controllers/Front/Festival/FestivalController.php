<?php

namespace App\Http\Controllers\Front\Festival;

use App\Http\Controllers\Controller;
use App\Infrastructure\Services\Setting\Groups\FestivalSettings;
use Illuminate\Http\Response;
use Illuminate\View\View;

class FestivalController extends Controller
{
    public function index(FestivalSettings $settings): View|Response
    {
        // جشنواره غیرفعال = 404 برای کاربر عادی
        abort_unless($settings->isEnabled(), 404);

        $items = collect($settings->get('deals_items', []) ?? [])
            ->filter(fn ($item) => filled($item['title'] ?? null))
            ->values();

        return view('front.festival.index', [
            'festival' => [
                'badge' => (string) $settings->get('badge_text', ''),
                'title' => (string) $settings->get('title', 'جشنواره ویژه'),
                'subtitle' => (string) $settings->get('subtitle', ''),
                'description' => (string) $settings->get('description', ''),
                'themeColor' => $settings->themeColor(),
                'countdownEnabled' => (bool) $settings->get('countdown_enabled', false),
                'countdownEndsAt' => (string) $settings->get('countdown_ends_at', ''),
                'discountText' => (string) $settings->get('discount_text', ''),
                'buttonText' => (string) $settings->get('button_text', ''),
                'buttonUrl' => (string) $settings->get('button_url', ''),
                'couponCode' => trim((string) $settings->get('coupon_code', '')),
                'couponHint' => (string) $settings->get('coupon_hint', ''),
                'dealsTitle' => (string) $settings->get('deals_title', 'پیشنهادهای جشنواره'),
                'footerNote' => (string) $settings->get('footer_note', ''),
                'heroImage' => $settings->get('hero_image')?->path,
                'bgImage' => $settings->get('bg_image')?->path,
                'bgPattern' => (string) $settings->get('bg_pattern', 'none'),
                'deals' => $items,
            ],
        ]);
    }
}
