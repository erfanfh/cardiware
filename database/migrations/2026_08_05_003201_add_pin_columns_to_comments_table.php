<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table
                ->boolean('is_pinned')
                ->default(false)
                ->after('score');

            $table
                ->timestamp('pinned_at')
                ->nullable()
                ->after('is_pinned');

            $table
                ->foreignId('pinned_by')
                ->nullable()
                ->after('pinned_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->index(
                [
                    'commentable_type',
                    'commentable_id',
                    'is_pinned',
                    'pinned_at',
                ],
                'comments_commentable_pinned_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex('comments_commentable_pinned_index');
            $table->dropConstrainedForeignId('pinned_by');

            $table->dropColumn([
                'is_pinned',
                'pinned_at',
            ]);
        });
    }
};
