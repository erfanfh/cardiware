<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropUnique(['title']);
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->string('group')->after('id');
            $table->string('ability')->after('group');
            $table->string('label')->after('ability');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('title')->unique()->after('id');
            $table->dropColumn('group');
            $table->dropColumn('ability');
            $table->dropColumn('label');
        });
    }
};
