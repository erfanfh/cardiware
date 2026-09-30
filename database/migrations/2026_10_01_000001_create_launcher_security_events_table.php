<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tamper signals the launcher detects on a customer's machine (untrusted sider.dll, a
 * modified Sider module, an unexpected DLL next to the game, a debugger on the launcher),
 * reported with the account and device they came from.
 *
 * One row per distinct signal per device, not per occurrence: the launcher resends the
 * same finding on every launch, so rows are keyed by `fingerprint` and repeats only bump
 * `occurrences` / `last_seen_at`. That keeps the table small enough to query live from
 * the admin page and makes "how many times" a column instead of a COUNT(*).
 *
 * Severity is assigned by the SERVER from event_type (see RecordLauncherSecurityEventsAction),
 * never read from the request -- a client-chosen severity would let a tampered launcher
 * report everything as harmless.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('launcher_security_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('launcher_device_id')->nullable()
                ->constrained('launcher_devices')->nullOnDelete();

            $table->string('event_type', 48);
            $table->unsignedTinyInteger('severity');

            // What the signal is about, e.g. "dinput8.dll" or "lib\\CommonLib.lua". Short,
            // escaped on display, never a path from the user's disk beyond the game folder.
            $table->string('subject', 255)->nullable();
            $table->char('sha256', 64)->nullable();
            $table->string('app_version', 32)->nullable();

            $table->char('fingerprint', 64);
            $table->unsignedInteger('occurrences')->default(1);
            $table->timestamp('first_seen_at')->useCurrent();
            $table->timestamp('last_seen_at')->useCurrent();
            $table->string('last_ip', 45)->nullable();

            $table->unique(['user_id', 'fingerprint'], 'lse_user_fingerprint_unique');
            $table->index(['last_seen_at', 'severity'], 'lse_recent_severity_idx');
            $table->index(['event_type', 'sha256'], 'lse_type_hash_idx');
        });

        // "When did this device last send a security report" -- a report is sent on every
        // protected launch even when it is empty. A device that keeps talking to the API
        // but never reports is itself a signal: someone is blocking that one request.
        Schema::table('launcher_devices', function (Blueprint $table) {
            $table->timestamp('last_security_report_at')->nullable()->after('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::table('launcher_devices', function (Blueprint $table) {
            $table->dropColumn('last_security_report_at');
        });
        Schema::dropIfExists('launcher_security_events');
    }
};
