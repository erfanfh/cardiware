<?php

use App\Domain\Seo\Enums\RedirectStatusCodeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('redirects', function (Blueprint $table) {
            $table->id();

            $table->string('source_path', 2048)->unique();
            $table->text('target_url')->nullable();

            $table->unsignedSmallInteger('status_code')
                ->default(RedirectStatusCodeEnum::MovedPermanently->value);

            $table->string('format', 50)->default('plain');

            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('hits_count')->default(0);
            $table->timestamp('last_used_at')->nullable();

            $table->timestamps();

            $table->index(['is_active', 'status_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirects');
    }
};
