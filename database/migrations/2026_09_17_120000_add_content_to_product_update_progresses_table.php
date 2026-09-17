<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private const DEFAULT_TITLE = 'درصد پیشرفت آپدیت محصول';

    private const DEFAULT_DESCRIPTION = 'پیشرفت تا زمان انتشار آپدیت بعدی';

    public function up(): void
    {
        Schema::table('product_update_progresses', function (Blueprint $table) {
            $table->string('title')->nullable()->after('is_visible');
            $table->text('description')->nullable()->after('title');
        });

        DB::table('product_update_progresses')->update([
            'title' => self::DEFAULT_TITLE,
            'description' => self::DEFAULT_DESCRIPTION,
        ]);
    }

    public function down(): void
    {
        Schema::table('product_update_progresses', function (Blueprint $table) {
            $table->dropColumn(['title', 'description']);
        });
    }
};
