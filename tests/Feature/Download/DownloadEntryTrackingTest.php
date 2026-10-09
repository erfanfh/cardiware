<?php

namespace Tests\Feature\Download;

use App\Http\Middleware\HandleSeoRedirectsMiddleware;
use App\Http\Middleware\RecordDailyLoyaltyVisit;
use App\Infrastructure\Support\Counters\DownloadClickCounter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Js;
use RuntimeException;
use Tests\Support\CreatesDownloadEntries;
use Tests\TestCase;

class DownloadEntryTrackingTest extends TestCase
{
    use CreatesDownloadEntries, RefreshDatabase;

    public function test_signed_click_post_records_without_a_session_redirect_or_sql_write(): void
    {
        $entry = $this->createDownloadEntry();
        $this->mock(DownloadClickCounter::class)->shouldReceive('record')->once()->with($entry->id, '192.0.2.10');
        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->call('POST', $this->trackingUrl($entry->public_id), server: [
            'REMOTE_ADDR' => '192.0.2.10',
        ]);

        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        $response->assertNoContent()->assertHeaderMissing('Location')->assertHeaderMissing('Set-Cookie');
        $response->assertHeader('Cache-Control', 'max-age=0, no-store, private');
        $this->assertCount(1, $queries);
        $this->assertStringStartsWith('select ', strtolower($queries[0]['query']));
        $this->assertSame(0, $entry->fresh()->download_click_count);
    }

    public function test_a_redis_error_is_ignored_by_the_click_endpoint(): void
    {
        $entry = $this->createDownloadEntry();
        $this->mock(DownloadClickCounter::class)
            ->shouldReceive('record')->once()->andThrow(new RuntimeException('Redis unavailable'));

        $this->post($this->trackingUrl($entry->public_id))->assertNoContent()->assertHeaderMissing('Location');
    }

    public function test_get_head_unsigned_and_tampered_click_requests_are_not_counted(): void
    {
        $entry = $this->createDownloadEntry();
        $this->mock(DownloadClickCounter::class)->shouldNotReceive('record');

        $url = $this->trackingUrl($entry->public_id);
        $this->get($url, ['Purpose' => 'prefetch'])->assertStatus(405);
        $this->head($url)->assertStatus(405);
        $this->post(route('downloads.entry.track', $entry->public_id))->assertForbidden();
        $this->post($url.'&signature=invalid')->assertForbidden();
        $this->post(str_replace($entry->public_id, 'another-entry', $url))->assertForbidden();
    }

    public function test_invalid_text_deleted_and_unpublished_links_are_not_counted(): void
    {
        $this->mock(DownloadClickCounter::class)->shouldNotReceive('record');
        $text = $this->createDownloadEntry(['type' => 'text', 'content' => 'Instructions']);
        $this->post($this->trackingUrl($text->public_id))->assertNotFound();
        $this->post($this->trackingUrl('missing-entry'))->assertNotFound();

        $deleted = $this->createDownloadEntry();
        $deleted->delete();
        $this->post($this->trackingUrl($deleted->public_id))->assertNotFound();

        $entry = $this->createDownloadEntry();
        $entry->section->delete();
        $this->post($this->trackingUrl($entry->public_id))->assertNotFound();

        $unpublished = $this->createDownloadEntry();
        DB::table('posts')->where('id', $unpublished->section->post_id)->update(['status' => 'draft']);
        $this->post($this->trackingUrl($unpublished->public_id))->assertNotFound();
    }

    public function test_download_box_keeps_direct_urls_and_tracks_both_anchors_separately(): void
    {
        $entry = $this->createDownloadEntry();
        $post = $entry->section->post;
        $html = view('front.posts.partials.detail.download-box', compact('post'))->render();
        $this->assertSame(2, substr_count($html, 'href="'.e($entry->content).'"'));
        $this->assertSame(2, substr_count($html, 'data-click-tracking-url="'.e($this->trackingUrl($entry->public_id)).'"'));
        $this->assertSame(2, substr_count($html, '@click="trackClick('));
        $this->assertSame(2, substr_count($html, '@auxclick="trackClick('));
        $this->assertStringContainsString('copyText('.Js::from($entry->content)->toHtml(), $html);
        $this->assertStringNotContainsString('trackClick.prevent', $html);
        $this->assertStringNotContainsString('@click.prevent', $html);
    }

    public function test_analytics_route_has_no_browser_session_or_page_tracking_middleware(): void
    {
        $route = app('router')->getRoutes()->getByName('downloads.entry.track');
        $middleware = app('router')->gatherRouteMiddleware($route);

        $this->assertNotContains(StartSession::class, $middleware);
        $this->assertNotContains(HandleSeoRedirectsMiddleware::class, $middleware);
        $this->assertNotContains(RecordDailyLoyaltyVisit::class, $middleware);
    }

    private function trackingUrl(string $publicId): string
    {
        return URL::signedRoute('downloads.entry.track', ['publicId' => $publicId], absolute: false);
    }
}
