<?php

namespace App\Http\Controllers\Front\Commerce;

use App\Application\Vip\Actions\TrackVipSaleItemFileDownloadAction;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Http\Controllers\Controller;
use Illuminate\Http\Client\Response as UpstreamResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadVipSaleItemFileController extends Controller
{
    public function __invoke(Request $request, ProductSaleItem $saleItem, ProductSaleItemFile $file, TrackVipSaleItemFileDownloadAction $downloads): StreamedResponse
    {
        abort_unless($request->user()?->hasActiveVipSubscription(), 403);
        abort_unless($saleItem->vip_access_enabled && ! $saleItem->launcher_enabled && $saleItem->isPurchasableAvailable(), 403);
        abort_unless($saleItem->product()->withoutGlobalScopes()->first()?->isPubliclyVisible(), 403);
        abort_unless((int) $file->product_sale_item_id === (int) $saleItem->id && $file->is_active, 403);
        $claim = $request->user()->vipSaleItemClaims()
            ->where('product_sale_item_id', $saleItem->getKey())->first();
        abort_unless($claim, 403);
        $downloads->assertRemaining($claim, $file);

        $url = (string) $file->url;
        $this->ensureSafeUrl($url);

        $upstreamHeaders = [];
        if ($request->hasHeader('Range')) {
            $range = (string) $request->header('Range');
            abort_unless(preg_match('/^bytes=(?:\d+-\d*|-\d+)$/', $range) === 1, 416);
            $upstreamHeaders['Range'] = $range;
        }

        $upstream = $this->openUpstream($request->isMethod('HEAD') ? 'HEAD' : 'GET', $url, $upstreamHeaders);
        abort_unless(in_array($upstream->status(), [200, 206, 416], true), 502);
        if (! $request->isMethod('HEAD') && in_array($upstream->status(), [200, 206], true)) {
            $downloads->record($claim, $file);
        }

        $headers = [
            'Cache-Control' => 'private, no-store, max-age=0',
            'Content-Type' => $upstream->header('Content-Type') ?: 'application/octet-stream',
            'Content-Disposition' => "attachment; filename*=UTF-8''".rawurlencode(
                basename((string) ($file->file_name ?: parse_url($url, PHP_URL_PATH) ?: 'download'))
            ),
            'X-Content-Type-Options' => 'nosniff',
        ];
        foreach (['Content-Length', 'Content-Range', 'Accept-Ranges'] as $header) {
            if ($upstream->header($header) !== null) {
                $headers[$header] = $upstream->header($header);
            }
        }

        return response()->stream(function () use ($request, $upstream): void {
            $body = $upstream->toPsrResponse()->getBody();
            if ($request->isMethod('HEAD') || ! $request->user()?->hasActiveVipSubscription()) {
                $body->close();
                return;
            }

            $lastAccessCheck = time();
            while (! $body->eof()) {
                if (time() - $lastAccessCheck >= 5) {
                    if (! $request->user()?->hasActiveVipSubscription()) {
                        break;
                    }
                    $lastAccessCheck = time();
                }

                echo $body->read(1024 * 1024);
                flush();
            }
            $body->close();
        }, $upstream->status(), $headers);
    }

    private function openUpstream(string $method, string $url, array $headers): UpstreamResponse
    {
        // The source URL is never sent to the browser. Remote redirects are rejected.
        return Http::withOptions([
            'allow_redirects' => false,
            'stream' => true,
            'connect_timeout' => 10,
            'timeout' => 0,
        ])->withHeaders($headers)->send($method, $url);
    }

    private function ensureSafeUrl(string $url): void
    {
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $allowedHosts = array_map(
            static fn (string $value): string => strtolower(trim($value)),
            config('launcher.allowed_download_hosts', []),
        );

        abort_if(
            filter_var($url, FILTER_VALIDATE_URL) === false
                || $scheme !== 'https'
                || ($allowedHosts !== [] && !in_array($host, $allowedHosts, true)),
            403,
        );
    }
}
