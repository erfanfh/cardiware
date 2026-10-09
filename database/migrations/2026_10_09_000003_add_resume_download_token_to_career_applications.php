<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('career_applications', function (Blueprint $table): void {
            $table->uuid('resume_download_token')->nullable()->unique();
        });

        DB::table('career_applications')
            ->whereNotNull('resume_path')
            ->whereNull('resume_download_token')
            ->orderBy('id')
            ->chunkById(100, function ($applications): void {
                foreach ($applications as $application) {
                    DB::table('career_applications')
                        ->where('id', $application->id)
                        ->update(['resume_download_token' => (string) Str::uuid()]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('career_applications', function (Blueprint $table): void {
            $table->dropUnique(['resume_download_token']);
            $table->dropColumn('resume_download_token');
        });
    }
};
