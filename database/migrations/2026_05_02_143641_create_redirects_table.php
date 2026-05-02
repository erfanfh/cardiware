<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('redirects');

        Schema::create('redirects', function (Blueprint $table) {
            $table->id();

            $table->string('source_path', 2048);
            $table->string('source_path_hash', 32)->unique(); // md5

            $table->text('target_url')->nullable();

            $table->unsignedSmallInteger('status_code')->default(301);

            $table->string('format', 50)->default('plain');

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirects');

        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('source')->unique();
            $table->string('destination');
            $table->integer('status_code');
            $table->unsignedBigInteger('hits_count')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['source']);
        });
    }
};
