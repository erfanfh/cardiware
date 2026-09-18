<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ticket_message_attachments')) {
            Schema::create('ticket_message_attachments', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('ticket_message_id')
                    ->constrained('ticket_messages')
                    ->cascadeOnDelete();
                $table->foreignId('uploaded_by_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
                $table->string('original_name', 255)->nullable();
                $table->unsignedTinyInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['ticket_message_id', 'sort_order']);
                $table->index('created_at');
            });
        }

        // MySQL may leave this table behind when adding a foreign key fails.
        // This migration is still pending in that state, so rebuilding it is safe.
        Schema::dropIfExists('ticket_message_attachment_variants');

        Schema::create('ticket_message_attachment_variants', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('ticket_message_attachment_id');
            $table->string('variant', 32);
            $table->string('mime_type', 64)->default('image/webp');
            $table->string('path', 500)->unique();
            $table->unsignedBigInteger('byte_size');
            $table->unsignedMediumInteger('width');
            $table->unsignedMediumInteger('height');
            $table->string('checksum', 64);
            $table->timestamps();

            $table->unique(
                ['ticket_message_attachment_id', 'variant'],
                'ticket_attachment_variant_unique',
            );
            $table->foreign(
                'ticket_message_attachment_id',
                'ticket_attachment_variant_attachment_fk',
            )
                ->references('id')
                ->on('ticket_message_attachments')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_message_attachment_variants');
        Schema::dropIfExists('ticket_message_attachments');
    }
};
