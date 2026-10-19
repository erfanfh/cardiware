<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_announcements', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 180);
            $table->text('body');
            $table->string('type', 20)->default('info');
            $table->boolean('is_active')->default(false);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->index(['is_active', 'starts_at', 'ends_at']);
        });

        DB::table('permissions')->updateOrInsert(
            ['group' => 'account-announcement', 'ability' => 'manage'],
            ['label' => 'مدیریت اطلاعیه‌های حساب کاربری', 'created_at' => now(), 'updated_at' => now()],
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('account_announcements');
        DB::table('permissions')->where('group', 'account-announcement')->where('ability', 'manage')->delete();
    }
};
