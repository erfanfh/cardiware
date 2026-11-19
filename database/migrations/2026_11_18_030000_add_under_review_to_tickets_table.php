<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * حالت «در حال بررسی» تیکت: علامت‌گذاری توسط پشتیبان برای پیگیری بعدی.
 * فلگ مستقل است تا وضعیت اصلی تیکت (باز/پاسخ‌داده) حفظ شود.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->boolean('is_under_review')->default(false)->after('status');
            $table->timestamp('under_review_marked_at')->nullable()->after('is_under_review');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropColumn(['is_under_review', 'under_review_marked_at']);
        });
    }
};
