<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropUnique('posts_slug_deleted_at_unique');

            $table->unique('slug', 'posts_slug_unique');

            $table->index(
                ['status', 'published_at', 'id'],
                'posts_status_published_id_index'
            );

            $table->index(
                ['status', 'is_pinned', 'published_at', 'id'],
                'posts_status_pinned_published_id_index'
            );

            $table->index(
                ['author_id', 'status', 'published_at', 'id'],
                'posts_author_status_published_id_index'
            );
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_status_published_at_index');
            $table->dropIndex('posts_author_id_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->index(
                ['author_id', 'status'],
                'posts_author_id_status_index'
            );

            $table->index(
                ['status', 'published_at'],
                'posts_status_published_at_index'
            );

            $table->dropUnique('posts_slug_unique');

            $table->unique(
                ['slug', 'deleted_at'],
                'posts_slug_deleted_at_unique'
            );
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_author_status_published_id_index');
            $table->dropIndex('posts_status_pinned_published_id_index');
            $table->dropIndex('posts_status_published_id_index');
        });
    }
};
