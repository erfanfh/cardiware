<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_sale_item_manifests', function (Blueprint $table): void {
            $table->text('archive_password')->nullable()->after('archive_format');
        });

        Schema::table('launcher_updates', function (Blueprint $table): void {
            $table->text('archive_password')->nullable()->after('archive_format');
        });

        Schema::table('launcher_mods', function (Blueprint $table): void {
            $table->text('archive_password')->nullable()->after('version');
        });
    }

    public function down(): void
    {
        Schema::table('launcher_mods', function (Blueprint $table): void {
            $table->dropColumn('archive_password');
        });

        Schema::table('launcher_updates', function (Blueprint $table): void {
            $table->dropColumn('archive_password');
        });

        Schema::table('product_sale_item_manifests', function (Blueprint $table): void {
            $table->dropColumn('archive_password');
        });
    }
};
