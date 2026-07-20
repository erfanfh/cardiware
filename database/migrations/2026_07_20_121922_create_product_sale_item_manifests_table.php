<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(
            'product_sale_item_manifests',
            function (Blueprint $table): void {
                $table->id();
                $table->foreignId('product_sale_item_id')->unique()->constrained('product_sale_items')->cascadeOnDelete();
                $table->unsignedSmallInteger('schema_version')->default(1);
                $table->string('download_type', 40)->default('multiple_files');
                $table->string('assembly_strategy', 40)->default('independent');
                $table->string('archive_format', 30)->nullable();
                $table->string('assembled_file_name')->nullable();
                $table->string('validation_mode', 40)->default('none');
                $table->string('install_directory', 500)->nullable();
                $table->boolean('requires_administrator')->default(false);
                $table->string('main_executable_path', 500)->nullable();
                $table->string('working_directory', 500)->nullable();
                $table->text('launch_arguments')->nullable();
                $table->boolean('requires_sider')->default(false);
                $table->string('sider_executable_path', 500)->nullable();
                $table->string('sider_working_directory', 500)->nullable();
                $table->text('sider_arguments')->nullable();
                $table->boolean('run_sider_as_administrator')->default(false);
                $table->json('capabilities')->nullable();
                $table->json('install_options')->nullable();
                $table->json('runtime_notices')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('product_sale_item_manifests');
    }
};
