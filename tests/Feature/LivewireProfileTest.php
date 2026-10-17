<?php

namespace Tests\Feature;

use App\Livewire\ChangePassword;
use App\Livewire\ProfileForm;
use App\Livewire\ProfilePhoto;
use App\Models\User;
use App\Models\Userprofile;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireProfileTest extends TestCase
{
    use DatabaseTransactions;

    private User $reader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reader = User::factory()->create(['password' => 'password', 'name' => 'Ada Reader']);
    }

    protected function tearDown(): void
    {
        $file = optional(Userprofile::where('user_id', $this->reader->id)->first())->profile_picture;

        if ($file && is_file(public_path('Userprofile/'.$file))) {
            unlink(public_path('Userprofile/'.$file));
        }

        parent::tearDown();
    }

    public function test_tapping_the_photo_uploads_it_immediately(): void
    {
        Livewire::actingAs($this->reader)
            ->test(ProfilePhoto::class)
            ->set('photo', UploadedFile::fake()->image('me.jpg', 300, 300))
            ->assertHasNoErrors()
            ->assertSee('Photo updated.')
            ->assertDispatched('profile-photo-updated');

        $profile = Userprofile::where('user_id', $this->reader->id)->firstOrFail();
        $this->assertFileExists(public_path('Userprofile/'.$profile->profile_picture));
        $this->get($profile->photoUrl())->assertOk();
    }

    public function test_a_new_photo_replaces_and_deletes_the_old_one(): void
    {
        $component = Livewire::actingAs($this->reader)->test(ProfilePhoto::class);

        $component->set('photo', UploadedFile::fake()->image('first.jpg'));
        $firstFile = Userprofile::where('user_id', $this->reader->id)->value('profile_picture');

        sleep(1); // file names start with the upload time
        $component->set('photo', UploadedFile::fake()->image('second.png'));
        $secondFile = Userprofile::where('user_id', $this->reader->id)->value('profile_picture');

        $this->assertNotSame($firstFile, $secondFile);
        $this->assertFileDoesNotExist(public_path('Userprofile/'.$firstFile));
        $this->assertFileExists(public_path('Userprofile/'.$secondFile));
    }

    public function test_non_images_and_large_files_are_rejected(): void
    {
        Livewire::actingAs($this->reader)
            ->test(ProfilePhoto::class)
            ->set('photo', UploadedFile::fake()->create('notes.pdf', 50, 'application/pdf'))
            ->assertHasErrors(['photo']);

        Livewire::actingAs($this->reader)
            ->test(ProfilePhoto::class)
            ->set('photo', UploadedFile::fake()->image('huge.jpg')->size(3000))
            ->assertHasErrors(['photo' => 'max']);

        $this->assertNull(Userprofile::where('user_id', $this->reader->id)->value('profile_picture'));
    }

    public function test_profile_form_saves_details_and_photo_in_place(): void
    {
        Livewire::actingAs($this->reader)
            ->test(ProfileForm::class)
            ->assertSet('name', 'Ada Reader')
            ->set('name', 'Ada Okafor')
            ->set('phone', '0801 234 5678')
            ->set('address', '12 Zik Avenue, Awka')
            ->set('photo', UploadedFile::fake()->image('new.jpg'))
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Profile updated successfully.')
            ->assertDispatched('profile-updated')
            ->assertDispatched('profile-photo-updated')
            ->assertNoRedirect();

        $this->assertSame('Ada Okafor', $this->reader->fresh()->name);
        $profile = Userprofile::where('user_id', $this->reader->id)->firstOrFail();
        $this->assertSame('0801 234 5678', $profile->phone);
        $this->assertSame('12 Zik Avenue, Awka', $profile->address);
        $this->assertNotNull($profile->profile_picture);
    }

    public function test_profile_form_keeps_the_phone_and_name_rules(): void
    {
        Livewire::actingAs($this->reader)
            ->test(ProfileForm::class)
            ->set('name', '')
            ->set('phone', '12345')
            ->call('save')
            ->assertHasErrors(['name' => 'required', 'phone']);
    }

    public function test_account_page_uses_the_livewire_profile_components(): void
    {
        $this->actingAs($this->reader)
            ->get(route('profile'))
            ->assertOk()
            ->assertSeeLivewire(ProfilePhoto::class)
            ->assertSeeLivewire(ProfileForm::class)
            ->assertSeeLivewire(ChangePassword::class);
    }
}
