<?php

namespace Tests\Feature\Admin;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tests\TestCase;

class BulkSelectionCompositionTest extends TestCase
{
    public function test_admin_views_do_not_flatten_the_bulk_selection_getter_with_object_spread(): void
    {
        $views = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(resource_path('views/admin')),
        );

        foreach ($views as $view) {
            if (!$view instanceof SplFileInfo || !$view->isFile() || $view->getExtension() !== 'php') {
                continue;
            }

            self::assertStringNotContainsString(
                '...adminBulkSelection()',
                (string) file_get_contents($view->getPathname()),
                "Bulk selection must be mounted directly or in a parent Alpine scope: {$view->getPathname()}",
            );
        }
    }
}
