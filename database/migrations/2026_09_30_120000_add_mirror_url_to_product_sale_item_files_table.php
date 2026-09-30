<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional mirror of each product part on a SEPARATE download host, handed to users who
 * prefer an external download manager (IDM). Kept apart from `url` on purpose: links
 * users copy out of the launcher get shared, and that traffic must never land on the
 * launcher's own host. The file behind it must be byte-identical (same name, size and
 * SHA-256) so the launcher can verify a manually downloaded part like any other.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_sale_item_files', function (Blueprint $table): void {
            $table->string('mirror_url', 2048)->nullable()->after('url');
        });
    }

    public function down(): void
    {
        Schema::table('product_sale_item_files', function (Blueprint $table): void {
            $table->dropColumn('mirror_url');
        });
    }
};
