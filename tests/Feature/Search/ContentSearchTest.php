<?php

namespace Tests\Feature\Search;

use App\Application\Search\Support\ContentSearchQuery;
use App\Domain\Catalog\Models\Product;
use App\Domain\Content\Models\Post;
use App\Domain\Project\Models\Project;
use App\Domain\Video\Models\Video;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContentSearchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['posts', 'projects', 'products', 'videos'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table) use ($tableName): void {
                $table->id();
                $table->string('title');
                $table->text('excerpt')->nullable();
                $table->longText('body')->nullable();
                $table->string('status')->default('active');

                if ($tableName !== 'projects') {
                    $table->timestamp('published_at')->nullable();
                }

                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    protected function tearDown(): void
    {
        Schema::dropAllTables();

        parent::tearDown();
    }

    public function test_it_searches_titles_excerpts_and_bodies_across_all_public_content_types(): void
    {
        $now = now();

        $bodyMatchId = DB::table('posts')->insertGetId([
            'title' => 'نوشته قدیمی',
            'excerpt' => 'خلاصه متفاوت',
            'body' => '<p>راهنمای کامل نصب کهکشان برای بازی</p>',
            'status' => 'active',
            'published_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $titleMatchId = DB::table('posts')->insertGetId([
            'title' => 'کهکشان',
            'excerpt' => 'متن کوتاه',
            'body' => '<p>محتوای نوشته</p>',
            'status' => 'active',
            'published_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('projects')->insert([
            'title' => 'پروژه تست',
            'excerpt' => 'یک پروژه درباره کهکشان',
            'body' => '<p>متن پروژه</p>',
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach (['products', 'videos'] as $tableName) {
            DB::table($tableName)->insert([
                'title' => "{$tableName} test",
                'excerpt' => 'خلاصه محتوا',
                'body' => '<p>توضیحات کامل کهکشان</p>',
                'status' => 'active',
                'published_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $search = app(ContentSearchQuery::class);
        $posts = $search->apply(Post::query(), 'کهکشان')->get();

        self::assertSame([$titleMatchId, $bodyMatchId], $posts->pluck('id')->all());
        self::assertSame(1, $search->apply(Project::query(), 'کهکشان')->count());
        self::assertSame(1, $search->apply(Product::query(), 'کهکشان')->count());
        self::assertSame(1, $search->apply(Video::query(), 'کهکشان')->count());
    }

    public function test_all_words_must_exist_but_can_be_in_different_searchable_fields(): void
    {
        $now = now();

        DB::table('posts')->insert([
            [
                'title' => 'آموزش نصب',
                'excerpt' => 'مود گرافیکی حرفه‌ای',
                'body' => 'متن نوشته',
                'status' => 'active',
                'published_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'آموزش دیگر',
                'excerpt' => 'بدون عبارت دوم',
                'body' => 'متن نوشته',
                'status' => 'active',
                'published_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $results = app(ContentSearchQuery::class)
            ->apply(Post::query(), 'آموزش گرافیکی')
            ->get();

        self::assertCount(1, $results);
        self::assertSame('آموزش نصب', $results->first()->title);
    }

    public function test_an_empty_search_returns_the_entire_public_catalog(): void
    {
        DB::table('posts')->insert([
            'title' => 'نوشته تست',
            'status' => 'active',
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $count = app(ContentSearchQuery::class)
            ->apply(Post::query(), '   ')
            ->count();

        self::assertSame(1, $count);
    }
}
