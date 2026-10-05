<?php

namespace Tests\Feature\Vip;

use App\Domain\Identity\Models\Permission;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Vip\Models\VipPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\TestCase;

class VipPlanManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $role = Role::factory()->create(['is_super_admin' => true]);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function fields(array $overrides = []): array
    {
        return array_replace([
            'code' => 'vip-90',
            'title' => 'VIP سه‌ماهه',
            'description' => 'دسترسی ویژه',
            'duration_days' => 90,
            'price_toman' => 250000,
            'is_active' => '1',
            'sort_order' => 10,
        ], $overrides);
    }

    public function test_admin_can_manage_plans_and_deleted_plans_leave_a_record(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('admin.vip-plans.index'))->assertOk();
        $this->get(route('admin.vip-plans.create'))->assertOk()->assertSee('name="code"', false);

        $this->post(route('admin.vip-plans.store'), $this->fields(['code' => ' VIP-90 ']))
            ->assertRedirect();

        $plan = VipPlan::query()->firstOrFail();
        $this->assertSame('vip-90', $plan->code);
        $this->assertSame(90, $plan->duration_days);
        $this->assertTrue($plan->is_active);

        $this->get(route('admin.vip-plans.edit', $plan))->assertOk()->assertDontSee('name="code"', false);
        $this->put(route('admin.vip-plans.update', $plan), Arr::except($this->fields([
            'title' => 'VIP شش‌ماهه',
            'duration_days' => 180,
            'is_active' => '0',
        ]), ['code']))->assertRedirect();

        $this->assertSame('VIP شش‌ماهه', $plan->fresh()->title);
        $this->assertFalse($plan->fresh()->is_active);
        $this->assertSame('vip-90', $plan->fresh()->code);
        $this->assertSame(0, VipPlan::available()->count());

        $this->delete(route('admin.vip-plans.destroy', $plan))->assertRedirect();
        $this->assertSoftDeleted('vip_plans', ['id' => $plan->id]);
    }

    public function test_invalid_and_duplicate_plan_values_are_rejected(): void
    {
        $this->actingAs($this->admin());
        VipPlan::query()->create($this->fields(['is_active' => true]));

        $this->post(route('admin.vip-plans.store'), $this->fields([
            'duration_days' => 0,
            'price_toman' => -1,
        ]))->assertSessionHasErrors(['code', 'duration_days', 'price_toman']);

        $this->assertSame(1, VipPlan::query()->count());
    }

    public function test_code_cannot_be_changed_through_an_update_request(): void
    {
        $this->actingAs($this->admin());
        $plan = VipPlan::query()->create($this->fields());

        $this->put(route('admin.vip-plans.update', $plan), $this->fields([
            'code' => 'vip-renamed',
            'title' => 'عنوان جدید',
        ]))->assertSessionHasErrors('code');

        $this->assertSame('vip-90', $plan->fresh()->code);
        $this->assertSame('VIP سه‌ماهه', $plan->fresh()->title);
    }

    public function test_user_without_vip_plan_permissions_cannot_manage_plans(): void
    {
        $role = Role::factory()->create();
        $user = User::factory()->create(['role_id' => $role->id]);
        $this->actingAs($user);

        $this->get(route('admin.vip-plans.index'))->assertRedirect(route('account.dashboard'));
        $this->post(route('admin.vip-plans.store'), $this->fields())
            ->assertRedirect(route('account.dashboard'));
        $this->assertDatabaseCount('vip_plans', 0);
    }

    public function test_admin_with_list_only_permission_can_view_but_cannot_change_plans(): void
    {
        $role = Role::factory()->create();
        $role->permissions()->attach([
            Permission::query()->create(['group' => 'admin', 'ability' => 'display', 'label' => 'پنل'])->id,
            Permission::query()->create(['group' => 'vip-plan', 'ability' => 'list', 'label' => 'لیست'])->id,
        ]);
        $user = User::factory()->create(['role_id' => $role->id]);
        $this->actingAs($user);

        $this->get(route('admin.vip-plans.index'))
            ->assertOk()
            ->assertDontSee('اعمال عملیات گروهی');
        $this->get(route('admin.vip-plans.create'))->assertForbidden();
        $this->post(route('admin.vip-plans.store'), $this->fields())->assertForbidden();
        $this->post(route('admin.vip-plans.bulk-action'), [
            'bulk_action' => 'activate', 'ids' => [1],
        ])->assertForbidden();
        $this->assertDatabaseCount('vip_plans', 0);
    }

    public function test_list_filters_and_sorts_plans(): void
    {
        $this->actingAs($this->admin());
        VipPlan::query()->create($this->fields([
            'code' => 'vip-short', 'title' => 'پلن کوتاه', 'duration_days' => 30,
            'price_toman' => 100000, 'is_active' => true, 'sort_order' => 20,
        ]));
        VipPlan::query()->create($this->fields([
            'code' => 'vip-long', 'title' => 'پلن بلند', 'duration_days' => 180,
            'price_toman' => 500000, 'is_active' => false, 'sort_order' => 10,
        ]));

        $this->get(route('admin.vip-plans.index', [
            'is_active' => '0', 'duration_days_min' => 90,
            'price_toman_max' => 600000,
        ]))->assertOk()->assertSee('پلن بلند')->assertDontSee('پلن کوتاه');

        $this->get(route('admin.vip-plans.index', [
            'code' => 'short', 'title' => 'کوتاه',
        ]))->assertOk()->assertSee('vip-short')->assertDontSee('vip-long');

        $this->get(route('admin.vip-plans.index', [
            'sort' => 'price_toman', 'direction' => 'asc',
        ]))->assertOk()->assertSeeInOrder(['پلن کوتاه', 'پلن بلند']);
    }

    public function test_bulk_actions_change_status_and_soft_delete_selected_plans(): void
    {
        $this->actingAs($this->admin());
        $first = VipPlan::query()->create($this->fields(['code' => 'vip-first', 'is_active' => false]));
        $second = VipPlan::query()->create($this->fields(['code' => 'vip-second', 'is_active' => false]));

        $this->post(route('admin.vip-plans.bulk-action'), [
            'bulk_action' => 'activate', 'ids' => [$first->id, $second->id],
        ])->assertRedirect(route('admin.vip-plans.index'));
        $this->assertSame(2, VipPlan::available()->count());

        $this->post(route('admin.vip-plans.bulk-action'), [
            'bulk_action' => 'deactivate', 'ids' => [$first->id],
        ])->assertRedirect(route('admin.vip-plans.index'));
        $this->assertSame(1, VipPlan::available()->count());

        $this->post(route('admin.vip-plans.bulk-action'), [
            'bulk_action' => 'delete', 'ids' => [$first->id],
        ])->assertRedirect(route('admin.vip-plans.index'));
        $this->assertSoftDeleted('vip_plans', ['id' => $first->id]);
        $this->assertNotSoftDeleted('vip_plans', ['id' => $second->id]);

        $this->post(route('admin.vip-plans.bulk-action'), [
            'bulk_action' => 'delete', 'ids' => [$first->id],
        ])->assertSessionHasErrors('ids.0');
    }
}
