<?php

namespace Tests\Feature\Vip;

use App\Domain\Identity\Models\Permission;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Vip\Models\VipPlan;
use App\Http\Middleware\ForceTrailingSlash;
use App\Infrastructure\Services\Setting\Groups\VipSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VipPageSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $role = Role::factory()->create(['is_super_admin' => true]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function plan(string $code, string $title, bool $active = true): VipPlan
    {
        return VipPlan::query()->create([
            'code' => $code,
            'title' => $title,
            'duration_days' => 30,
            'price_toman' => 100000,
            'is_active' => $active,
            'sort_order' => 0,
        ]);
    }

    private function fields(?int $featuredPlanId): array
    {
        return array_replace(VipSettings::DEFAULT_HERO, [
            'hero_badge' => 'باشگاه ویژه',
            'hero_title' => 'عنوان جدید',
            'hero_highlight' => 'بخش رنگی جدید',
            'hero_description' => 'توضیح جدید برای اشتراک ویژه',
            'hero_button' => 'پلن‌ها را ببینید',
            'hero_benefit_1' => 'مزیت اول',
            'hero_benefit_2' => 'مزیت دوم',
            'hero_benefit_3' => 'مزیت سوم',
            'featured_plan_id' => $featuredPlanId ?? '',
        ]);
    }

    public function test_hero_is_editable_and_no_plan_is_featured_by_default(): void
    {
        $first = $this->plan('vip-first', 'پلن اول');
        $second = $this->plan('vip-second', 'پلن دوم');
        $this->actingAs($this->admin());
        $this->withoutMiddleware(ForceTrailingSlash::class);

        $this->get(route('vip.index'))->assertOk()->assertDontSee('انتخاب ویژه');
        $this->get(route('admin.vip-plans.page-settings'))
            ->assertOk()
            ->assertSee('بدون پلن ویژه')
            ->assertSee(VipSettings::DEFAULT_HERO['hero_title']);

        $this->put(route('admin.vip-plans.page-settings.update'), $this->fields($second->id))
            ->assertRedirect(route('admin.vip-plans.page-settings'));

        $this->assertSame((int) $second->id, (int) app(VipSettings::class)->get('featured_plan_id'));
        $this->get(route('vip.index'))
            ->assertOk()
            ->assertSee('عنوان جدید')
            ->assertSee('بخش رنگی جدید')
            ->assertSee('پلن‌ها را ببینید')
            ->assertSee('مزیت سوم')
            ->assertSee('انتخاب ویژه');
        $this->get(route('admin.vip-plans.index'))->assertSee('پلن ویژه');

        $this->put(route('admin.vip-plans.page-settings.update'), $this->fields(null))->assertRedirect();
        $this->assertNull(app(VipSettings::class)->get('featured_plan_id'));
        $this->get(route('vip.index'))->assertDontSee('انتخاب ویژه');
        $this->assertSame(2, VipPlan::query()->whereKey([$first->id, $second->id])->count());
    }

    public function test_inactive_plan_cannot_be_selected_as_featured(): void
    {
        $inactive = $this->plan('vip-inactive', 'پلن غیرفعال', false);
        $this->actingAs($this->admin());

        $this->put(route('admin.vip-plans.page-settings.update'), $this->fields($inactive->id))
            ->assertSessionHasErrors('featured_plan_id');
        $this->assertNull(app(VipSettings::class)->get('featured_plan_id'));
    }

    public function test_list_only_admin_cannot_edit_vip_page_settings(): void
    {
        $role = Role::factory()->create();
        $role->permissions()->attach([
            Permission::query()->create(['group' => 'admin', 'ability' => 'display', 'label' => 'پنل'])->id,
            Permission::query()->create(['group' => 'vip-plan', 'ability' => 'list', 'label' => 'لیست'])->id,
        ]);
        $this->actingAs(User::factory()->create(['role_id' => $role->id]));

        $this->get(route('admin.vip-plans.page-settings'))->assertForbidden();
        $this->put(route('admin.vip-plans.page-settings.update'), $this->fields(null))->assertForbidden();
    }
}
