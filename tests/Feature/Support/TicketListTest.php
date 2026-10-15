<?php

namespace Tests\Feature\Support;

use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
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
}
