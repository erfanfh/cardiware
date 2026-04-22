<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasColumn('taxonomables', 'type')) {
            return;
        }

        Schema::table('taxonomables', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasColumn('taxonomables', 'type')) {
            return;
        }

        Schema::table('taxonomables', function (Blueprint $table) {
            $table->string('type', 50)->nullable()->after('taxonomy_term_id');
        });
    }
};
