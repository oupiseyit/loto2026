<?php

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\BaseTestCase;

class AccountWebTest extends BaseTestCase
{
    public function test_account_page_renders_for_all_roles(): void
    {
        foreach ([$this->admin, $this->master, $this->staff] as $user) {
            $this->actingAs($user)
                 ->get(route('account'))
                 ->assertOk()
                 ->assertViewIs('account');
        }
    }

    public function test_guest_cannot_access_account(): void
    {
        $this->get(route('account'))->assertRedirect(route('login'));
    }

    public function test_admin_sees_staff_list_on_account_page(): void
    {
        $this->actingAsAdmin()
             ->get(route('account'))
             ->assertOk()
             ->assertSee($this->staff->name);
    }

    public function test_master_sees_own_staff_on_account_page(): void
    {
        $otherStaff = User::factory()->staff()->create(['created_by' => $this->admin->id]);

        $this->actingAsMaster()
             ->get(route('account'))
             ->assertOk()
             ->assertSee($this->staff->name)
             ->assertDontSee($otherStaff->name);
    }

    public function test_staff_cannot_see_staff_list(): void
    {
        $this->actingAsStaff()
             ->get(route('account'))
             ->assertOk()
             ->assertDontSee('Staff Accounts');
    }

    // --- Livewire: change password ---

    public function test_user_can_change_own_password(): void
    {
        $this->actingAsStaff();

        \Livewire\Livewire::test(\App\Livewire\AccountPage::class)
            ->set('currentPassword', 'password')
            ->set('newPassword', 'newpassword123')
            ->set('confirmPassword', 'newpassword123')
            ->call('changePassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('newpassword123', $this->staff->fresh()->password));
    }

    public function test_change_password_fails_with_wrong_current(): void
    {
        $this->actingAsStaff();

        \Livewire\Livewire::test(\App\Livewire\AccountPage::class)
            ->set('currentPassword', 'wrongpassword')
            ->set('newPassword', 'newpassword123')
            ->set('confirmPassword', 'newpassword123')
            ->call('changePassword')
            ->assertHasErrors(['currentPassword']);
    }

    public function test_change_password_fails_when_confirm_mismatch(): void
    {
        $this->actingAsStaff();

        \Livewire\Livewire::test(\App\Livewire\AccountPage::class)
            ->set('currentPassword', 'password')
            ->set('newPassword', 'newpassword123')
            ->set('confirmPassword', 'different456')
            ->call('changePassword')
            ->assertHasErrors(['confirmPassword']);
    }

    // --- Livewire: create staff ---

    public function test_admin_can_create_staff_via_livewire(): void
    {
        $this->actingAsAdmin();

        \Livewire\Livewire::test(\App\Livewire\AccountPage::class)
            ->set('newName', 'Test Staff')
            ->set('newUsername', 'teststaffx01')
            ->set('newStaffPass', 'password123')
            ->set('newStaffConfirm', 'password123')
            ->call('createStaff')
            ->assertHasNoErrors()
            ->assertSet('showCreateModal', false);

        $this->assertDatabaseHas('users', [
            'username'   => 'teststaffx01',
            'role'       => 'staff',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_create_staff_fails_on_duplicate_username(): void
    {
        $this->actingAsAdmin();

        \Livewire\Livewire::test(\App\Livewire\AccountPage::class)
            ->set('newName', 'Duplicate')
            ->set('newUsername', $this->staff->username)
            ->set('newStaffPass', 'password123')
            ->set('newStaffConfirm', 'password123')
            ->call('createStaff')
            ->assertHasErrors(['newUsername']);
    }

    // --- Livewire: edit staff ---

    public function test_admin_can_update_staff_via_livewire(): void
    {
        $this->actingAsAdmin();

        \Livewire\Livewire::test(\App\Livewire\AccountPage::class)
            ->call('openEdit', $this->staff->id)
            ->set('editName', 'Updated Name')
            ->set('editIsActive', true)
            ->call('updateStaff')
            ->assertHasNoErrors()
            ->assertSet('showEditModal', false);

        $this->assertDatabaseHas('users', ['id' => $this->staff->id, 'name' => 'Updated Name']);
    }

    public function test_master_cannot_edit_other_masters_staff(): void
    {
        $this->actingAsMaster();

        $otherStaff = User::factory()->staff()->create(['created_by' => $this->admin->id]);

        \Livewire\Livewire::test(\App\Livewire\AccountPage::class)
            ->call('openEdit', $otherStaff->id)
            ->assertForbidden();
    }

    // --- Livewire: delete staff ---

    public function test_admin_can_delete_staff(): void
    {
        $this->actingAsAdmin();

        \Livewire\Livewire::test(\App\Livewire\AccountPage::class)
            ->call('deleteStaff', $this->staff->id);

        $this->assertModelMissing($this->staff);
    }

    public function test_master_cannot_delete_staff(): void
    {
        $this->actingAsMaster();

        \Livewire\Livewire::test(\App\Livewire\AccountPage::class)
            ->call('deleteStaff', $this->staff->id)
            ->assertForbidden();

        $this->assertModelExists($this->staff);
    }
}
