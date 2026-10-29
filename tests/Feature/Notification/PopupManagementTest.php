<?php

namespace Tests\Feature\Notification;

use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Notification\Models\Popup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PopupManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->actingAs(User::factory()->create([
            'role_id' => Role::factory()->create(['is_super_admin' => true])->id,
        ]));
    }

    public function test_admin_can_save_edit_and_remove_multiple_buttons(): void
    {
        $buttons = [
            ['text' => 'مشاهده محصولات', 'url' => 'https://example.com/products'],
            ['text' => 'اطلاعات بیشتر', 'url' => 'https://example.com/details?a=1&b=2'],
        ];
        $this->get(route('admin.popups.create'))->assertOk()->assertSee('افزودن دکمه')->assertSee('حالت نمایش');
        $this->post(route('admin.popups.store'), $this->payload(['buttons' => $buttons]))
            ->assertRedirect(route('admin.popups.index'))->assertSessionHasNoErrors();
        $popup = Popup::query()->sole();
        $this->assertSame($buttons, $popup->buttons);
        $this->assertSame('standard', $popup->display_mode);
        $this->get(route('admin.popups.edit', $popup))->assertOk()->assertSee('افزودن دکمه');

        $this->put(route('admin.popups.update', $popup), $this->payload(['buttons' => [$buttons[1]]]))
            ->assertSessionHasNoErrors();
        $this->assertSame([$buttons[1]], $popup->fresh()->buttons);
        $this->put(route('admin.popups.update', $popup), $this->payload(['buttons' => '']))
            ->assertSessionHasNoErrors();
        $this->assertSame([], $popup->fresh()->buttons);
    }

    public function test_image_only_requires_an_image_and_saves_with_valid_public_media(): void
    {
        $this->post(route('admin.popups.store'), $this->payload(['display_mode' => 'image_only']))
            ->assertSessionHasErrors('image.id');
        $image = MediaFile::create([
            'title' => 'تصویر پاپ‌آپ', 'type' => 'image', 'visibility' => 'public', 'status' => 'ready',
            'created_by' => auth()->id(),
        ]);
        $this->post(route('admin.popups.store'), $this->payload([
            'display_mode' => 'image_only', 'image' => ['id' => $image->id],
        ]))->assertSessionHasNoErrors();
        $this->assertSame('image_only', Popup::query()->sole()->display_mode);
        $this->assertSame($image->id, Popup::query()->sole()->image_id);
    }

    #[DataProvider('invalidButtons')]
    public function test_invalid_or_unsafe_buttons_are_rejected(array $buttons, string $error): void
    {
        $this->post(route('admin.popups.store'), $this->payload(['buttons' => $buttons]))
            ->assertSessionHasErrors($error);
        $this->assertDatabaseCount('popups', 0);
    }

    public static function invalidButtons(): array
    {
        return [
            'script URL' => [[['text' => 'Click', 'url' => 'javascript:alert(1)']], 'buttons.0.url'],
            'data URL' => [[['text' => 'Click', 'url' => 'data:text/html,test']], 'buttons.0.url'],
            'missing text' => [[['text' => '', 'url' => 'https://example.com']], 'buttons.0.text'],
            'missing URL' => [[['text' => 'Click']], 'buttons.0.url'],
            'too many buttons' => [array_fill(0, 21, ['text' => 'Click', 'url' => 'https://example.com']), 'buttons'],
        ];
    }

    public function test_image_only_hides_visible_text_and_renders_uncropped_image_and_optional_buttons(): void
    {
        $popup = new Popup($this->payload([
            'display_mode' => 'image_only', 'buttons' => [['text' => '<b>خرید</b>', 'url' => 'https://example.com/?a=1&b=2']],
        ]));
        $image = new class extends MediaFile {
            public function getUrlAttribute(): ?string { return 'https://example.com/banner.jpg'; }
        };
        $popup->setRelation('image', $image);
        $html = view('front.notification.popups', ['popups' => collect([$popup])])->render();
        $this->assertStringNotContainsString('<h2', $html);
        $this->assertStringNotContainsString('متن آزمایشی', $html);
        $this->assertStringContainsString('object-contain', $html);
        $this->assertStringContainsString('aria-label="پیشنهاد ویژه"', $html);
        $this->assertStringContainsString('&lt;b&gt;خرید&lt;/b&gt;', $html);
        $this->assertStringContainsString('href="https://example.com/?a=1&amp;b=2"', $html);
        $this->assertStringContainsString('data-popup-dismiss="later"', $html);
        $this->assertStringContainsString('data-popup-dismiss="long"', $html);

        $popup->buttons = [];
        $html = view('front.notification.popups', ['popups' => collect([$popup])])->render();
        $this->assertStringNotContainsString('<a ', $html);
        $this->assertStringNotContainsString('<h2', $html);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'پیشنهاد ویژه', 'body' => 'متن آزمایشی', 'target_type' => 'home',
            'specific_target' => '0', 'audience' => 'all', 'delay_seconds' => '0',
            'close_minutes' => '60', 'hide_days' => '30', 'is_active' => '1',
        ], $overrides);
    }
}
