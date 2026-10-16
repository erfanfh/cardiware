<?php

namespace Tests\Feature\Catalog;

use App\Application\Catalog\Support\ProductEditLock;
use App\Domain\Catalog\Models\Product;
use App\Domain\Identity\Models\User;
use App\Domain\Marketplace\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ProductFileSizeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::before(fn () => true);
        $this->actingAs(User::factory()->createQuietly());
        Store::create(['title' => 'Store', 'slug' => 'file-size-store', 'status' => 'active']);
    }

    public function test_admin_form_has_an_optional_editor_and_restores_old_input(): void
    {
        $this->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('حجم فایل ها')
            ->assertSee('name="file_size_body"', false);

        $this->withSession(['_old_input' => ['file_size_body' => '<p>حجم ذخیره نشده</p>']])
            ->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee(e('<p>حجم ذخیره نشده</p>'), false);
    }

    public function test_editor_content_is_saved_on_create_update_and_clear(): void
    {
        $body = '<p>فایل اصلی: <strong>۱۰ گیگابایت</strong></p><table><tbody><tr><td>آپدیت</td><td>۲ گیگابایت</td></tr></tbody></table>';
        $payload = $this->payload() + ['file_size_body' => $body];
        $this->post(route('admin.products.store'), $payload)->assertSessionHasNoErrors()->assertRedirect();

        $product = Product::withoutGlobalScopes()->where('slug', $payload['slug'])->firstOrFail();
        self::assertSame($body, $product->file_size_body);
        $this->get(route('admin.products.edit', $product))->assertOk()->assertSee(e($body), false);

        $payload['edit_lock_token'] = app(ProductEditLock::class)->acquire($product, auth()->user());
        $payload['file_size_body'] = '<p>حجم جدید: ۱۲ گیگابایت</p>';
        $this->put(route('admin.products.update', $product), $payload)->assertSessionHasNoErrors()->assertRedirect();
        self::assertSame($payload['file_size_body'], $product->fresh()->file_size_body);

        // An unrelated update that omits the editor must preserve its content.
        $savedBody = $payload['file_size_body'];
        unset($payload['file_size_body']);
        $this->put(route('admin.products.update', $product), $payload)->assertSessionHasNoErrors()->assertRedirect();
        self::assertSame($savedBody, $product->fresh()->file_size_body);

        $payload['file_size_body'] = '';
        $this->put(route('admin.products.update', $product), $payload)->assertSessionHasNoErrors()->assertRedirect();
        self::assertNull($product->fresh()->file_size_body);
        self::assertStringNotContainsString('id="file-size"', view('front.catalog.products.partials.detail.file-size', [
            'product' => $product->fresh(),
        ])->render());
    }

    public function test_file_size_renders_after_releases_with_formatted_content(): void
    {
        $body = '<p>حجم فایل: <strong>۱۰ گیگابایت</strong></p>';
        $product = Product::withoutEvents(fn () => Product::create($this->payload() + [
            'file_size_body' => $body, 'published_at' => now()->subMinute(),
        ]));
        $product->releases()->create(['title' => 'نسخه اول', 'sort_order' => 0]);
        $product->refresh();

        $html = view('front.catalog.products.detail', [
            'product' => $product,
            'relatedProducts' => collect(),
            'storeProducts' => 1,
            'storeSoldQuantity' => 0,
            'soldQuantity' => 0,
            'averageRating' => null,
        ])->render();

        self::assertStringContainsString('حجم فایل ها', $html);
        self::assertStringContainsString($body, $html);
        self::assertStringContainsString('tinymce-content dark:text-slate-300', $html);
        self::assertStringContainsString('id="releases"', $html);
        self::assertStringContainsString('id="file-size"', $html);
        self::assertLessThan(strpos($html, 'id="file-size"'), strpos($html, 'id="releases"'));
        self::assertGreaterThan(strpos($html, 'id="file-size"'), strpos($html, 'id="comments"'));
    }

    public function test_empty_editor_markup_does_not_render_a_section_but_media_content_does(): void
    {
        foreach ([null, '', '  ', '<p></p>', '<p><br></p>', '<p>&nbsp;</p>', '<div> &#160; </div>'] as $body) {
            $product = new Product(['file_size_body' => $body]);
            self::assertFalse($product->hasFileSizeContent());
            self::assertSame('', trim(view('front.catalog.products.partials.detail.file-size', compact('product'))->render()));
        }

        $product = new Product(['file_size_body' => '<p><img src="https://example.test/sizes.png" alt="حجم فایل ها"></p>']);
        self::assertTrue($product->hasFileSizeContent());
        self::assertStringContainsString('id="file-size"', view('front.catalog.products.partials.detail.file-size', compact('product'))->render());
    }

    public function test_file_size_editor_rejects_non_text_input(): void
    {
        $this->postJson(route('admin.products.store'), $this->payload() + ['file_size_body' => ['invalid']])
            ->assertUnprocessable()->assertJsonValidationErrors('file_size_body');

        $this->assertDatabaseCount('products', 0);
    }

    private function payload(): array
    {
        return [
            'title' => 'File size product',
            'slug' => 'file-size-product',
            'body' => 'Product description',
            'store_id' => Store::query()->firstOrFail()->id,
            'type' => 'digital',
            'structure' => 'simple',
            'status' => 'active',
        ];
    }
}
