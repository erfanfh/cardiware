<?php

namespace Tests\Unit\System;

use App\Domain\System\Models\Setting;
use App\Infrastructure\Casts\ArrayCast;
use InvalidArgumentException;
use JsonException;
use PHPUnit\Framework\TestCase;

class ArrayCastTest extends TestCase
{
    private function leaveUnrelatedJsonError(): void
    {
        json_decode('{invalid json');
        self::assertSame(JSON_ERROR_SYNTAX, json_last_error());
    }

    public function test_valid_array_can_be_encoded_after_an_unrelated_json_failure(): void
    {
        $value = ['title' => 'عنوان فارسی', 'items' => [], 'link' => null];
        $this->leaveUnrelatedJsonError();

        $encoded = (new ArrayCast)->set(new Setting, 'ultimate', $value, []);

        self::assertSame($value, json_decode($encoded, true, 512, JSON_THROW_ON_ERROR));
    }

    public function test_valid_json_can_be_decoded_after_an_unrelated_json_failure(): void
    {
        $value = ['title' => 'عنوان فارسی', 'items' => [], 'link' => null];
        $encoded = json_encode($value, JSON_THROW_ON_ERROR);
        $this->leaveUnrelatedJsonError();

        self::assertSame($value, (new ArrayCast)->get(new Setting, 'ultimate', $encoded, []));
    }

    public function test_invalid_utf8_is_repaired_without_losing_valid_nested_content(): void
    {
        $encoded = (new ArrayCast)->set(new Setting, 'ultimate', [
            'title' => 'عنوان فارسی',
            'items' => [["\xB1key" => "\xB1\x31", 'enabled' => true]],
        ], []);
        $decoded = json_decode($encoded, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('عنوان فارسی', $decoded['title']);
        self::assertTrue($decoded['items'][0]['enabled']);
        self::assertSame(mb_scrub("\xB1\x31", 'UTF-8'), $decoded['items'][0][mb_scrub("\xB1key", 'UTF-8')]);
    }

    public function test_malformed_stored_json_is_still_rejected(): void
    {
        try {
            (new ArrayCast)->get(new Setting, 'ultimate', '{invalid json', []);
            self::fail('Malformed JSON must not be accepted.');
        } catch (InvalidArgumentException $exception) {
            self::assertInstanceOf(JsonException::class, $exception->getPrevious());
            self::assertSame(JSON_ERROR_SYNTAX, $exception->getPrevious()->getCode());
        }
    }
}
