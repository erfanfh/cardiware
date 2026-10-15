<?php

use App\Domain\Catalog\Models\ProductSaleItemManifest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Game-gate (FIFA / FC) columns on the sale-item manifest.
 *
 *  - content_gate         : محصول از «نگهبانِ MW» استفاده می‌کند (MainExecutablePath = Launcher.exe
 *                           نگهبان؛ لانچرِ واقعیِ Live Editor به LELaunch.exe تغییرِ نام می‌دهد).
 *  - protected_gate_files : مسیرهای نسبی‌ای که نگهبان موقعِ اجرا رمزگشایی می‌کند (<path>.enc -> <path>).
 *
 * نامِ جدول از خودِ مدل گرفته می‌شود تا با هر نام‌گذاری‌ای درست کار کند. هر دو ستون کنارِ
 * ستون‌های موجودِ content_key_id / protected_excluded_paths می‌نشینند و idempotent هستند.
 */
return new class extends Migration {
    private function manifestTable(): string
    {
        return (new ProductSaleItemManifest())->getTable();
    }

    public function up(): void
    {
        $table = $this->manifestTable();

        Schema::table($table, function (Blueprint $blueprint) use ($table): void {
            if (!Schema::hasColumn($table, 'content_gate')) {
                $blueprint->boolean('content_gate')->default(false)->after('content_key_id');
            }
            if (!Schema::hasColumn($table, 'protected_gate_files')) {
                // Same storage shape as protected_exact_paths / protected_excluded_paths
                // (an array cast on the model). If those columns are `text` in your schema,
                // change `json` to `text` here to match -- the array cast works with both.
                $blueprint->json('protected_gate_files')->nullable()->after('protected_excluded_paths');
            }
        });
    }

    public function down(): void
    {
        $table = $this->manifestTable();

        Schema::table($table, function (Blueprint $blueprint) use ($table): void {
            foreach (['content_gate', 'protected_gate_files'] as $column) {
                if (Schema::hasColumn($table, $column)) {
                    $blueprint->dropColumn($column);
                }
            }
        });
    }
};
