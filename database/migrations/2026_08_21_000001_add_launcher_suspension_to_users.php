<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('launcher_suspended_at')->nullable()->after('transferred_at');
            $table->timestamp('launcher_suspended_until')->nullable()->after('launcher_suspended_at');
            $table->text('launcher_suspension_reason')->nullable()->after('launcher_suspended_until');
            $table->foreignId('launcher_suspended_by')
                ->nullable()
                ->after('launcher_suspension_reason')
                ->constrained('users')
                ->nullOnDelete();

            $table->index('launcher_suspended_until');
        });

        Schema::create('launcher_access_suspension_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('target_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 20);
            $table->text('reason')->nullable();
            $table->timestamp('suspended_until')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['target_user_id', 'created_at'], 'launcher_suspension_user_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('launcher_access_suspension_logs');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['launcher_suspended_by']);
            $table->dropIndex(['launcher_suspended_until']);
            $table->dropColumn([
                'launcher_suspended_at',
                'launcher_suspended_until',
                'launcher_suspension_reason',
                'launcher_suspended_by',
            ]);
        });
    }
};
