<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        /*
         * OTPهای قبلی کد خام دارند و با ساختار جدید سازگار نیستند.
         * بنابراین همه را منقضی می‌کنیم.
         */
        DB::table('otps')->update([
            'is_used' => true,
        ]);

        Schema::table('otps', function (Blueprint $table): void {
            $table->string('code')->nullable()->change();
            $table->string('code_hash', 64)->nullable()->after('code');
            $table->unsignedTinyInteger('attempts')->default(0)->after('code_hash');
            $table->timestamp('resend_available_at')->nullable()->after('expired_at');

            $table->index(['key', 'type', 'is_used'], 'otps_key_type_used_index');
        });

        /*
         * حذف ON UPDATE CURRENT_TIMESTAMP از expired_at
         */
        DB::statement(
            'ALTER TABLE `otps`
             MODIFY `expired_at` TIMESTAMP NOT NULL'
        );
    }

    public function down(): void
    {
        Schema::table('otps', function (Blueprint $table): void {
            $table->dropIndex('otps_key_type_used_index');

            $table->dropColumn([
                'code_hash',
                'attempts',
                'resend_available_at',
            ]);
        });
    }
};
