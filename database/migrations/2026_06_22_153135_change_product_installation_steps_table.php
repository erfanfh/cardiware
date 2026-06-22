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
        Schema::table('product_installation_steps', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropColumn('value');
            $table->longText('body')->after('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_installation_steps', function (Blueprint $table) {
            $table->dropColumn('body');
            $table->string('title')->after('product_id');
            $table->text('value')->after('title');
        });
    }
};
