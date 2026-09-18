<?php

namespace Tests\Feature\Admin;

use Illuminate\Support\Facades\Blade;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tests\TestCase;

class AdminRowDeleteConfirmationTest extends TestCase
{
    public function test_delete_forms_render_with_the_shared_confirmation_hook(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-admin.form
                action="/admin/items/1"
                method="delete"
                class="row-action"
                data-confirm-title="حذف آیتم"
                data-confirm-text="پیام اختصاصی حذف"
            >
                <button type="submit">حذف</button>
            </x-admin.form>
        BLADE);

        self::assertMatchesRegularExpression('/class="[^"]*confirmationForm[^"]*"/', $html);
        self::assertMatchesRegularExpression('/class="[^"]*js-confirm[^"]*"/', $html);
        self::assertMatchesRegularExpression('/class="[^"]*row-action[^"]*"/', $html);
        self::assertSame(1, substr_count($html, 'class="'));
        self::assertStringContainsString('name="_method" value="DELETE"', $html);
        self::assertStringContainsString('data-confirm-title="حذف آیتم"', $html);
        self::assertStringContainsString('data-confirm-text="پیام اختصاصی حذف"', $html);
    }

    public function test_shared_modal_guards_every_spoofed_delete_form(): void
    {
        $modal = file_get_contents(resource_path('views/components/admin/flash-modal.blade.php'));

        self::assertIsString($modal);
        self::assertStringContainsString('input[name="_method"]', $modal);
        self::assertStringContainsString("spoofedMethod === 'DELETE'", $modal);
        self::assertStringContainsString('event.preventDefault()', $modal);
    }

    public function test_raw_admin_delete_forms_explicitly_use_the_confirmation_modal(): void
    {
        $unprotectedForms = [];

        foreach ($this->adminBladeFiles() as $file) {
            $contents = file_get_contents($file->getPathname());

            if (! is_string($contents)) {
                continue;
            }

            preg_match_all(
                '/<form\b(?<attributes>[^>]*)>(?:(?!<\/form>).)*@method\(\s*[\'\"]DELETE[\'\"]\s*\)/si',
                $contents,
                $matches,
            );

            foreach ($matches['attributes'] as $attributes) {
                if (! str_contains($attributes, 'js-confirm')) {
                    $unprotectedForms[] = $file->getPathname();
                }
            }
        }

        self::assertSame([], array_values(array_unique($unprotectedForms)));
    }

    public function test_ajax_comment_delete_uses_the_shared_confirmation_modal(): void
    {
        $script = file_get_contents(resource_path('js/admin/comment-actions.js'));

        self::assertIsString($script);
        self::assertStringContainsString("action === 'delete' ? 'حذف دیدگاه'", $script);
        self::assertStringContainsString("new CustomEvent('admin-open-confirm'", $script);
        self::assertStringNotContainsString('window.confirm(message)', $script);
    }

    /** @return list<SplFileInfo> */
    private function adminBladeFiles(): array
    {
        $files = [];

        foreach ([resource_path('views/admin'), resource_path('views/livewire/admin')] as $directory) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

            foreach ($iterator as $file) {
                if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                    $files[] = $file;
                }
            }
        }

        return $files;
    }
}
