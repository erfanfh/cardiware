<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Device re-activation after a Windows reinstall (launcher 1.21.118+).
 *
 * Everything the classic device identity is built from (device.json, the DPAPI device key,
 * MachineGuid) is recreated by a Windows reinstall, so the SAME PC arrived as a brand-new
 * device and consumed the account's one-time transfer. The launcher now also sends
 * `hardware_fingerprint_hash` -- SHA-256 over SMBIOS identifiers (system UUID, board serial)
 * plus the CPU model, which survive a reinstall. When an unknown device id carries the same
 * hardware hash as the account's ACTIVE slot, AuthController re-binds that slot to the new
 * key instead of treating it as a transfer. See docs/DEVICE-REACTIVATION-AFTER-WINDOWS-REINSTALL-FA.md
 * in the launcher repository.
 *
 * `launcher_device_reactivations` records every such re-bind: it drives the per-account
 * rate limit (config launcher.device_reactivation_limit / _window_days) and gives support a
 * trail when an account is suspected of being shared.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->char('first_device_hardware_fingerprint', 64)->nullable()->after('first_device_fingerprint');
            $table->char('second_device_hardware_fingerprint', 64)->nullable()->after('second_device_fingerprint');
        });

        Schema::table('launcher_devices', function (Blueprint $table): void {
            $table->char('hardware_fingerprint_hash', 64)->nullable()->after('fingerprint_hash')->index();
        });

        Schema::create('launcher_device_reactivations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('device_slot');
            $table->char('previous_public_key_fingerprint', 64)->nullable();
            $table->char('new_public_key_fingerprint', 64);
            $table->char('hardware_fingerprint_hash', 64);
            $table->string('device_name', 255)->nullable();
            $table->string('app_version', 32)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('launcher_device_reactivations');

        Schema::table('launcher_devices', function (Blueprint $table): void {
            $table->dropIndex(['hardware_fingerprint_hash']);
            $table->dropColumn('hardware_fingerprint_hash');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['first_device_hardware_fingerprint', 'second_device_hardware_fingerprint']);
        });
    }
};
