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
        Schema::table('taxonomy_term_slugs', function (Blueprint $table) {
            $table->string('taxonomy', 100)->nullable()->after('id');
        });

        DB::table('taxonomy_term_slugs')
            ->where('type', 'category')
            ->update(['taxonomy' => 'post_category']);

        DB::table('taxonomy_term_slugs')
            ->where('type', 'tag')
            ->update(['taxonomy' => 'post_tag']);

        Schema::table('taxonomy_term_slugs', function (Blueprint $table) {
            $table->dropUnique('taxonomy_term_slugs_type_route_key_unique');
        });

        Schema::table('taxonomy_term_slugs', function (Blueprint $table) {
            $table->string('taxonomy', 100)->nullable(false)->change();

            $table->unique(
                ['taxonomy', 'route_key'],
                'taxonomy_term_slugs_taxonomy_route_key_unique'
            );

            $table->index('taxonomy', 'taxonomy_term_slugs_taxonomy_index');
        });

        Schema::table('taxonomy_term_slugs', function (Blueprint $table) {
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
        Schema::table('taxonomy_term_slugs', function (Blueprint $table) {
            $table->string('type', 50)->nullable()->after('id');
        });

        DB::table('taxonomy_term_slugs')
            ->where('taxonomy', 'post_category')
            ->update(['type' => 'category']);

        DB::table('taxonomy_term_slugs')
            ->where('taxonomy', 'post_tag')
            ->update(['type' => 'tag']);

        DB::table('taxonomy_term_slugs')
            ->where('taxonomy', 'project_tag')
            ->update(['type' => 'tag']);

        Schema::table('taxonomy_term_slugs', function (Blueprint $table) {
            $table->dropUnique('taxonomy_term_slugs_taxonomy_route_key_unique');
            $table->dropIndex('taxonomy_term_slugs_taxonomy_index');
        });

        Schema::table('taxonomy_term_slugs', function (Blueprint $table) {
            $table->string('type', 50)->nullable(false)->change();

            $table->unique(
                ['type', 'route_key'],
                'taxonomy_term_slugs_type_route_key_unique'
            );
        });

        Schema::table('taxonomy_term_slugs', function (Blueprint $table) {
            $table->dropColumn('taxonomy');
        });
    }
};
