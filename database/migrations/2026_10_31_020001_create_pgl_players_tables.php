<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // دیتای بازیکنان پچ PGL — منبع: pgl6_db_export.py (pgl6_export_v2.json)
        Schema::create('pgl_imports', function (Blueprint $table) {
            $table->id();
            $table->string('label')->nullable();       // مثلاً «آپدیت 6.3»
            $table->string('source', 16)->default('bin'); // bin | csv
            $table->unsignedInteger('players_count')->default(0);
            $table->unsignedInteger('teams_count')->default(0);
            $table->unsignedInteger('changes_count')->default(0);
            $table->string('file_sha256', 64)->nullable();
            $table->timestamps();
        });

        Schema::create('pgl_teams', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();  // teamId خود پچ
            $table->string('name');
            $table->string('name_fa')->nullable();     // نام فارسی (از پنل/ایمپورت، دستی قابل ویرایش)
            $table->string('code', 8)->default('');
            $table->timestamps();
        });

        Schema::create('pgl_players', function (Blueprint $table) {
            $table->unsignedInteger('pid')->primary(); // playerId خود پچ
            $table->foreignId('import_id')->constrained('pgl_imports')->cascadeOnUpdate();
            $table->string('name');
            $table->string('name_fa')->nullable();     // نام فارسی (دستی/فایل ایمپورت؛ در آپدیت‌ها حفظ می‌شود)
            $table->string('shirt', 64)->default('');
            $table->unsignedTinyInteger('height')->nullable(); // سانتی‌متر
            $table->unsignedTinyInteger('weight')->nullable(); // کیلوگرم
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('pos', 8)->nullable();
            $table->string('pos_fa', 32)->default('');
            $table->unsignedSmallInteger('overall')->nullable();
            $table->unsignedTinyInteger('playing_style')->nullable();
            $table->json('stats')->nullable();
            $table->json('pos_grades')->nullable();
            $table->json('team_ids')->nullable();
            $table->boolean('is_primary')->default(true); // کارت‌های چندگانه → یک نمایش اصلی
            $table->unsignedInteger('primary_pid')->nullable();
            $table->timestamps();

            $table->index('name');
            $table->index(['is_primary', 'overall']);
            $table->index('pos');
        });

        Schema::create('pgl_player_team', function (Blueprint $table) {
            $table->unsignedInteger('player_pid');
            $table->unsignedInteger('team_id');
            $table->primary(['player_pid', 'team_id']);
            $table->index('team_id');
        });

        // تغییرات هر بازیکن بین دو ایمپورت (چنج‌لاگ رسمی پچ)
        Schema::create('pgl_player_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained('pgl_imports')->cascadeOnDelete();
            $table->unsignedInteger('pid');
            $table->string('name');
            $table->string('type');                    // overall|position|age|height|weight|stats|transfer
            $table->json('payload')->nullable();       // {overall:[79,81], transfer:{from,to}, stats:{...}}
            $table->timestamps();

            $table->index(['import_id', 'pid']);
            $table->index('pid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pgl_player_changes');
        Schema::dropIfExists('pgl_player_team');
        Schema::dropIfExists('pgl_players');
        Schema::dropIfExists('pgl_teams');
        Schema::dropIfExists('pgl_imports');
    }
};
