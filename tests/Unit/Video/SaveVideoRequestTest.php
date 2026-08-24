<?php

namespace Tests\Unit\Video;

use App\Http\Requests\Admin\Video\SaveVideoRequest;
use Illuminate\Support\Carbon;
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
        self::assertInstanceOf(Carbon::class, $data['published_at']);
    }
}
