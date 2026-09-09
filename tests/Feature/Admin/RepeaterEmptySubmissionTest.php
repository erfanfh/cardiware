<?php

namespace Tests\Feature\Admin;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class RepeaterEmptySubmissionTest extends TestCase
{
    public function test_shared_repeater_renders_an_empty_value_before_its_indexed_fields(): void
    {
        $html = Blade::render(<<<'BLADE'
            <x-admin.form.repeater name="links" :items="[]" />
        BLADE);

        $emptyValuePosition = strpos($html, 'data-repeater-empty-value');
        $indexedFieldsPosition = strpos($html, 'x-ref="list"');

        self::assertNotFalse($emptyValuePosition);
        self::assertNotFalse($indexedFieldsPosition);
        self::assertLessThan($indexedFieldsPosition, $emptyValuePosition);
        self::assertStringContainsString('x-bind:name="name" value=""', $html);
    }

    public function test_empty_value_is_replaced_when_indexed_repeater_fields_are_present(): void
    {
        parse_str('links=&links%5B0%5D%5Btitle%5D=First', $populatedPayload);
        parse_str('links=', $emptyPayload);

        self::assertSame([['title' => 'First']], $populatedPayload['links']);
        self::assertArrayHasKey('links', $emptyPayload);
        self::assertSame('', $emptyPayload['links']);
    }
}
