<?php

namespace Tests\Unit\Video;

use App\Http\Requests\Admin\Video\SaveVideoRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SaveVideoRequestTest extends TestCase
{
    public function test_empty_admin_payload_is_accepted_and_normalized(): void
    {
        $request = SaveVideoRequest::create('/admin/videos', 'POST');
        $request->setContainer($this->app);
        $request->setRedirector($this->app->make('redirect'));
        $request->validateResolved();

        $data = $request->validated();

        self::assertSame('', $data['title']);
        self::assertSame('', $data['video_url']);
        self::assertSame('inactive', $data['status']);
        self::assertSame('aparat', $data['source_type']);
        self::assertNull($data['cover_media_file_id']);
        self::assertNull($data['duration_seconds']);
        self::assertSame(0, $data['display_views_offset']);
        self::assertInstanceOf(Carbon::class, $data['published_at']);
    }

    public function test_display_views_offset_must_be_a_non_negative_integer(): void
    {
        $request = SaveVideoRequest::create('/admin/videos', 'POST', [
            'display_views_offset' => 'invalid',
        ]);
        $request->setContainer($this->app);
        $request->setRedirector($this->app->make('redirect'));

        $this->expectException(ValidationException::class);

        $request->validateResolved();
    }
}
