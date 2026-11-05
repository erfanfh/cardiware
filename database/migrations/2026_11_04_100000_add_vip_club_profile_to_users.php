<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** پروفایل اختصاصی VIP در کارت باشگاه: معرفی کوتاه و تم رنگی. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'club_bio')) {
                $table->string('club_bio', 160)->nullable();
            }
            if (! Schema::hasColumn('users', 'club_theme')) {
                $table->string('club_theme', 20)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['club_bio', 'club_theme']);
        });
    }
};
