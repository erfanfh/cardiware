<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        /*
         * DATETIME هیچ رفتار خودکاری مثل
         * ON UPDATE CURRENT_TIMESTAMP ندارد.
         *
         * زمان انقضا فقط توسط Application تعیین می‌شود.
         */
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement(
                'ALTER TABLE `otps`
                 MODIFY COLUMN `expired_at` DATETIME NOT NULL'
            );

            DB::statement(
                'ALTER TABLE `otps`
                 MODIFY COLUMN `resend_available_at` DATETIME NULL DEFAULT NULL'
            );
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement(
                'ALTER TABLE `otps`
                 MODIFY COLUMN `expired_at` TIMESTAMP NOT NULL'
            );

            DB::statement(
                'ALTER TABLE `otps`
                 MODIFY COLUMN `resend_available_at` TIMESTAMP NULL DEFAULT NULL'
            );
        }
    }
};
