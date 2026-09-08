<?php

namespace Tests\Feature\Support;

use App\Application\Support\Actions\CleanupExpiredTicketAttachmentsAction;
use App\Application\Support\Actions\DeleteTicketMessageAttachmentAction;
use App\Application\Support\Actions\StoreTicketMessageAttachmentsAction;
use App\Domain\Identity\Models\User;
use App\Domain\Support\Enums\TicketAttachmentVariantEnum;
use App\Domain\Support\Enums\TicketMessageSenderTypeEnum;
use App\Domain\Support\Models\TicketMessage;
use App\Domain\Support\Models\TicketMessageAttachment;
use App\Infrastructure\Services\Setting\Groups\TicketSettings;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TicketAttachmentLifecycleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
        });
        Schema::create('tickets', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
        });
        Schema::create('ticket_messages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('sender_type');
            $table->longText('body');
            $table->timestamps();
        });
        Schema::create('ticket_message_attachments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('ticket_message_id');
            $table->unsignedBigInteger('uploaded_by_user_id')->nullable();
            $table->string('original_name')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
        Schema::create('ticket_message_attachment_variants', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('ticket_message_attachment_id');
            $table->string('variant');
            $table->string('mime_type');
            $table->string('path');
            $table->unsignedBigInteger('byte_size');
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->string('checksum');
            $table->timestamps();
        });

        DB::table('users')->insert(['id' => 9]);
        DB::table('tickets')->insert(['id' => 1, 'user_id' => 9]);
    }

    public function test_uploaded_image_uses_dedicated_tables_and_private_ticket_directory(): void
    {
        Storage::fake('local');
        $message = $this->createMessage(1, TicketMessageSenderTypeEnum::USER);
        $uploader = new User;
        $uploader->id = 9;

        $attachments = app(StoreTicketMessageAttachmentsAction::class)->execute(
            message: $message,
            files: [UploadedFile::fake()->image('proof.png', 320, 240)],
            uploader: $uploader,
        );

        self::assertCount(1, $attachments);
        self::assertFalse(Schema::hasTable('media_files'));
        self::assertDatabaseHas('ticket_message_attachments', [
            'ticket_message_id' => $message->id,
            'uploaded_by_user_id' => 9,
            'original_name' => 'proof.png',
        ]);

        $attachment = $attachments[0]->fresh('variants');
        self::assertCount(5, $attachment->variants);

        foreach ($attachment->variants as $variant) {
            self::assertSame('image/webp', $variant->mime_type);
            self::assertStringStartsWith('support/ticket-images/', $variant->path);
            self::assertStringEndsWith("/{$variant->variant->value}.webp", $variant->path);
            Storage::disk('local')->assertExists($variant->path);
        }
    }

    public function test_reencoding_strips_content_appended_to_an_otherwise_valid_image(): void
    {
        Storage::fake('local');
        $message = $this->createMessage(1, TicketMessageSenderTypeEnum::USER);
        $uploader = new User;
        $uploader->id = 9;
        $cleanImage = UploadedFile::fake()->image('clean.png', 100, 100);
        $binary = file_get_contents($cleanImage->getRealPath());
        self::assertIsString($binary);

        $suspiciousImage = UploadedFile::fake()->createWithContent(
            'suspicious.png',
            $binary.'<?php echo "unsafe";',
        );

        $attachments = app(StoreTicketMessageAttachmentsAction::class)->execute(
            message: $message,
            files: [$suspiciousImage],
            uploader: $uploader,
        );

        foreach ($attachments[0]->fresh('variants')->variants as $variant) {
            $storedBinary = Storage::disk('local')->get($variant->path);

            self::assertStringNotContainsString('<?php', $storedBinary);
            self::assertSame('image/webp', (new \finfo(FILEINFO_MIME_TYPE))->buffer($storedBinary));
        }
    }

    public function test_retention_cleanup_deletes_only_expired_user_images(): void
    {
        Storage::fake('local');

        $oldUserMessage = $this->createMessage(1, TicketMessageSenderTypeEnum::USER);
        $oldSupportMessage = $this->createMessage(2, TicketMessageSenderTypeEnum::SUPPORT);
        $recentUserMessage = $this->createMessage(3, TicketMessageSenderTypeEnum::USER);

        $expiredUserAttachment = $this->createAttachment($oldUserMessage, 'expired-user', now()->subDays(91));
        $supportAttachment = $this->createAttachment($oldSupportMessage, 'support', now()->subDays(91));
        $recentAttachment = $this->createAttachment($recentUserMessage, 'recent', now()->subDays(20));

        $settings = new class extends TicketSettings
        {
            public function attachmentRetentionDays(): int
            {
                return 90;
            }
        };

        $deleted = (new CleanupExpiredTicketAttachmentsAction(
            $settings,
            app(DeleteTicketMessageAttachmentAction::class),
        ))->execute();

        self::assertSame(1, $deleted);
        self::assertDatabaseMissing('ticket_message_attachments', ['id' => $expiredUserAttachment->id]);
        self::assertDatabaseMissing('ticket_message_attachment_variants', [
            'ticket_message_attachment_id' => $expiredUserAttachment->id,
        ]);
        self::assertDatabaseHas('ticket_message_attachments', ['id' => $supportAttachment->id]);
        self::assertDatabaseHas('ticket_message_attachments', ['id' => $recentAttachment->id]);
        Storage::disk('local')->assertMissing($this->path('expired-user'));
        Storage::disk('local')->assertExists($this->path('support'));
        Storage::disk('local')->assertExists($this->path('recent'));
    }

    private function createMessage(int $id, TicketMessageSenderTypeEnum $senderType): TicketMessage
    {
        DB::table('ticket_messages')->insert([
            'id' => $id,
            'ticket_id' => 1,
            'user_id' => 9,
            'sender_type' => $senderType->value,
            'body' => 'Test message',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return TicketMessage::query()->findOrFail($id);
    }

    private function createAttachment(
        TicketMessage $message,
        string $name,
        \DateTimeInterface $createdAt,
    ): TicketMessageAttachment {
        $path = $this->path($name);
        Storage::disk('local')->put($path, 'private-webp');

        $attachment = $message->attachments()->create([
            'uploaded_by_user_id' => 9,
            'original_name' => "{$name}.webp",
        ]);
        $attachment->variants()->create([
            'variant' => TicketAttachmentVariantEnum::ORIGINAL,
            'mime_type' => 'image/webp',
            'path' => $path,
            'byte_size' => 12,
            'width' => 10,
            'height' => 10,
            'checksum' => hash('sha256', 'private-webp'),
        ]);
        $attachment->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->save();

        return $attachment;
    }

    private function path(string $name): string
    {
        $uuid = match ($name) {
            'expired-user' => '11111111-1111-4111-8111-111111111111',
            'support' => '22222222-2222-4222-8222-222222222222',
            'recent' => '33333333-3333-4333-8333-333333333333',
        };

        return "support/ticket-images/2026/09/{$uuid}/original.webp";
    }
}
