<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_events', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
            $table->string('subject_id', 64)->change();
            $table->string('actor_name')->nullable()->after('user_id');
            $table->string('subject_label', 160)->nullable()->after('subject_type');
            $table->index(['created_at', 'id'], 'audit_events_created_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('audit_events', function (Blueprint $table): void {
            $table->dropColumn('actor_name');
            $table->dropColumn('subject_label');
            $table->dropIndex('audit_events_created_id_index');
            $table->unsignedBigInteger('subject_id')->change();
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
};
