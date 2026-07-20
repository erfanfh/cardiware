<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_sale_items', function (Blueprint $table): void {
            $table->string('title')->nullable()->after('sku');
            $table->string('edition_key', 80)->nullable()->after('title');
            $table->string('version', 50)->nullable()->after('edition_key');
            $table->unsignedBigInteger('manifest_revision')->default(0)->after('version');

            $table->index(['product_id', 'edition_key'], 'psi_product_edition_index');
        });
    }

    public function down(): void
    {
        Schema::table('product_sale_items', function (Blueprint $table): void {
            $table->dropIndex('psi_product_edition_index');

            $table->dropColumn(['title', 'edition_key', 'version', 'manifest_revision']);
        });
    }
};
