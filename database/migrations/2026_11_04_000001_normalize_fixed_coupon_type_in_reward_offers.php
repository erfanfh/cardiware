<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * پاداش‌های نمونه coupon_type را «fixed» ذخیره کرده بودند ولی مقدار معتبر «fixed_amount»
 * است؛ دریافت این پاداش‌ها خطای ۵۰۰ می‌داد.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reward_offers')) {
            return;
        }

        DB::table('reward_offers')->orderBy('id')->get(['id', 'config'])->each(function ($row): void {
            $config = json_decode((string) $row->config, true);
            if (is_array($config) && ($config['coupon_type'] ?? null) === 'fixed') {
                $config['coupon_type'] = 'fixed_amount';
                DB::table('reward_offers')->where('id', $row->id)->update(['config' => json_encode($config)]);
            }
        });
    }

    public function down(): void
    {
    }
};
