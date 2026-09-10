<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;

class AdminEditFormScrollTest extends TestCase
{
    public function test_every_inline_admin_edit_form_has_a_stable_scroll_target(): void
    {
        $forms = [
            'views/livewire/admin/taxonomy/partials/form.blade.php',
            'views/livewire/admin/taxonomy/partials/category-form.blade.php',
            'views/livewire/admin/support/ticket-departments/partials/form.blade.php',
            'views/livewire/admin/seo/redirects/partials/form.blade.php',
            'views/livewire/admin/catalog/product-medals/partials/form.blade.php',
            'views/livewire/admin/catalog/product-information-fields/partials/form.blade.php',
            'views/livewire/admin/catalog/product-attributes/partials/form.blade.php',
        ];

        foreach ($forms as $relativePath) {
            $contents = file_get_contents(resource_path($relativePath));

            self::assertIsString($contents, $relativePath);
            self::assertStringContainsString('class="scroll-mt-20"', $contents, $relativePath);
            self::assertStringContainsString('data-admin-inline-edit-form', $contents, $relativePath);
        }
    }

    public function test_inline_edit_actions_dispatch_the_global_scroll_event(): void
    {
        $components = [
            'app/Livewire/Admin/Taxonomy/Base/ManageFlatTaxonomyTerms.php',
            'app/Livewire/Admin/Taxonomy/Base/ManageHierarchicalTaxonomyCategories.php',
            'app/Livewire/Admin/Support/ManageTicketDepartments.php',
            'app/Livewire/Admin/Seo/ManageRedirects.php',
            'app/Livewire/Admin/Catalog/ManageProductMedals.php',
            'app/Livewire/Admin/Catalog/ManageProductInformationFields.php',
            'app/Livewire/Admin/Catalog/ManageProductAttributes.php',
        ];

        foreach ($components as $relativePath) {
            $contents = file_get_contents(base_path($relativePath));

            self::assertIsString($contents, $relativePath);
            self::assertStringContainsString(
                '$this->dispatch(\'admin-inline-edit-started\')',
                $contents,
                $relativePath,
            );
        }
    }

    public function test_global_listener_waits_for_livewire_dom_updates_before_scrolling(): void
    {
        $script = file_get_contents(resource_path('js/admin/admin.js'));

        self::assertIsString($script);
        self::assertStringContainsString("window.addEventListener('admin-inline-edit-started'", $script);
        self::assertSame(2, substr_count($script, 'requestAnimationFrame(() => {'));
        self::assertStringContainsString("document.querySelector('[data-admin-inline-edit-form]')", $script);
        self::assertStringContainsString('editForm.scrollIntoView(', $script);
        self::assertStringContainsString('focus({preventScroll: true})', $script);
    }
}
