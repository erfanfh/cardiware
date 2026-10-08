<?php

namespace Tests\Feature\Audit;

use App\Domain\Audit\Models\AuditEvent;
use App\Domain\Identity\Models\Permission;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Http\Middleware\ForceTrailingSlash;
use App\Infrastructure\Services\Audit\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuditHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_admin_can_filter_and_inspect_changes(): void
    {
        $admin = User::factory()->create(['role_id' => Role::factory()->create(['is_super_admin' => true])->id]);
        $this->actingAs($admin)->withoutMiddleware(ForceTrailingSlash::class);

        $role = Role::create(['title' => 'نقش اولیه']);
        $role->update(['title' => 'نقش جدید']);
        $event = AuditEvent::query()->where('subject_type', $role->getMorphClass())
            ->where('subject_id', $role->getKey())->where('type', 'updated')->firstOrFail();

        $this->get(route('admin.audit-events.index', [
            'type' => 'updated', 'subject_type' => $role->getMorphClass(),
            'subject_id' => $role->getKey(), 'actor_id' => $admin->getKey(),
        ]))->assertOk()->assertSee('نقش جدید')->assertSee('ویرایش');

        $date = jalali_date($event->created_at, 'Y/m/d');
        $this->get(route('admin.audit-events.index', ['from' => $date, 'to' => $date]))
            ->assertOk()->assertSee('نقش جدید');

        $this->get(route('admin.audit-events.show', $event))
            ->assertOk()->assertSee('نقش اولیه')->assertSee('نقش جدید')
            ->assertSee($admin->display_name)->assertSee('عنوان');
    }

    public function test_audit_access_is_separate_from_general_reports(): void
    {
        $role = Role::factory()->create();
        $permission = Permission::firstOrCreate(
            ['group' => 'report', 'ability' => 'view'],
            ['label' => 'مشاهده گزارش‌ها'],
        );
        $role->permissions()->attach($permission);
        $role->permissions()->attach(Permission::firstOrCreate(
            ['group' => 'admin', 'ability' => 'display'],
            ['label' => 'ورود به پنل مدیریت'],
        ));
        $admin = User::factory()->create(['role_id' => $role->id]);
        $this->actingAs($admin)->withoutMiddleware(ForceTrailingSlash::class);

        $this->get(route('admin.audit-events.index'))->assertForbidden();
        $this->get(route('admin.audit-events.show', 1))->assertForbidden();

        $auditPermission = Permission::query()->where('group', 'audit')->where('ability', 'view')->firstOrFail();
        $role->permissions()->attach($auditPermission);
        $this->get(route('admin.audit-events.index'))->assertOk();
    }

    public function test_values_are_escaped_and_invalid_filters_are_rejected(): void
    {
        $admin = User::factory()->create(['role_id' => Role::factory()->create(['is_super_admin' => true])->id]);
        $this->actingAs($admin)->withoutMiddleware(ForceTrailingSlash::class);
        $role = Role::create(['title' => '<script>alert(1)</script>']);
        $event = AuditEvent::query()->where('subject_type', $role->getMorphClass())
            ->where('subject_id', $role->getKey())->firstOrFail();

        $this->get(route('admin.audit-events.show', $event))
            ->assertOk()->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
        $this->get(route('admin.audit-events.index', ['type' => 'invalid']))->assertSessionHasErrors('type');
        $this->get(route('admin.audit-events.index', ['from' => '1405/13/01']))->assertSessionHasErrors('from');
    }

    public function test_pivot_detail_and_deleted_subject_remain_understandable(): void
    {
        $admin = User::factory()->create(['role_id' => Role::factory()->create(['is_super_admin' => true])->id]);
        $this->actingAs($admin)->withoutMiddleware(ForceTrailingSlash::class);

        $role = Role::create(['title' => 'نقش نمونه']);
        $permission = Permission::query()->where('group', 'audit')->where('ability', 'view')->firstOrFail();
        app(AuditService::class)->sync($role, 'permissions', [$permission->id]);
        $pivotEvent = AuditEvent::query()->where('subject_type', $role->getMorphClass())
            ->where('subject_id', $role->getKey())->where('type', 'attached')->firstOrFail();

        $this->get(route('admin.audit-events.show', $pivotEvent))
            ->assertOk()->assertSee('اتصال رابطه')->assertSee('مجوز')
            ->assertSee('نقش نمونه')->assertSee('شناسه مفهوم مرتبط');

        $role->delete();
        $deletedEvent = AuditEvent::query()->where('subject_type', $role->getMorphClass())
            ->where('subject_id', $role->getKey())->where('type', 'deleted')->firstOrFail();
        $this->get(route('admin.audit-events.show', $deletedEvent))
            ->assertOk()->assertSee('نقش نمونه')->assertSee('حذف');
    }
}
