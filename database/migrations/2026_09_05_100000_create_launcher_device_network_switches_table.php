<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records only the moments a launcher device is seen from a DIFFERENT network than
 * last time -- not every request, and not the raw address.
 *
 * The point is detecting a cloned device: someone who decompiles the launcher can read
 * the DPAPI entropy, lift their own device key, and make a second machine present the
 * same fingerprint. Nothing in the protocol can tell the two apart, because to the
 * server they are byte-for-byte the same device. What gives it away is behaviour: one
 * real machine settles on a network and stays there, so it writes one row here and then
 * nothing. Two machines in different places take turns, so they write rows constantly.
 *
 * Only the network prefix is stored (a /16 for IPv4, /48 for IPv6), never the full
 * address: it is enough to tell "different network" from "same network" and keeps this
 * table from becoming a location history of every customer.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('launcher_device_network_switches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('launcher_device_id')
                ->constrained('launcher_devices')
                ->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Truncated prefix, e.g. "5.121" or "2a01:4f8:c17". Wide enough for a /48
            // IPv6 prefix written out in full.
            $table->string('network', 45);
            $table->string('previous_network', 45)->nullable();

            // Seconds since this device was last seen on the PREVIOUS network. A laptop
            // handing off from wi-fi to a phone hotspot does this occasionally with a
            // small gap; two people playing the same account do it repeatedly. Storing
            // the gap here means the admin query is a simple count instead of a
            // window function over the whole table.
            $table->unsignedInteger('seconds_since_previous')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['launcher_device_id', 'created_at'], 'lds_device_created_idx');
            $table->index(['user_id', 'created_at'], 'lds_user_created_idx');
            $table->index(['created_at', 'seconds_since_previous'], 'lds_switch_time_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('launcher_device_network_switches');
    }
};
