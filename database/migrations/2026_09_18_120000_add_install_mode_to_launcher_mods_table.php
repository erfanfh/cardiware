<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('launcher_mods', function (Blueprint $table): void {
            // toggle    = classic enable/disable package (enable/ + disable/ trees, cached in _MWMODS)
            // permanent = install-once pack (enable/ only, moved into the game folder, no disable)
            $table->string('install_mode', 20)
                ->default('toggle')
                ->after('install_target');
        });
    }

    public function down(): void
    {
        Schema::table('launcher_mods', function (Blueprint $table): void {
            $table->dropColumn('install_mode');
        });
    }
};
