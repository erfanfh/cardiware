<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-buyer watermarking of protected content (layer 2).
 *
 * The encrypted files are identical for every customer, so the only place the content can
 * differ per customer is after decryption, in the game's memory -- which is exactly what a
 * leaked database dump is made from. sider.dll overwrites a few admin-chosen bytes of
 * chosen protected files with the buyer's code as it decrypts them. A leaked dump then
 * carries the code of the account it came from.
 *
 * `protected_watermark_sites`: lines "<offset> <length> <path>", per product. Empty = off
 * (the default), in which case nothing is sent and sider behaves exactly as before.
 *
 * `launcher_watermarks`: one random 8-byte code per user, stored so a code found in a leak
 * can be looked up. Random rather than derived from the user id, so the code reveals
 * nothing and cannot be forged for someone else's account.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_sale_item_manifests', function (Blueprint $table): void {
            $table->json('protected_watermark_sites')->nullable()->after('protected_enforce_native_dlls');
        });

        Schema::create('launcher_watermarks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->char('code', 16)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('launcher_watermarks');
        Schema::table('product_sale_item_manifests', function (Blueprint $table): void {
            $table->dropColumn('protected_watermark_sites');
        });
    }
};
