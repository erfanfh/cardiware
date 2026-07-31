<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('mobile')->nullable()->change();

            $table->string('deleted_mobile')->nullable()->after('mobile');
            $table->string('deleted_email')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'deleted_mobile',
                'deleted_email',
            ]);

            $table->string('mobile')->nullable(false)->change();
        });
    }
};
