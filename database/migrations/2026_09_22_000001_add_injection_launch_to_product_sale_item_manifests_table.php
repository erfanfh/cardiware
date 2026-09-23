<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_sale_item_manifests', function (Blueprint $table): void {
            // Parallels requires_sider / sider_executable_path below, for products
            // whose "helper process" is a DLL injected into the game itself instead of
            // a side-loaded companion exe (e.g. a FIFA-style live editor). Mutually
            // independent from requires_sider on purpose -- a future product could
            // theoretically want both -- but no current product sets more than one.
            $table->boolean('requires_injection')
                ->default(false)
                ->after('run_sider_as_administrator');

            // Relative to the installed product root, same convention as
            // main_executable_path / sider_executable_path. The launcher starts
            // main_executable_path suspended and loads this DLL into it before
            // resuming, rather than launching a separate helper process.
            $table->string('inject_dll_path', 500)
                ->nullable()
                ->after('requires_injection');
        });
    }

    public function down(): void
    {
        Schema::table('product_sale_item_manifests', function (Blueprint $table): void {
            $table->dropColumn([
                'requires_injection',
                'inject_dll_path',
            ]);
        });
    }
};
