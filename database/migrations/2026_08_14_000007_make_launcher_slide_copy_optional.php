<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('launcher_slides', function (Blueprint $table): void {
            $table->string('title', 255)->nullable()->change();
            $table->string('button_text', 80)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        DB::table('launcher_slides')->whereNull('title')->update(['title' => 'اسلاید لانچر']);
        DB::table('launcher_slides')->whereNull('button_text')->update(['button_text' => 'مشاهده']);
        Schema::table('launcher_slides', function (Blueprint $table): void {
            $table->string('title', 255)->nullable(false)->change();
            $table->string('button_text', 80)->nullable(false)->default('مشاهده')->change();
        });
    }
};
