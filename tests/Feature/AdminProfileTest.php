<?php

namespace Tests\Feature;

use App\Livewire\ChangePassword;
use App\Livewire\ProfileForm;
use App\Livewire\ProfilePhoto;
use App\Models\User;
use App\Models\Userprofile;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminProfileTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['password' => 'old-password', 'name' => 'Howard Admin']);
        $this->admin->forceFill(['role_as' => 1])->save();
    }

    protected function tearDown(): void
    {
        $file = optional(Userprofile::where('user_id', $this->admin->id)->first())->profile_picture;
        if ($file && is_file(public_path('Userprofile/'.$file))) {
            unlink(public_path('Userprofile/'.$file));
        }

        parent::tearDown();
    }

    public function test_admin_profile_page_lives_inside_the_admin_panel(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.profile'))
            ->assertOk()
            ->assertSee('ad-side', false)
            ->assertSeeLivewire(ProfilePhoto::class)
            ->assertSeeLivewire(ProfileForm::class)
            ->assertSeeLivewire(ChangePassword::class)
            ->assertSee(route('admin.profile'), false);
    }

    public function test_readers_cannot_open_the_admin_profile_page(): void
    {
        $this->actingAs(User::factory()->create(['password' => 'password']))
            ->get(route('admin.profile'))
            ->assertRedirect(url('/'));
    }

    public function test_uploaded_photo_shows_in_the_admin_header(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('ui-avatars.com/api/?name=Howard', false);

        Livewire::actingAs($this->admin)
            ->test(ProfilePhoto::class, ['variant' => 'admin'])
            ->set('photo', UploadedFile::fake()->image('me.jpg'))
            ->assertHasNoErrors()
            ->assertDispatched('profile-photo-updated');

        $photoUrl = $this->admin->fresh()->avatarUrl();
        $this->assertStringContainsString('/profile-photo/', $photoUrl);

        $this->actingAs($this->admin->fresh())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee($photoUrl, false);
    }

    public function test_password_change_works_in_place(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ChangePassword::class, ['variant' => 'admin'])
            ->set('current_password', 'wrong-password')
            ->set('new_password', 'brand-new-pass')
            ->set('new_password_confirmation', 'brand-new-pass')
            ->call('save')
            ->assertHasErrors(['current_password']);

        $this->assertTrue(Hash::check('old-password', $this->admin->fresh()->password));

        Livewire::actingAs($this->admin)
            ->test(ChangePassword::class, ['variant' => 'admin'])
            ->set('current_password', 'old-password')
            ->set('new_password', 'brand-new-pass')
            ->set('new_password_confirmation', 'brand-new-pass')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Password updated successfully.')
            ->assertSet('current_password', '');

        $this->assertTrue(Hash::check('brand-new-pass', $this->admin->fresh()->password));
    }

    public function test_password_rules_are_kept(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ChangePassword::class)
            ->set('current_password', 'old-password')
            ->set('new_password', '123')
            ->set('new_password_confirmation', '456')
            ->call('save')
            ->assertHasErrors(['new_password']);
    }
}
