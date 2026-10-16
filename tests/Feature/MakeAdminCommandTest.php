<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MakeAdminCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_promotes_an_existing_user_who_can_then_open_the_admin_area(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $this->artisan('user:make-admin', ['email' => $user->email])
            ->expectsOutputToContain('is now an admin')
            ->assertSuccessful();

        $this->assertSame(1, (int) $user->fresh()->role_as);
        $this->actingAs($user->fresh())->get(route('admin.dashboard'))->assertOk();
    }

    public function test_it_creates_a_new_admin_account_when_none_exists(): void
    {
        $this->artisan('user:make-admin', ['email' => 'new-admin-test@example.com'])
            ->expectsConfirmation('No account found for new-admin-test@example.com. Create a new admin account?', 'yes')
            ->expectsQuestion('Full name', 'Ada Admin')
            ->expectsQuestion('Password (at least 8 characters)', 'super-secret-1')
            ->assertSuccessful();

        $admin = User::where('email', 'new-admin-test@example.com')->firstOrFail();

        $this->assertSame('Ada Admin', $admin->name);
        $this->assertSame(1, (int) $admin->role_as);
        $this->assertTrue(Hash::check('super-secret-1', $admin->password));
    }

    public function test_it_revokes_admin_access(): void
    {
        $user = User::factory()->create(['password' => 'password']);
        $user->forceFill(['role_as' => 1])->save();

        $this->artisan('user:make-admin', ['email' => $user->email, '--revoke' => true])->assertSuccessful();

        $this->assertSame(0, (int) $user->fresh()->role_as);
        $this->actingAs($user->fresh())->get(route('admin.dashboard'))->assertRedirect(url('/'));
    }

    public function test_it_rejects_an_invalid_email(): void
    {
        $this->artisan('user:make-admin', ['email' => 'not-an-email'])->assertFailed();
    }
}
