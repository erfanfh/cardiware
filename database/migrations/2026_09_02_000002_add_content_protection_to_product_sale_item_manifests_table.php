<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-product DRM policy for the launcher to hand to sider.dll.
     *
     * These lists used to be compiled into pgldrm.cpp, which meant every ordinary
     * content change -- a new protected file, an updated Lua module, a new exclusion
     * -- required rebuilding both sider.dll variants, re-registering their SHA-256
     * hashes in the launcher, and shipping a new launcher release. Storing them here
     * turns all of that into an admin-panel edit.
     *
     * Nothing stored here is secret: it is a list of paths plus digests of files every
     * customer already has. The protection comes from the delivery path (a verified
     * sider.dll, over a per-run CurrentUserOnly pipe), not from hiding these values.
     * The actual key value stays where it is, in content_key_value behind the vault.
     */
    public function up(): void
    {
        Schema::table('product_sale_item_manifests', function (Blueprint $table): void {
            // Folder substrings treated as protected wholesale (e.g. "pesdb"). Only
            // safe where every file under the folder is ours AND encrypted.
            $table->json('protected_folder_markers')->nullable()->after('content_key_value');
            // Full relative virtual paths, suffix-matched -- required for folders that
            // also hold base-game files we do not own.
            $table->json('protected_exact_paths')->nullable()->after('protected_folder_markers');
            // Checked first: files that must ship plain despite living in a protected
            // folder (Competition.bin, CompetitionRegulation.bin).
            $table->json('protected_excluded_paths')->nullable()->after('protected_exact_paths');
            // [{path, tier: content|shipped, sha256}] -- the Lua modules allowed to run.
            $table->json('protected_modules')->nullable()->after('protected_excluded_paths');
            // When true, a module that is not on the allowlist is not loaded at all.
            // Defaults true: for a product that declares a content key, "allow every
            // module" is the setting that silently weakens protection, so it has to be
            // turned off deliberately rather than by omission.
            $table->boolean('protected_strict_modules')->default(true)->after('protected_modules');
        });
    }

    public function down(): void
    {
        Schema::table('product_sale_item_manifests', function (Blueprint $table): void {
            $table->dropColumn([
                'protected_folder_markers',
                'protected_exact_paths',
                'protected_excluded_paths',
                'protected_modules',
                'protected_strict_modules',
            ]);
        });
    }
};
