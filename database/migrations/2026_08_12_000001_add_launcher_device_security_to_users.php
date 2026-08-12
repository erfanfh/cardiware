<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->text('first_device_public_key')->nullable()->after('remember_token');
            $table->char('first_device_fingerprint', 64)->nullable()->after('first_device_public_key');
            $table->text('second_device_public_key')->nullable()->after('first_device_fingerprint');
            $table->char('second_device_fingerprint', 64)->nullable()->after('second_device_public_key');
            $table->unsignedTinyInteger('active_device_slot')->nullable()->after('second_device_fingerprint');
            $table->boolean('device_transfer_used')->default(false)->after('active_device_slot');
            $table->timestamp('first_activated_at')->nullable()->after('device_transfer_used');
            $table->timestamp('transferred_at')->nullable()->after('first_activated_at');
        });

        Schema::table('launcher_devices', function (Blueprint $table): void {
            $table->text('public_key')->nullable()->after('fingerprint_hash');
            $table->char('public_key_fingerprint', 64)->nullable()->after('public_key');
            $table->unsignedTinyInteger('device_slot')->nullable()->after('public_key_fingerprint');
            $table->timestamp('offline_license_expires_at')->nullable()->after('device_slot');
        });
    }

    public function down(): void
    {
        Schema::table('launcher_devices', function (Blueprint $table): void {
            $table->dropColumn([
                'public_key',
                'public_key_fingerprint',
                'device_slot',
                'offline_license_expires_at',
            ]);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'first_device_public_key',
                'first_device_fingerprint',
                'second_device_public_key',
                'second_device_fingerprint',
                'active_device_slot',
                'device_transfer_used',
                'first_activated_at',
                'transferred_at',
            ]);
        });
    }
};
