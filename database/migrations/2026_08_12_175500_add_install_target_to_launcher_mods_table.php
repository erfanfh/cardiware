<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('launcher_mods', function (Blueprint $table): void {
            $table->string('install_target', 40)
                ->default('product_root')
                ->after('version');
        });
    }

    public function down(): void
    {
        Schema::table('launcher_mods', function (Blueprint $table): void {
            $table->dropColumn('install_target');
        });
    }
};
