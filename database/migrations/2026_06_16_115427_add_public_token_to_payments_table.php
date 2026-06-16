<?php

use App\Domain\Financial\Models\Payment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('public_token', 40)
                ->nullable()
                ->unique()
                ->after('tracking_code');
        });

        Payment::query()
            ->whereNull('public_token')
            ->chunkById(100, function ($payments) {
                foreach ($payments as $payment) {
                    $payment->forceFill([
                        'public_token' => (string)Str::ulid(),
                    ])->save();
                }
            });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('public_token', 40)
                ->nullable(false)
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['public_token']);
            $table->dropColumn('public_token');
        });
    }
};
