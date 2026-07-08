<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('department_id')
                ->nullable()
                ->after('user_id')
                ->constrained('ticket_departments')
                ->restrictOnDelete();

            $table->foreignId('product_id')
                ->nullable()
                ->after('department_id')
                ->constrained('products')
                ->nullOnDelete();

            $table->index(['department_id', 'status']);
            $table->index(['product_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
            $table->dropConstrainedForeignId('department_id');
        });
    }
};
