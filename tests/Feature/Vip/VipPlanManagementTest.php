<?php

namespace Tests\Feature\Vip;

use App\Domain\Identity\Models\Permission;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Vip\Models\VipPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $this->get(route('admin.vip-plans.create'))->assertOk();

        $this->post(route('admin.vip-plans.store'), $this->fields(['code' => ' VIP-90 ']))
            ->assertRedirect();

        $plan = VipPlan::query()->firstOrFail();
        $this->assertSame('vip-90', $plan->code);
        $this->assertSame(90, $plan->duration_days);
        $this->assertTrue($plan->is_active);

        $this->get(route('admin.vip-plans.edit', $plan))->assertOk();
        $this->put(route('admin.vip-plans.update', $plan), $this->fields([
            'title' => 'VIP شش‌ماهه',
            'duration_days' => 180,
            'is_active' => '0',
        ]))->assertRedirect();

        $this->assertSame('VIP شش‌ماهه', $plan->fresh()->title);
        $this->assertFalse($plan->fresh()->is_active);
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

        $this->get(route('admin.vip-plans.index'))->assertOk();
        $this->get(route('admin.vip-plans.create'))->assertForbidden();
        $this->post(route('admin.vip-plans.store'), $this->fields())->assertForbidden();
        $this->assertDatabaseCount('vip_plans', 0);
    }
}
