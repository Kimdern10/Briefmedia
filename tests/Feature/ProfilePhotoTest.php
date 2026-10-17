<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use DatabaseTransactions;

    /** @var array<int, string> */
    private array $createdFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $file) {
            @unlink($file);
        }

        parent::tearDown();
    }

    public function test_uploaded_profile_photo_is_served_and_shown_in_the_header(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $this->actingAs($user)
            ->put(route('update'), [
                'name' => $user->name,
                'profile_picture' => UploadedFile::fake()->image('me.jpg', 300, 300),
            ])
            ->assertRedirect();

        $profile = $user->fresh()->Userprofile;
        $this->assertNotNull($profile->profile_picture);
        $this->createdFiles[] = public_path('Userprofile/'.$profile->profile_picture);

        $this->get($profile->photoUrl())
            ->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg');

        $this->actingAs($user->fresh())
            ->get(route('profile'))
            ->assertOk()
            ->assertSee($profile->photoUrl(), false);
    }

    public function test_only_existing_photos_in_the_profile_folder_are_served(): void
    {
        $this->get('/profile-photo/does-not-exist.jpg')->assertNotFound();
        $this->get('/profile-photo/..%2F..%2F.env')->assertNotFound();

        $notAnImage = public_path('Userprofile/zz-not-an-image.jpg');
        file_put_contents($notAnImage, '<?php echo "hi";');
        $this->createdFiles[] = $notAnImage;

        $this->get('/profile-photo/zz-not-an-image.jpg')->assertNotFound();
    }
}
