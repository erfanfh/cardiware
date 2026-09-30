<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Native-DLL enforcement for protected products.
 *
 * `protected_enforce_native_dlls` (per product, default OFF): when on, the launcher refuses
 * to start the game if a modules\*.dll is missing from / differs from the product's
 * modules.json list, or a DLL/ASI next to the game exe is not in launcher_allowed_dlls.
 * Off by default because turning it on before the allowlist holds the DLLs real installs
 * carry (ReShade, the patch's hook loader, ...) would stop working games.
 *
 * `launcher_allowed_dlls` is ONE global list, filled from the «رصد امنیتی لانچر» page
 * with a click on hashes already seen on customers' machines.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_sale_item_manifests', function (Blueprint $table): void {
            $table->boolean('protected_enforce_native_dlls')->default(false)->after('protected_strict_modules');
        });

        Schema::create('launcher_allowed_dlls', function (Blueprint $table): void {
            $table->id();
            $table->char('sha256', 64)->unique();
            $table->string('file_name', 255)->nullable();
            $table->string('note', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('launcher_allowed_dlls');
        Schema::table('product_sale_item_manifests', function (Blueprint $table): void {
            $table->dropColumn('protected_enforce_native_dlls');
        });
    }
};
