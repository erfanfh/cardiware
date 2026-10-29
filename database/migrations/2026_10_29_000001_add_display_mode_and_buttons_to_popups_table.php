<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('popups', function (Blueprint $table) {
            $table->string('display_mode')->default('standard');
            $table->json('buttons')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('popups', function (Blueprint $table) {
            $table->dropColumn(['display_mode', 'buttons']);
        });
    }
};
