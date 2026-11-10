<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** دنبال‌کردن اعضای باشگاه (کارت عمومی پروفایل) — هر کاربر هر عضو را یک‌بار. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_follows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('follower_id')->index();
            $table->unsignedBigInteger('target_id')->index();
            $table->timestamps();

            $table->unique(['follower_id', 'target_id']);

            $table->foreign('follower_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('target_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_follows');
    }
};
