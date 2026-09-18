<?php

namespace Tests\Feature\Support;

use App\Domain\Identity\Models\User;
use App\Domain\Support\Enums\TicketAttachmentVariantEnum;
use App\Domain\Support\Models\Ticket;
use App\Domain\Support\Models\TicketMessage;
use App\Domain\Support\Models\TicketMessageAttachment;
use App\Domain\Support\Models\TicketMessageAttachmentVariant;
use App\Http\Controllers\Front\Support\TicketAttachmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class TicketAttachmentControllerTest extends TestCase
{
    public function test_ticket_owner_can_stream_private_webp_with_security_headers(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put($this->securePath(), 'safe-image');

        [$attachment, $owner] = $this->attachmentGraph(ownerId: 10);
        $request = $this->requestFor($owner);

        $response = app(TicketAttachmentController::class)(
            $request,
            $attachment,
            TicketAttachmentVariantEnum::ORIGINAL->value,
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('image/webp', $response->headers->get('Content-Type'));
        self::assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        self::assertSame('same-origin', $response->headers->get('Cross-Origin-Resource-Policy'));
        self::assertSame('no-referrer', $response->headers->get('Referrer-Policy'));
        self::assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    public function test_unrelated_user_receives_not_found_instead_of_private_image(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put($this->securePath(), 'safe-image');

        [$attachment] = $this->attachmentGraph(ownerId: 10);
        $unrelatedUser = $this->user(20);

        Gate::shouldReceive('forUser')
            ->once()
            ->with($unrelatedUser)
            ->andReturnSelf();
        Gate::shouldReceive('allows')
            ->once()
            ->with('admin.display')
            ->andReturnFalse();

        $this->expectException(NotFoundHttpException::class);

        app(TicketAttachmentController::class)(
            $this->requestFor($unrelatedUser),
            $attachment,
            TicketAttachmentVariantEnum::ORIGINAL->value,
        );
    }

    public function test_authorized_admin_can_stream_ticket_image(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put($this->securePath(), 'safe-image');

        [$attachment] = $this->attachmentGraph(ownerId: 10);
        $admin = $this->user(30);

        Gate::shouldReceive('forUser')
            ->twice()
            ->with($admin)
            ->andReturnSelf();
        Gate::shouldReceive('allows')
            ->once()
            ->with('admin.display')
            ->andReturnTrue();
        Gate::shouldReceive('allows')
            ->once()
            ->with('ticket.show')
            ->andReturnTrue();

        $response = app(TicketAttachmentController::class)(
            $this->requestFor($admin),
            $attachment,
            TicketAttachmentVariantEnum::ORIGINAL->value,
        );

        self::assertSame(200, $response->getStatusCode());
    }

    /**
     * @return array{TicketMessageAttachment, User}
     */
    private function attachmentGraph(int $ownerId): array
    {
        $owner = $this->user($ownerId);

        $ticket = new Ticket(['user_id' => $ownerId]);
        $ticket->id = 100;

        $message = new TicketMessage;
        $message->id = 200;
        $message->setRelation('ticket', $ticket);

        $variant = new TicketMessageAttachmentVariant([
            'mime_type' => 'image/webp',
            'variant' => TicketAttachmentVariantEnum::ORIGINAL,
            'path' => $this->securePath(),
        ]);
        $variant->id = 400;

        $attachment = new TicketMessageAttachment;
        $attachment->id = 500;
        $attachment->setRelation('message', $message);
        $attachment->setRelation('variants', collect([$variant]));

        return [$attachment, $owner];
    }

    private function user(int $id): User
    {
        $user = new User([
            'username' => 'user-'.Str::random(8),
            'display_name' => 'Ticket user',
        ]);
        $user->id = $id;

        return $user;
    }

    private function requestFor(User $user): Request
    {
        $request = Request::create('/support/ticket-images/500/original');
        $request->setUserResolver(fn (): User => $user);

        return $request;
    }

    private function securePath(): string
    {
        return 'support/ticket-images/2026/09/11111111-1111-4111-8111-111111111111/original.webp';
    }
}
