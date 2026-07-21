<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('job_title')->nullable();
            $table->text('bio')->nullable();
            $table->foreignId('picture_id')->nullable()->constrained('media_files')->nullOnDelete();
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('website_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
