<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Publisher signature over each product file's SHA-256.
 *
 * Until now a downloaded archive was checked against a hash that arrives in the same API
 * response as the download URL, so both halves of the comparison came from this server.
 * A server compromise could therefore hand a customer a modified archive together with a
 * matching hash, and the launcher would install it into the game folder. Launcher updates
 * never had that gap -- they are RSA-PSS signed with a key that never leaves the release
 * machine -- and these two columns close the same gap for product content, reusing that
 * same key.
 *
 * Both nullable on purpose: every file registered before this existed has no signature,
 * and the launcher treats absence as "unsigned, allowed for now" rather than as tampering
 * (see ProductPackageSignature.RequireSignature). Once every file has been re-registered
 * with a signature, that switch flips and absence becomes a hard failure.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_sale_item_files', function (Blueprint $table): void {
            // Base64 RSA-PSS signature over the raw 32 bytes of sha256. A 4096-bit key
            // produces 512 bytes, which is 684 characters of Base64, so `text` rather
            // than a bounded string keeps future key sizes from silently truncating.
            $table->text('sha256_signature')->nullable()->after('sha256');

            // SHA-256 of the signing key's SubjectPublicKeyInfo, lowercase hex. The
            // launcher checks this before the signature so that "we rotated the signing
            // key and did not ship the new launcher" reports as exactly that, instead of
            // looking like an attack.
            $table->char('signature_key_id', 64)->nullable()->after('sha256_signature');
        });
    }

    public function down(): void
    {
        Schema::table('product_sale_item_files', function (Blueprint $table): void {
            $table->dropColumn(['sha256_signature', 'signature_key_id']);
        });
    }
};
