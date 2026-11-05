<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * لندینگ‌ساز: هر صفحه = تم (رنگ‌ها، حالت تیره/روشن، پس‌زمینه) + سئو + فهرست بلوک‌ها.
 * آدرس عمومی: /landing/{slug}
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 190);
            $table->string('slug', 190)->unique();
            $table->string('status', 20)->default('draft')->index();
            $table->json('theme')->nullable();
            $table->json('blocks')->nullable();
            $table->json('seo')->nullable();
            // بازه‌ی نمایش اختیاری (کمپین/جشنواره)؛ بیرون از بازه ۴۰۴ می‌دهد.
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedBigInteger('views_count')->default(0);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_pages');
    }
};
