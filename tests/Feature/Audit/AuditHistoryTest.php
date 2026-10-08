<?php

namespace Tests\Feature\Audit;

use App\Domain\Audit\Enums\AuditEventTypeEnum;
use App\Domain\Audit\Models\AuditEvent;
use App\Domain\Identity\Models\Permission;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Marketplace\Models\Store;
use App\Domain\Marketplace\Models\StoreUser;
use App\Infrastructure\Services\Audit\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuditHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_history_records_actor_and_only_changed_update_fields(): void
    {
        $actor = User::factory()->create();
        $this->actingAs($actor);

        $role = Role::create(['title' => 'Original']);
        $role->update(['title' => 'Changed']);
        $role->delete();

        $events = AuditEvent::query()->where('subject_type', $role->getMorphClass())
            ->where('subject_id', $role->getKey())->orderBy('id')->get();

        $this->assertSame([
            AuditEventTypeEnum::CREATED,
            AuditEventTypeEnum::UPDATED,
            AuditEventTypeEnum::DELETED,
        ], $events->pluck('type')->all());
        $this->assertSame($actor->getKey(), $events[1]->user_id);
        $this->assertSame($actor->display_name, $events[1]->actor_name);
        $this->assertNotNull($events[1]->created_at);
        $this->assertSame(['title' => 'Original'], $events[1]->old_data);
        $this->assertSame(['title' => 'Changed'], $events[1]->new_data);
    }

    public function test_pivot_changes_are_recorded_without_unchanged_relations(): void
    {
        $role = Role::create(['title' => 'Editor']);
        $permission = Permission::create(['group' => 'content', 'ability' => 'edit', 'label' => 'Edit']);
        $audit = app(AuditService::class);

        $audit->sync($role, 'permissions', [$permission->getKey()]);
        $audit->sync($role, 'permissions', [$permission->getKey()]);
        $audit->sync($role, 'permissions', []);

        $events = AuditEvent::query()->where('subject_type', $role->getMorphClass())
            ->where('subject_id', $role->getKey())->whereNotNull('meta')->orderBy('id')->get();

        $this->assertCount(2, $events);
        $this->assertSame(AuditEventTypeEnum::ATTACHED, $events[0]->type);
        $this->assertSame(AuditEventTypeEnum::DETACHED, $events[1]->type);
        $this->assertSame('permissions', $events[0]->meta['relation']);
    }

    public function test_hidden_credentials_are_not_logged(): void
    {
        $user = User::factory()->create();
        $user->update(['first_name' => 'Changed', 'password' => 'another-password']);

        $event = AuditEvent::query()->where('subject_type', $user->getMorphClass())
            ->where('subject_id', $user->getKey())->where('type', 'updated')->latest('id')->firstOrFail();

        $this->assertSame(['first_name' => 'Changed'], $event->new_data);
        $this->assertArrayNotHasKey('password', $event->old_data);
    }

    public function test_soft_delete_and_restore_have_distinct_events(): void
    {
        $user = User::factory()->create();
        $user->delete();
        $user->restore();

        $types = AuditEvent::query()->where('subject_type', $user->getMorphClass())
            ->where('subject_id', $user->getKey())->orderBy('id')->pluck('type')->all();

        $this->assertSame([
            AuditEventTypeEnum::CREATED,
            AuditEventTypeEnum::DELETED,
            AuditEventTypeEnum::RESTORED,
        ], $types);
    }

    public function test_audit_row_rolls_back_with_the_model_change(): void
    {
        try {
            DB::transaction(function (): void {
                Role::create(['title' => 'Rolled back']);
                throw new \RuntimeException('rollback');
            });
        } catch (\RuntimeException) {
        }

        $this->assertDatabaseMissing('roles', ['title' => 'Rolled back']);
        $this->assertSame(0, AuditEvent::query()->count());
    }

    public function test_actor_identity_survives_force_deletion(): void
    {
        $actor = User::factory()->create();
        $this->actingAs($actor);
        $role = Role::create(['title' => 'Temporary']);
        $actor->forceDelete();

        $event = AuditEvent::query()->where('subject_type', $role->getMorphClass())
            ->where('subject_id', $role->getKey())->firstOrFail();

        $this->assertSame($actor->getKey(), $event->user_id);
        $this->assertSame($actor->display_name, $event->actor_name);
    }

    public function test_pivot_update_keeps_only_changed_pivot_columns(): void
    {
        $store = Store::create(['title' => 'Shop', 'slug' => 'shop', 'status' => 'active']);
        $seller = User::factory()->create();
        $audit = app(AuditService::class);

        $audit->sync($store, 'sellers', [$seller->getKey() => ['joined_at' => '2026-01-01 00:00:00']]);
        $audit->sync($store, 'sellers', [$seller->getKey() => ['joined_at' => '2026-02-01 00:00:00']]);

        $event = AuditEvent::query()->where('subject_type', $store->getMorphClass())
            ->where('subject_id', $store->getKey())->where('type', 'updated')->firstOrFail();

        $this->assertSame(['joined_at' => '2026-01-01 00:00:00'], $event->old_data);
        $this->assertSame(['joined_at' => '2026-02-01 00:00:00'], $event->new_data);
    }

    public function test_subject_key_accepts_uuid_identifiers(): void
    {
        $id = '123e4567-e89b-12d3-a456-426614174000';
        $event = AuditEvent::create([
            'type' => AuditEventTypeEnum::CREATED,
            'subject_type' => 'launcher_checkout_session',
            'subject_id' => $id,
        ]);

        $this->assertSame($id, $event->fresh()->subject_id);
    }

    public function test_dependent_domain_models_are_tracked_without_registration(): void
    {
        $store = Store::create(['title' => 'Shop', 'slug' => 'shop', 'status' => 'active']);
        $seller = User::factory()->create();
        $membership = StoreUser::create(['store_id' => $store->getKey(), 'user_id' => $seller->getKey()]);
        $membership->update(['joined_at' => '2026-01-01 00:00:00']);

        $events = AuditEvent::query()->where('subject_type', $membership->getMorphClass())
            ->where('subject_id', $membership->getKey())->orderBy('id')->get();

        $this->assertCount(2, $events);
        $this->assertSame(['joined_at' => null], $events[1]->old_data);
        $this->assertSame(['joined_at' => '2026-01-01 00:00:00'], $events[1]->new_data);
    }
}
