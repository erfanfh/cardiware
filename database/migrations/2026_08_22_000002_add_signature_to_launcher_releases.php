<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('launcher_releases', function (Blueprint $table): void {
            $table->text('signature')->nullable()->after('sha256');
            $table->char('signature_key_id', 64)->nullable()->after('signature');
        });
    }

    public function down(): void
    {
        Schema::table('launcher_releases', function (Blueprint $table): void {
            $table->dropColumn(['signature', 'signature_key_id']);
        });
    }
};
