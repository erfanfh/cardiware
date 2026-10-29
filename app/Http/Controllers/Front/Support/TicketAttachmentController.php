<?php

namespace App\Http\Controllers\Front\Support;

use App\Domain\Catalog\Scopes\ActiveProductScope;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Support\Enums\TicketAttachmentVariantEnum;
use App\Domain\Support\Models\TicketMessageAttachment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketAttachmentController extends Controller
{
    private const STORAGE_PATH_PATTERN = '#\Asupport/ticket-images/\d{4}/\d{2}/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/(?:original|thumbnail|medium|medium_large|large)\.webp\z#D';

    public function __invoke(
        Request $request,
        TicketMessageAttachment $ticketMessageAttachment,
        string $variant,
    ): StreamedResponse {
        $ticketMessageAttachment->loadMissing([
            'message.ticket',
            'variants',
        ]);

        $user = $request->user();
        abort_unless($user, 404);

        $ticket = $ticketMessageAttachment->message?->ticket;
        $isOwner = $ticket && (int) $ticket->user_id === (int) $user->getKey();
        $isAuthorizedAdmin = ! $isOwner
            && Gate::forUser($user)->allows('admin.display')
            && Gate::forUser($user)->allows('ticket.show');
        $sellerStore = ! $isOwner && ! $isAuthorizedAdmin && $ticket?->product_id
            ? Store::query()
                ->whereHas('sellers', fn ($query) => $query->whereKey($user->getKey()))
                ->whereHas('products', fn ($query) => $query->withoutGlobalScope(ActiveProductScope::class)->whereKey($ticket->product_id))
                ->first()
            : null;
        $isAssignedSeller = $sellerStore?->sellerCan('view_tickets') ?? false;

        abort_unless($ticket && ($isOwner || $isAuthorizedAdmin || $isAssignedSeller), 404);

        $variantEnum = TicketAttachmentVariantEnum::tryFrom($variant);
        abort_unless($variantEnum, 404);

        $imageVariant = $ticketMessageAttachment->variants->firstWhere('variant', $variantEnum);

        abort_unless(
            $imageVariant
            && $imageVariant->mime_type === 'image/webp'
            && preg_match(self::STORAGE_PATH_PATTERN, $imageVariant->path) === 1,
            404,
        );

        $storage = Storage::disk('local');
        abort_unless($storage->exists($imageVariant->path), 404);

        $filename = "ticket-image-{$ticketMessageAttachment->getKey()}.webp";

        return $storage->response($imageVariant->path, $filename, [
            'Content-Type' => 'image/webp',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
            'Cache-Control' => 'private, no-store, max-age=0',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
            'Cross-Origin-Resource-Policy' => 'same-origin',
            'Referrer-Policy' => 'no-referrer',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
