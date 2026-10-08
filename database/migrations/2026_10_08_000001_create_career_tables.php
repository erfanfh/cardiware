<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_positions', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('department', 100)->nullable();
            $table->string('employment_type', 100)->nullable();
            $table->string('cooperation_type', 100)->nullable();
            $table->string('location', 100)->nullable();
            $table->string('level', 100)->nullable();
            $table->string('detail_label', 100)->nullable();
            $table->string('detail_value', 200)->nullable();
            $table->text('summary');
            $table->json('requirements')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('closes_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('career_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('career_position_id')->nullable()->constrained('career_positions')->nullOnDelete();
            $table->string('position_title', 255)->nullable();
            $table->string('full_name');
            $table->string('phone', 30);
            $table->string('email')->nullable();
            $table->string('preferred_cooperation_type', 100)->nullable();
            $table->string('portfolio_url', 2048)->nullable();
            $table->text('skills_and_experience');
            $table->string('resume_path', 2048)->nullable();
            $table->string('resume_original_name')->nullable();
            $table->string('resume_mime', 100)->nullable();
            $table->string('status', 20)->default('new')->index();
            $table->text('internal_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['career_position_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_applications');
        Schema::dropIfExists('career_positions');
    }
};
