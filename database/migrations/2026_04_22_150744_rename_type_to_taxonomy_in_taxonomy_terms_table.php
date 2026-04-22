<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('taxonomy_terms', function (Blueprint $table) {
            $table->string('taxonomy', 100)->nullable()->after('id');
        });

        DB::table('taxonomy_terms')
            ->where('type', 'category')
            ->update(['taxonomy' => 'post_category']);

        DB::table('taxonomy_terms')
            ->where('type', 'tag')
            ->update(['taxonomy' => 'post_tag']);

        Schema::table('taxonomy_terms', function (Blueprint $table) {
            $table->dropUnique('taxonomy_terms_type_slug_parent_id_unique');
            $table->dropUnique('taxonomy_terms_type_path_unique');
        });

        Schema::table('taxonomy_terms', function (Blueprint $table) {
            $table->string('taxonomy', 100)->nullable(false)->change();

            $table->unique(
                ['taxonomy', 'slug', 'parent_id'],
                'taxonomy_terms_taxonomy_slug_parent_id_unique'
            );

            $table->unique(
                ['taxonomy', 'path'],
                'taxonomy_terms_taxonomy_path_unique'
            );

            $table->index('taxonomy', 'taxonomy_terms_taxonomy_index');
        });

        Schema::table('taxonomy_terms', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('taxonomy_terms', function (Blueprint $table) {
            $table->string('type', 50)->nullable()->after('id');
        });

        DB::table('taxonomy_terms')
            ->where('taxonomy', 'post_category')
            ->update(['type' => 'category']);

        DB::table('taxonomy_terms')
            ->where('taxonomy', 'post_tag')
            ->update(['type' => 'tag']);

        DB::table('taxonomy_terms')
            ->where('taxonomy', 'project_tag')
            ->update(['type' => 'tag']);

        Schema::table('taxonomy_terms', function (Blueprint $table) {
            $table->dropUnique('taxonomy_terms_taxonomy_slug_parent_id_unique');
            $table->dropUnique('taxonomy_terms_taxonomy_path_unique');
            $table->dropIndex('taxonomy_terms_taxonomy_index');
        });

        Schema::table('taxonomy_terms', function (Blueprint $table) {
            $table->string('type', 50)->nullable(false)->change();

            $table->unique(
                ['type', 'slug', 'parent_id'],
                'taxonomy_terms_type_slug_parent_id_unique'
            );

            $table->unique(
                ['type', 'path'],
                'taxonomy_terms_type_path_unique'
            );
        });

        Schema::table('taxonomy_terms', function (Blueprint $table) {
            $table->dropColumn('taxonomy');
        });
    }
};
