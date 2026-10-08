<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('career_applications', function (Blueprint $table): void {
            $table->timestamp('viewed_at')->nullable()->index();
            $table->foreignId('viewed_by')->nullable()->constrained('users')->nullOnDelete();
        });

        DB::table('career_applications')->whereNotNull('reviewed_at')
            ->update(['viewed_at' => DB::raw('reviewed_at'), 'viewed_by' => DB::raw('reviewed_by')]);
    }

    public function down(): void
    {
        Schema::table('career_applications', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('viewed_by');
            $table->dropIndex(['viewed_at']);
            $table->dropColumn('viewed_at');
        });
    }
};
