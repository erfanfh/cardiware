<?php

namespace App\Http\Controllers\Front\Commerce;

use App\Application\Commerce\Exceptions\PurchaseAccessFileUnavailableException;
use App\Application\Launcher\Support\RemoteDownloadFileMetadataResolver;
use App\Domain\Catalog\Models\ProductSaleItemFile;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Commerce\Models\PurchaseAccessDownload;
use App\Domain\Commerce\Scopes\ActivePurchaseAccessScope;
use App\Domain\Launcher\Models\LauncherDownloadSession;
use App\Domain\Launcher\Models\LauncherMod;
use App\Domain\Launcher\Models\LauncherModFile;
use App\Domain\Launcher\Models\LauncherUpdate;
use App\Domain\Launcher\Models\LauncherUpdateFile;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class StreamLauncherDownloadController extends Controller
{
    public function __invoke(
        Request $request,
        int $purchaseAccess,
        string $contentType,
        int $content,
        int $file,
        string $downloadSessionId,
        RemoteDownloadFileMetadataResolver $metadataResolver,
    ): Response {
        $access = PurchaseAccess::query()
            ->withoutGlobalScope(ActivePurchaseAccessScope::class)
            ->whereKey($purchaseAccess)
            ->firstOrFail();

        abort_unless($access->isUsable(), 403);

        [$sourceType, $downloadFile, $sourceUrl] = $this->resolveDownload(
            access: $access,
            contentType: $contentType,
            contentId: $content,
            fileId: $file,
            sessionId: strtolower($downloadSessionId),
        );

        $metadata = $metadataResolver->resolve(
            sourceType: $sourceType,
            file: $downloadFile,
            url: $sourceUrl,
        );

        $totalSize = $metadata['size_bytes'];
        $range = $this->resolveRange($request->header('Range'), $totalSize);
        $start = $range['start'] ?? 0;
        $end = $range['end'] ?? ($totalSize - 1);
        $length = $end - $start + 1;
        $statusCode = $range === null ? 200 : 206;

        $fileName = $this->resolveFileName($downloadFile, $sourceUrl);
        $mimeType = $metadata['mime_type'] ?: 'application/octet-stream';

        $headers = [
            'Accept-Ranges' => 'bytes',
            'Content-Type' => $mimeType,
            'Content-Encoding' => 'identity',
            'Content-Length' => (string)$length,
            'Content-Disposition' => $this->contentDisposition($fileName),
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Accel-Buffering' => 'no',
        ];

        if ($range !== null) {
            $headers['Content-Range'] = sprintf(
                'bytes %d-%d/%d',
                $start,
                $end,
                $totalSize,
            );
        }

        if ($request->isMethod('HEAD')) {
            return response('', $statusCode, $headers);
        }

        return response()->stream(
            function () use ($sourceUrl, $start, $end): void {
                $this->streamRemoteRange($sourceUrl, $start, $end);
            },
            $statusCode,
            $headers,
        );
    }

    /**
     * @return array{0:string,1:Model,2:string}
     */
    private function resolveDownload(
        PurchaseAccess $access,
        string $contentType,
        int $contentId,
        int $fileId,
        string $sessionId,
    ): array {
        if ($contentType === 'product') {
            abort_unless($contentId === (int)$access->product_sale_item_id, 404);

            $downloadSession = PurchaseAccessDownload::query()
                ->where('purchase_access_id', $access->getKey())
                ->where('product_sale_item_file_id', $fileId)
                ->where('download_session_id', $sessionId)
                ->firstOrFail();

            $this->ensureSessionIsAlive($downloadSession->session_expires_at);

            $file = ProductSaleItemFile::query()
                ->whereKey($fileId)
                ->where('product_sale_item_id', $access->product_sale_item_id)
                ->where('is_active', true)
                ->firstOrFail();

            return ['product', $file, (string)$file->url];
        }

        $downloadSession = LauncherDownloadSession::query()
            ->where('purchase_access_id', $access->getKey())
            ->where('content_type', $contentType)
            ->where('content_id', $contentId)
            ->where('file_id', $fileId)
            ->where('download_session_id', $sessionId)
            ->firstOrFail();

        $this->ensureSessionIsAlive($downloadSession->session_expires_at);

        if ($contentType === 'update') {
            $owner = LauncherUpdate::query()
                ->whereKey($contentId)
                ->where('product_sale_item_id', $access->product_sale_item_id)
                ->published()
                ->firstOrFail();

            $file = LauncherUpdateFile::query()
                ->whereKey($fileId)
                ->where('launcher_update_id', $owner->getKey())
                ->firstOrFail();

            return ['update', $file, (string)$file->url];
        }

        if ($contentType === 'mod') {
            $owner = LauncherMod::query()
                ->whereKey($contentId)
                ->where('product_sale_item_id', $access->product_sale_item_id)
                ->published()
                ->firstOrFail();

            $file = LauncherModFile::query()
                ->whereKey($fileId)
                ->where('launcher_mod_id', $owner->getKey())
                ->firstOrFail();

            return ['mod', $file, (string)$file->url];
        }

        abort(404);
    }

    private function ensureSessionIsAlive(mixed $expiresAt): void
    {
        abort_if($expiresAt === null || $expiresAt->isPast(), 410, 'Download session has expired.');
    }

    /**
     * @return array{start:int,end:int}|null
     */
    private function resolveRange(?string $rangeHeader, int $totalSize): ?array
    {
        if ($rangeHeader === null || trim($rangeHeader) === '') {
            return null;
        }

        $rangeHeader = trim($rangeHeader);

        if (
            str_contains($rangeHeader, ',')
            || preg_match('/^bytes=(\d*)-(\d*)$/i', $rangeHeader, $matches) !== 1
        ) {
            throw new HttpException(
                416,
                'Requested range is not satisfiable.',
                ['Content-Range' => "bytes */{$totalSize}", 'Accept-Ranges' => 'bytes'],
            );
        }

        $startValue = $matches[1];
        $endValue = $matches[2];

        if ($startValue === '' && $endValue === '') {
            throw new HttpException(
                416,
                'Requested range is not satisfiable.',
                ['Content-Range' => "bytes */{$totalSize}", 'Accept-Ranges' => 'bytes'],
            );
        }

        if ($startValue === '') {
            $suffixLength = (int)$endValue;

            if ($suffixLength <= 0) {
                throw new HttpException(
                    416,
                    'Requested range is not satisfiable.',
                    ['Content-Range' => "bytes */{$totalSize}", 'Accept-Ranges' => 'bytes'],
                );
            }

            return [
                'start' => max(0, $totalSize - $suffixLength),
                'end' => $totalSize - 1,
            ];
        }

        $start = (int)$startValue;

        if ($start >= $totalSize) {
            throw new HttpException(
                416,
                'Requested range is not satisfiable.',
                ['Content-Range' => "bytes */{$totalSize}", 'Accept-Ranges' => 'bytes'],
            );
        }

        $end = $endValue === ''
            ? $totalSize - 1
            : min((int)$endValue, $totalSize - 1);

        if ($end < $start) {
            throw new HttpException(
                416,
                'Requested range is not satisfiable.',
                ['Content-Range' => "bytes */{$totalSize}", 'Accept-Ranges' => 'bytes'],
            );
        }

        return ['start' => $start, 'end' => $end];
    }

    private function streamRemoteRange(string $url, int $start, int $end): void
    {
        @set_time_limit(0);

        if (!function_exists('curl_init')) {
            throw new PurchaseAccessFileUnavailableException();
        }

        $expectedLength = $end - $start + 1;
        $remoteStatus = 0;
        $remoteBodyStart = null;
        $remoteOffset = 0;
        $writtenBytes = 0;

        $handle = curl_init($url);

        if ($handle === false) {
            throw new PurchaseAccessFileUnavailableException();
        }

        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => max(
                1,
                (int)config('launcher.remote_download_connect_timeout_seconds', 15)
            ),
            CURLOPT_TIMEOUT => 0,
            CURLOPT_NOSIGNAL => true,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_RANGE => "{$start}-{$end}",
            CURLOPT_HTTPHEADER => [
                'Accept: application/octet-stream,*/*;q=0.8',
                'Accept-Encoding: identity',
            ],
            CURLOPT_HEADERFUNCTION => static function ($curl, string $header) use (&$remoteStatus, &$remoteBodyStart): int {
                $trimmed = trim($header);

                if (preg_match('#^HTTP/\S+\s+(\d{3})#i', $trimmed, $matches) === 1) {
                    $remoteStatus = (int)$matches[1];
                    $remoteBodyStart = null;
                } elseif (preg_match('/^Content-Range:\s*bytes\s+(\d+)-\d+\/\d+$/i', $trimmed, $matches) === 1) {
                    $remoteBodyStart = (int)$matches[1];
                }

                return strlen($header);
            },
            CURLOPT_WRITEFUNCTION => static function ($curl, string $chunk) use (
                &$remoteOffset,
                &$writtenBytes,
                &$remoteStatus,
                &$remoteBodyStart,
                $start,
                $end,
                $expectedLength,
            ): int {
                $chunkLength = strlen($chunk);

                if (!in_array($remoteStatus, [200, 206], true)) {
                    return 0;
                }

                if ($remoteStatus === 206 && $remoteBodyStart === null) {
                    return 0;
                }

                if ($writtenBytes >= $expectedLength) {
                    return 0;
                }

                /*
                 * A compliant origin answers the upstream Range request with
                 * 206 and its first body byte already maps to $start. If the
                 * origin ignores Range and answers 200, skip bytes locally so
                 * the client still receives a correct 206 byte range.
                 */
                $chunkAbsoluteStart = $remoteStatus === 206
                    ? $remoteBodyStart + $remoteOffset
                    : $remoteOffset;
                $chunkAbsoluteEnd = $chunkAbsoluteStart + $chunkLength - 1;

                $copyStart = max($start, $chunkAbsoluteStart);
                $copyEnd = min($end, $chunkAbsoluteEnd);

                if ($copyStart <= $copyEnd) {
                    $offsetInChunk = $copyStart - $chunkAbsoluteStart;
                    $copyLength = min(
                        $copyEnd - $copyStart + 1,
                        $expectedLength - $writtenBytes,
                    );

                    echo substr($chunk, $offsetInChunk, $copyLength);
                    $writtenBytes += $copyLength;

                    if (function_exists('ob_flush')) {
                        @ob_flush();
                    }
                    flush();
                }

                $remoteOffset += $chunkLength;

                if (connection_aborted()) {
                    return 0;
                }

                return $chunkLength;
            },
        ]);

        $result = curl_exec($handle);
        $curlError = curl_error($handle);
        curl_close($handle);

        /*
         * Returning 0 from the write callback after all requested bytes have
         * been written intentionally aborts an origin that ignored Range.
         */
        if ($writtenBytes < $expectedLength && ($result === false || $curlError !== '')) {
            report(new \RuntimeException('Launcher remote download stream ended before the requested range was complete.'));
        }
    }

    private function resolveFileName(Model $file, string $url): string
    {
        $explicit = trim((string)($file->getAttribute('file_name') ?? ''));

        if ($explicit !== '') {
            return $explicit;
        }

        $path = (string)parse_url($url, PHP_URL_PATH);
        $fromUrl = basename(rawurldecode($path));

        if ($fromUrl !== '' && $fromUrl !== '/' && $fromUrl !== '.') {
            return $fromUrl;
        }

        $title = trim((string)($file->getAttribute('title') ?? ''));

        return $title !== '' ? $title : 'download.bin';
    }

    private function contentDisposition(string $fileName): string
    {
        $fallback = preg_replace('/[^A-Za-z0-9._-]/', '_', $fileName) ?: 'download.bin';
        $fallback = str_replace(['\\', '"'], '_', $fallback);

        return sprintf(
            'attachment; filename="%s"; filename*=UTF-8\'\'%s',
            $fallback,
            rawurlencode($fileName),
        );
    }
}
