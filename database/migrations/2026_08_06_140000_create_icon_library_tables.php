<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('icon_sets', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('label', 150);
            $table->string('source_file')->nullable();
            $table->char('checksum', 64)->nullable()->index();
            $table->unsignedInteger('icons_count')->default(0);
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });

        Schema::create('icons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('icon_set_id')->constrained('icon_sets')->cascadeOnDelete();
            $table->string('name', 190);
            $table->string('full_name', 255)->unique();
            $table->string('search_text', 255)->index();
            $table->mediumText('svg');
            $table->uuid('import_batch')->index();

            $table->unique(['icon_set_id', 'name']);
            $table->index(['icon_set_id', 'search_text']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icons');
        Schema::dropIfExists('icon_sets');
    }
};
