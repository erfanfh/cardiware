<?php

namespace Tests\Feature\Notification;

use App\Application\Notification\GetVisiblePopupsAction;
use App\Domain\Identity\Models\User;
use App\Domain\Notification\Models\Popup;
use App\Http\Controllers\Admin\Notification\PopupBulkActionController;
use App\Http\Controllers\Admin\Notification\PopupController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PopupVisibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('popups', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body')->nullable();
            $table->unsignedBigInteger('image_id')->nullable();
            $table->string('target_type');
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('audience');
            $table->unsignedInteger('delay_seconds');
            $table->unsignedInteger('close_minutes');
            $table->unsignedInteger('hide_days');
            $table->boolean('is_active');
            $table->timestamps();
        });
    }

    public function test_specific_targets_and_audiences_are_filtered(): void
    {
        $this->popup('Everywhere', 'all', null, 'all');
        $this->popup('Post 5', 'post', 5, 'all');
        $this->popup('Post 6', 'post', 6, 'all');
        $this->popup('All posts', 'post', null, 'all');
        $this->popup('Guest', 'post', 5, 'guest');
        $this->popup('Signed in', 'post', 5, 'authenticated');
        $this->popup('VIP', 'post', 5, 'vip');
        $this->popup('Inactive', 'post', 5, 'all', false);

        $action = new GetVisiblePopupsAction();
        self::assertSame(
            ['Everywhere', 'Post 5', 'All posts', 'Guest'],
            $action->execute('post', 5, null)->pluck('title')->all()
        );

        $ordinary = new class extends User {
            public function hasActiveVipSubscription(): bool { return false; }
        };
        self::assertSame(
            ['Everywhere', 'Post 5', 'All posts', 'Signed in'],
            $action->execute('post', 5, $ordinary)->pluck('title')->all()
        );

        $vip = new class extends User {
            public function hasActiveVipSubscription(): bool { return true; }
        };
        self::assertSame(
            ['Everywhere', 'Post 5', 'All posts', 'Signed in', 'VIP'],
            $action->execute('post', 5, $vip)->pluck('title')->all()
        );
    }

    public function test_popup_markup_escapes_content_and_exposes_both_dismiss_durations(): void
    {
        $this->popup('<script>alert(1)</script>', 'home', null, 'all');

        $html = view('front.notification.popups', ['popups' => Popup::query()->get()])->render();

        self::assertStringNotContainsString('<script>alert(1)</script>', $html);
        self::assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        self::assertStringContainsString('data-popup-dismiss="later"', $html);
        self::assertStringContainsString('data-popup-dismiss="long"', $html);
        self::assertStringContainsString('data-popup-close-minutes="60"', $html);
        self::assertStringContainsString('data-popup-hide-days="30"', $html);
    }

    public function test_admin_routes_are_registered(): void
    {
        self::assertNotNull(Route::getRoutes()->getByName('admin.popups.index'));
        self::assertNotNull(Route::getRoutes()->getByName('admin.popups.store'));
        self::assertNotNull(Route::getRoutes()->getByName('admin.popups.update'));
        self::assertNotNull(Route::getRoutes()->getByName('admin.popups.targets'));
        self::assertNotNull(Route::getRoutes()->getByName('admin.popups.bulk-action'));
    }

    public function test_admin_list_filters_and_bulk_activation(): void
    {
        $wanted = $this->popup('Welcome guests', 'home', null, 'guest', false);
        $this->popup('Other notice', 'home', null, 'guest');
        $this->popup('Welcome members', 'home', null, 'authenticated', false);

        Gate::shouldReceive('authorize')->with('popup.list')->once();
        Gate::shouldReceive('authorize')->with('popup.edit')->once();
        Gate::shouldReceive('allows')->andReturn(true);

        $view = app(PopupController::class)->index(Request::create('/admin/popups', 'GET', [
            'title' => 'Welcome', 'audience' => 'guest', 'is_active' => '0',
        ]));
        self::assertSame([$wanted->id], $view->getData()['popups']->pluck('id')->all());
        self::assertTrue($view->getData()['hasActiveFilters']);

        app(PopupBulkActionController::class)(Request::create('/admin/popups/bulk-action', 'POST', [
            'bulk_action' => 'activate', 'ids' => [$wanted->id],
        ]));
        self::assertTrue($wanted->fresh()->is_active);
    }

    private function popup(string $title, string $targetType, ?int $targetId, string $audience, bool $active = true): Popup
    {
        return Popup::create([
            'title' => $title,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'audience' => $audience,
            'delay_seconds' => 0,
            'close_minutes' => 60,
            'hide_days' => 30,
            'is_active' => $active,
        ]);
    }
}
