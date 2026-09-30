<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_descriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->boolean('is_legacy')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['order_id', 'created_at', 'id']);
        });

        // The old column has no note-specific timestamp. Preserve its text and
        // flag the order's last update time as an approximation in the UI.
        DB::table('orders')
            ->whereNotNull('description')
            ->where('description', '<>', '')
            ->select(['id', 'description', 'created_at', 'updated_at'])
            ->chunkById(500, function ($orders): void {
                $rows = [];

                foreach ($orders as $order) {
                    $body = trim((string) $order->description);
                    if ($body === '') {
                        continue;
                    }

                    $rows[] = [
                        'order_id' => $order->id,
                        'created_by_user_id' => null,
                        'body' => $body,
                        'is_legacy' => true,
                        'created_at' => $order->updated_at ?? $order->created_at ?? now(),
                    ];
                }

                if ($rows !== []) {
                    DB::table('order_descriptions')->insert($rows);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_descriptions');
    }
};
