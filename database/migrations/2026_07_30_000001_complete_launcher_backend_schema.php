<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_sale_items', function (Blueprint $table): void {
            $table->boolean('launcher_enabled')->default(false)->after('manifest_revision')->index();
            $table->string('launcher_title')->nullable()->after('launcher_enabled');
            $table->text('launcher_excerpt')->nullable()->after('launcher_title');
            $table->unsignedBigInteger('launcher_cover_media_id')->nullable()->after('launcher_excerpt');
            $table->unsignedInteger('launcher_sort_order')->default(0)->after('launcher_cover_media_id');
            $table->foreign('launcher_cover_media_id')
                ->references('id')
                ->on('media_files')
                ->nullOnDelete();
        });

        Schema::table('product_sale_item_files', function (Blueprint $table): void {
            $table->string('file_name')->nullable()->after('title');
            $table->unsignedBigInteger('size_bytes')->nullable()->after('url');
            $table->char('sha256', 64)->nullable()->after('size_bytes');
            $table->string('mime_type')->nullable()->after('sha256');
            $table->unsignedInteger('part_number')->nullable()->after('mime_type');
            $table->string('archive_group')->nullable()->after('part_number');
            $table->boolean('is_required')->default(true)->after('archive_group');
            $table->boolean('is_active')->default(true)->after('is_required')->index();
        });

        Schema::table('product_sale_item_manifests', function (Blueprint $table): void {
            $table->string('install_folder_name')->nullable()->after('install_directory');
            $table->boolean('has_option_file')->default(false)->after('requires_administrator');
            $table->string('option_file_folder_name')->default('OPTION FILE')->after('has_option_file');
            $table->string('package_root_mode', 50)->default('merge_to_product_root')->after('option_file_folder_name');
        });

        Schema::create('launcher_devices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->uuid('device_id');
            $table->string('name');
            $table->char('fingerprint_hash', 64)->nullable();
            $table->string('platform', 30)->default('windows');
            $table->string('app_version', 30)->nullable();
            $table->string('last_ip', 45)->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('revoked_at')->nullable()->index();
            $table->timestamps();
            $table->unique(['user_id', 'device_id']);
        });

        Schema::table('personal_access_tokens', function (Blueprint $table): void {
            $table->foreignId('launcher_device_id')
                ->nullable()
                ->after('tokenable_id')
                ->constrained('launcher_devices')
                ->nullOnDelete();
        });

        Schema::create('launcher_updates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_sale_item_id')->constrained('product_sale_items')->cascadeOnDelete();
            $table->string('version', 50);
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('changelog')->nullable();
            $table->boolean('is_enabled')->default(false)->index();
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('has_option_file')->default(false);
            $table->string('archive_format', 30)->default('7z');
            $table->timestamp('published_at')->nullable()->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['product_sale_item_id', 'version']);
        });

        Schema::create('launcher_update_files', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('launcher_update_id')->constrained('launcher_updates')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('file_name');
            $table->text('url');
            $table->unsignedBigInteger('size_bytes');
            $table->char('sha256', 64);
            $table->string('mime_type')->nullable();
            $table->unsignedInteger('part_number')->nullable();
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('launcher_mod_groups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_sale_item_id')->constrained('product_sale_items')->cascadeOnDelete();
            $table->string('key', 100);
            $table->string('title');
            $table->boolean('is_exclusive')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['product_sale_item_id', 'key']);
        });

        Schema::create('launcher_mods', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_sale_item_id')->constrained('product_sale_items')->cascadeOnDelete();
            $table->foreignId('launcher_mod_group_id')->nullable()->constrained('launcher_mod_groups')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->string('version', 50);
            $table->unsignedBigInteger('image_media_id')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_enabled')->default(false)->index();
            $table->boolean('is_required')->default(false);
            $table->json('compatible_base_versions')->nullable();
            $table->json('compatible_update_versions')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->unique(['product_sale_item_id', 'slug']);
            $table->foreign('image_media_id')
                ->references('id')
                ->on('media_files')
                ->nullOnDelete();
        });

        Schema::create('launcher_mod_files', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('launcher_mod_id')->constrained('launcher_mods')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('file_name');
            $table->text('url');
            $table->unsignedBigInteger('size_bytes');
            $table->char('sha256', 64);
            $table->string('mime_type')->nullable();
            $table->unsignedInteger('part_number')->nullable();
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('launcher_checkout_sessions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('launcher_device_id')->constrained('launcher_devices')->cascadeOnDelete();
            $table->foreignId('product_sale_item_id')->constrained('product_sale_items')->cascadeOnDelete();
            $table->char('state_hash', 64)->unique();
            $table->timestamp('expires_at')->index();
            $table->timestamp('consumed_at')->nullable();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('launcher_download_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('purchase_access_id')->constrained('purchase_accesses')->cascadeOnDelete();
            $table->foreignId('launcher_device_id')->constrained('launcher_devices')->cascadeOnDelete();
            $table->string('content_type', 30);
            $table->unsignedBigInteger('content_id');
            $table->unsignedBigInteger('file_id');
            $table->uuid('download_session_id');
            $table->timestamp('session_expires_at');
            $table->timestamp('last_prepared_at')->nullable();
            $table->unsignedInteger('prepare_count')->default(1);
            $table->timestamps();
            $table->unique(
                ['purchase_access_id', 'launcher_device_id', 'content_type', 'file_id', 'download_session_id'],
                'launcher_content_download_session_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('launcher_download_sessions');
        Schema::dropIfExists('launcher_checkout_sessions');
        Schema::dropIfExists('launcher_mod_files');
        Schema::dropIfExists('launcher_mods');
        Schema::dropIfExists('launcher_mod_groups');
        Schema::dropIfExists('launcher_update_files');
        Schema::dropIfExists('launcher_updates');

        Schema::table('personal_access_tokens', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('launcher_device_id');
        });

        Schema::dropIfExists('launcher_devices');

        Schema::table('product_sale_item_manifests', function (Blueprint $table): void {
            $table->dropColumn([
                'install_folder_name',
                'has_option_file',
                'option_file_folder_name',
                'package_root_mode',
            ]);
        });

        Schema::table('product_sale_item_files', function (Blueprint $table): void {
            $table->dropColumn([
                'file_name',
                'size_bytes',
                'sha256',
                'mime_type',
                'part_number',
                'archive_group',
                'is_required',
                'is_active',
            ]);
        });

        Schema::table('product_sale_items', function (Blueprint $table): void {
            $table->dropForeign(['launcher_cover_media_id']);
            $table->dropColumn([
                'launcher_enabled',
                'launcher_title',
                'launcher_excerpt',
                'launcher_cover_media_id',
                'launcher_sort_order',
            ]);
        });
    }
};
