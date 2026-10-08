<?php

namespace Tests\Feature\Identity;

use App\Domain\Identity\Models\User;
use App\Domain\Identity\PresetAvatars;
use App\Livewire\Front\Account\AccountInformationSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PresetAvatarSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_select_a_supplied_avatar_and_see_it_in_the_account(): void
    {
        $user = User::factory()->create(['mobile' => '09123456789']);

        Livewire::actingAs($user)->test(AccountInformationSection::class)
            ->assertSee('تغییر تصویر پروفایل')
            ->assertSee(PresetAvatars::url(7))
            ->set('first_name', '')
            ->set('preset_avatar', '7')
            ->call('saveAvatar')
            ->assertHasNoErrors()
            ->assertRedirect(route('account.information'));

        $this->assertSame(7, $user->fresh()->preset_avatar);
        $this->assertSame(PresetAvatars::url(7), $user->fresh()->profileAvatarUrl());

        $this->actingAs($user)->get(route('account.information'))
            ->assertOk()
            ->assertSee(PresetAvatars::url(7))
            ->assertSee('آواتار پروفایل با موفقیت ذخیره شد.')
            ->assertSee(route('account.information').'#account-avatar');
    }

    public function test_user_cannot_save_an_avatar_outside_the_supplied_set(): void
    {
        $user = User::factory()->create(['mobile' => '09123456789']);

        Livewire::actingAs($user)->test(AccountInformationSection::class)
            ->set('preset_avatar', '13')
            ->call('saveAvatar')
            ->assertHasErrors(['preset_avatar' => 'in'])
            ->assertSee('آواتار انتخاب‌شده معتبر نیست.');

        $this->assertNull($user->fresh()->preset_avatar);
    }

    public function test_profile_save_refreshes_the_account_page_with_success_feedback(): void
    {
        $user = User::factory()->create(['mobile' => '09123456789']);

        Livewire::actingAs($user)->test(AccountInformationSection::class)
            ->set('first_name', 'نام تازه')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('account.information'));

        $this->assertSame('نام تازه', $user->fresh()->first_name);
        $this->actingAs($user)->get(route('account.information'))
            ->assertOk()
            ->assertSee('اطلاعات حساب با موفقیت ذخیره شد.');
    }
}
