<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->boolean('is_floating_box_enabled')
                ->default(false)
                ->after('used_count')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropIndex(['is_floating_box_enabled']);
            $table->dropColumn('is_floating_box_enabled');
        });
    }
};
