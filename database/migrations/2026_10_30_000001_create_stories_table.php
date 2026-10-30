<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stories', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 180);
            $table->string('subtitle')->nullable();
            $table->text('caption')->nullable();
            $table->foreignId('image_id')->nullable()->constrained('media_files')->nullOnDelete();
            $table->foreignId('thumbnail_id')->nullable()->constrained('media_files')->nullOnDelete();
            $table->string('link_url', 2048)->nullable();
            $table->string('link_label', 100)->nullable();
            $table->unsignedSmallInteger('duration_seconds')->default(15);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(false);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->timestamps();
            $table->index(['is_active', 'sort_order', 'id']);
        });

        foreach (['list' => 'لیست استوری‌ها', 'create' => 'ایجاد استوری', 'edit' => 'ویرایش استوری', 'delete' => 'حذف استوری'] as $ability => $label) {
            DB::table('permissions')->updateOrInsert(
                ['group' => 'story', 'ability' => $ability],
                ['label' => $label, 'created_at' => now(), 'updated_at' => now()],
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stories');
        DB::table('permissions')->where('group', 'story')->delete();
    }
};
