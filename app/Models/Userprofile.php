<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Userprofile extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'phone', 'address', 'profile_picture'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id'); // 'user_id'
    }

    /**
     * Save a new profile photo for the user into public/Userprofile, remove the old file,
     * and return the stored file name.
     */
    public static function replacePhotoFor(User $user, UploadedFile $file): string
    {
        $profile = static::firstOrNew(['user_id' => $user->id]);
        $directory = public_path('Userprofile');

        File::ensureDirectoryExists($directory);

        $filename = time().'_'.Str::lower(Str::random(6)).'.'.strtolower($file->getClientOriginalExtension() ?: $file->extension());
        File::copy($file->getRealPath(), $directory.DIRECTORY_SEPARATOR.$filename);

        if ($profile->profile_picture && is_file($directory.DIRECTORY_SEPARATOR.basename($profile->profile_picture))) {
            File::delete($directory.DIRECTORY_SEPARATOR.basename($profile->profile_picture));
        }

        $profile->profile_picture = $filename;
        $profile->save();

        return $filename;
    }

    /**
     * Public URL of the uploaded profile photo, or null when there is none.
     */
    public function photoUrl(): ?string
    {
        return $this->profile_picture
            ? route('profile.photo', $this->profile_picture)
            : null;
    }
}
