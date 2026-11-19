<?php

namespace Tests\Feature\Support;

use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use App\Domain\Support\Models\Ticket;
use App\Domain\Support\Models\TicketDepartment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TicketListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::factory()->create(['is_super_admin' => true]);
        $user = User::factory()->create(['role_id' => $role->id]);
        $this->actingAs($user);

        DB::table('tickets')->insert([
            ['id' => 1, 'number' => 1001, 'user_id' => $user->id, 'title' => 'First ticket', 'status' => 'open'],
            ['id' => 2, 'number' => 1002, 'user_id' => $user->id, 'title' => 'Second ticket', 'status' => 'open'],
        ]);
    }

    #[DataProvider('ticketNumbers')]
    public function test_admin_can_search_by_ticket_number(string $number): void
    {
        $this->get(route('admin.tickets.index', [
            'number' => $number,
            'user_name' => '',
            'user_mobile' => '',
            'user_email' => '',
            'title' => '',
            'message' => '',
            'status' => '',
            'department_id' => '',
            'product_id' => '',
            'created_at_from' => '',
            'created_at_to' => '',
            'last_message_at_from' => '',
            'last_message_at_to' => '',
        ]))
            ->assertOk()
            ->assertViewHas('tickets', fn ($tickets) => $tickets->pluck('number')->all() === [1001])
            ->assertSee('First ticket')
            ->assertDontSee('Second ticket');
    }

    public static function ticketNumbers(): array
    {
        return [
            'English digits' => ['1001'],
            'Persian digits' => ['۱۰۰۱'],
            'Arabic digits' => ['١٠٠١'],
            'copied number' => ['#1001'],
            'copied Persian number with spaces' => [' # ۱۰۰۱ '],
        ];
    }

    public function test_unknown_ticket_number_returns_no_results(): void
    {
        $this->get(route('admin.tickets.index', ['number' => '9999']))
            ->assertOk()
            ->assertViewHas('tickets', fn ($tickets) => $tickets->isEmpty());
    }

    public function test_invalid_ticket_number_displays_validation_error_and_keeps_input(): void
    {
        $url = route('admin.tickets.index');

        $this->from($url)->get(route('admin.tickets.index', ['number' => '1001x']))
            ->assertRedirect($url)
            ->assertSessionHasErrors('number');

        $this->get($url)
            ->assertOk()
            ->assertSee('شماره وارد شده معتبر نیست.')
            ->assertSee('value="1001x"', false);
    }

    public function test_review_status_can_be_set_filtered_and_cleared(): void
    {
        $ticket = Ticket::query()->findOrFail(1);
        $list = route('admin.tickets.index', ['under_review' => 1]);
        $endpoint = route('admin.tickets.under-review', $ticket);

        $this->from($list)->post($endpoint, ['mark' => '1'])->assertRedirect($list);
        $this->assertNotNull($ticket->fresh()->under_review_marked_at);
        $this->get($list)->assertOk()->assertViewHas('tickets', fn ($tickets) => $tickets->pluck('number')->all() === [1001]);

        $this->from($list)->post($endpoint, ['mark' => '0'])->assertRedirect($list);
        $this->assertNull($ticket->fresh()->under_review_marked_at);
        $this->get($list)->assertOk()->assertViewHas('tickets', fn ($tickets) => $tickets->isEmpty());
    }

    public function test_only_active_vip_can_skip_the_product_for_departments_that_require_it(): void
    {
        $department = TicketDepartment::query()->create(['title' => 'Product support', 'requires_product' => true, 'is_active' => true]);
        $member = User::factory()->create();
        $payload = ['department_id' => $department->id, 'title' => 'Help with my account', 'message' => 'Please help me with this issue.'];

        $this->actingAs($member)->post(route('account.tickets.store'), $payload)
            ->assertSessionHasErrorsIn('createTicket', 'product_id');
        $member->vipSubscriptions()->create([
            'source' => 'gift', 'duration_days' => 30,
            'starts_at' => now()->subDay(), 'expires_at' => now()->addDays(29),
        ]);
        $this->post(route('account.tickets.store'), $payload)->assertRedirect(route('account.tickets'));
        $this->assertDatabaseHas('tickets', ['user_id' => $member->id, 'department_id' => $department->id, 'product_id' => null]);
    }

    public function test_free_product_tickets_follow_other_tickets_in_the_same_priority_group(): void
    {
        $store = DB::table('stores')->insertGetId(['title' => 'Store', 'slug' => 'support-store', 'status' => 'active']);
        $product = DB::table('products')->insertGetId([
            'store_id' => $store, 'title' => 'Free product', 'slug' => 'free-product',
            'type' => 'digital', 'structure' => 'simple', 'status' => 'active', 'published_at' => now(),
        ]);
        DB::table('product_sale_items')->insert(['product_id' => $product, 'base_price' => 0, 'status' => 'available']);
        Ticket::query()->findOrFail(2)->update(['product_id' => $product, 'last_message_at' => now()]);

        $this->get(route('admin.tickets.index'))->assertOk()
            ->assertViewHas('tickets', fn ($tickets) => $tickets->pluck('number')->all() === [1001, 1002])
            ->assertSee('محصول رایگان');
    }

    public function test_vip_priority_ends_when_subscription_expires(): void
    {
        $vip = User::factory()->create();
        Ticket::query()->findOrFail(1)->update(['user_id' => $vip->id]);
        $subscription = $vip->vipSubscriptions()->create([
            'source' => 'gift',
            'duration_days' => 30,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDays(29),
        ]);

        $this->get(route('admin.tickets.index'))->assertOk()
            ->assertViewHas('tickets', fn ($tickets) => $tickets->pluck('number')->all() === [1001, 1002]);
        $subscription->update(['expires_at' => now()->subSecond()]);
        $this->get(route('admin.tickets.index'))->assertOk()
            ->assertViewHas('tickets', fn ($tickets) => $tickets->pluck('number')->all() === [1002, 1001]);
    }
}
