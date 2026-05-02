<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('page_views', function (Blueprint $table) {
            $table->unsignedBigInteger('count')->default(0)->change();
        });

        Schema::table('page_view_details', function (Blueprint $table) {
            $table->unsignedBigInteger('count')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('page_views', function (Blueprint $table) {
            $table->unsignedBigInteger('count')->default(null)->change();
        });

        Schema::table('page_view_details', function (Blueprint $table) {
            $table->unsignedBigInteger('count')->default(null)->change();
        });
    }
};
