<?php

namespace Application\Identity;

use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EnsureAuthenticatedUserIsActiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_continue_using_the_system(): void
    {
        $user = User::factory()->create([
            'status' => UserStatusEnum::ACTIVE,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/');

        $response->assertSuccessful();

        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_user_is_logged_out_on_next_request(): void
    {
        $user = User::factory()->create([
            'status' => UserStatusEnum::ACTIVE,
        ]);

        $this->actingAs($user);

        $user->update([
            'status' => UserStatusEnum::INACTIVE,
        ]);

        $response = $this->get('/');

        $response->assertRedirect(
            route('login')
        );

        $this->assertGuest();

        $response->assertSessionHas(
            'error',
            'حساب کاربری شما غیرفعال شده است.'
        );
    }

    public function test_inactive_api_user_receives_forbidden_response(): void
    {
        $user = User::factory()->create([
            'status' => UserStatusEnum::INACTIVE,
        ]);

        $token = $user->createToken('test-token');

        $response = $this
            ->withToken($token->plainTextToken)
            ->getJson('/api/v1/me');

        $response
            ->assertForbidden()
            ->assertJson([
                'message' => 'حساب کاربری شما غیرفعال شده است.',
            ]);

        $this->assertDatabaseMissing(
            'personal_access_tokens',
            [
                'tokenable_id' => $user->getKey(),
                'tokenable_type' => $user->getMorphClass(),
            ]
        );
    }
}
