<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('launcher_device_transfer_blocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason', 40);
            $table->string('attempted_device_id', 100)->nullable();
            $table->string('attempted_device_name', 150)->nullable();
            $table->string('attempted_platform', 30)->nullable();
            $table->string('attempted_public_key_fingerprint', 64)->nullable();
            $table->string('attempted_fingerprint_hash', 64)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('launcher_device_transfer_blocks');
    }
};
