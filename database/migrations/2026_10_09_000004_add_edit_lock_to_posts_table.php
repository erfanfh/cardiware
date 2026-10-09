<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->foreignId('edit_lock_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('edit_lock_token', 64)->nullable();
            $table->timestamp('edit_lock_expires_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('edit_lock_user_id');
            $table->dropColumn(['edit_lock_token', 'edit_lock_expires_at']);
        });
    }
};
