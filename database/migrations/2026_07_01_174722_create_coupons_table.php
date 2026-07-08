<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();

            $table->string('code', 80)->unique();
            $table->string('title');
            $table->text('description')->nullable();

            $table->string('type', 30);
            $table->unsignedBigInteger('value');

            $table->unsignedBigInteger('max_discount_amount')->nullable();

            $table->string('scope', 30);
            $table->string('status', 30)->default('active');

            $table->boolean('applies_to_all_users')->default(true);

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->unsignedBigInteger('minimum_order_amount')->default(0);

            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_limit_per_user')->nullable();
            $table->unsignedInteger('used_count')->default(0);

            $table->boolean('applies_to_addons')->default(false);

            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'starts_at', 'expires_at']);
            $table->index('scope');
            $table->index('created_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
