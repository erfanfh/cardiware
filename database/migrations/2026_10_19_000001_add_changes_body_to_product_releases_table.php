<?php

use App\Domain\Catalog\Support\ProductReleaseContent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_releases', function (Blueprint $table) {
            $table->longText('changes_body')->nullable()->after('excerpt');
        });

        DB::table('product_releases')->select('id')->chunkById(200, function ($releases) {
            $changes = DB::table('product_release_changes')
                ->whereIn('product_release_id', $releases->pluck('id'))
                ->orderBy('sort_order')->orderBy('id')
                ->get()->groupBy('product_release_id');

            foreach ($releases as $release) {
                DB::table('product_releases')->where('id', $release->id)->update([
                    'changes_body' => ProductReleaseContent::fromChanges($changes->get($release->id, [])),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_releases', function (Blueprint $table) {
            $table->dropColumn('changes_body');
        });
    }
};
