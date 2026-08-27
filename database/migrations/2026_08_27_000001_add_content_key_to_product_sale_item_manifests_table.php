<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_sale_item_manifests', function (Blueprint $table): void {
            // Opaque, versioned identifier (e.g. "pgl6-db-2026-01") -- NOT the product
            // slug. Looked up as-is by the launcher against the offline license's
            // content_keys map. NOT database-unique on purpose: two sale item variants
            // of the same product (e.g. PGL6's half-season and full-season editions)
            // legitimately ship the identical database until their update paths
            // diverge, and should be able to share one id + key rather than the
            // database being encrypted twice for no reason. UpdateProductRequest
            // enforces the real invariant instead: a shared id is only allowed when
            // every manifest using it has the EXACT SAME key value.
            $table->string('content_key_id', 128)
                ->nullable()
                ->index()
                ->after('run_sider_as_administrator');

            // The raw AES-256 key, Base64-encoded (exactly 32 bytes once decoded --
            // enforced in UpdateProductRequest, not here). Encrypted at rest with the
            // same 'encrypted' cast already used for archive_password on this table.
            $table->text('content_key_value')
                ->nullable()
                ->after('content_key_id');
        });
    }

    public function down(): void
    {
        Schema::table('product_sale_item_manifests', function (Blueprint $table): void {
            $table->dropColumn([
                'content_key_id',
                'content_key_value',
            ]);
        });
    }
};
