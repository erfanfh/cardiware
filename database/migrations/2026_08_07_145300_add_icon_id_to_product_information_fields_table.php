<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_information_fields', function (Blueprint $table) {
            $table->foreignId('icon_id')
                ->nullable()
                ->after('title')
                ->constrained('icons')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_information_fields', function (Blueprint $table) {
            $table->dropConstrainedForeignId('icon_id');
        });
    }
};
