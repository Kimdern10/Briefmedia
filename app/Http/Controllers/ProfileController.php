<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Userprofile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function profile()
    {
        $user = User::with('Userprofile')->find(Auth::id());
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:225',
            'phone' => [
                'nullable',
                'string',
                'max:15',
                function ($attribute, $value, $fail) {
                    if ($value && strlen(preg_replace('/[^0-9]/', '', $value)) != 11) {
                        $fail('The phone number must be exactly 11 digits.');
                    }
                },
            ],
            'address' => 'nullable|string',
            'bio' => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'name.required' => 'Please enter your full name',
            'phone.max' => 'Phone number cannot exceed 15 characters',
            'profile_picture.image' => 'File must be an image',
            'profile_picture.mimes' => 'Image must be jpeg, png, jpg, or gif',
            'profile_picture.max' => 'Image size cannot exceed 2MB',
        ]);

        $user = Auth::user();

        $user->update(['name' => $request->name]);

        $data = $request->only(['phone', 'address', 'bio']);

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $filename = time() . '.' . $file->getClientOriginalExtension();

            // Delete old profile picture if exists
            if ($user->Userprofile && $user->Userprofile->profile_picture && file_exists(public_path('Userprofile/' . $user->Userprofile->profile_picture))) {
                unlink(public_path('Userprofile/' . $user->Userprofile->profile_picture));
            }
            
            $file->move(public_path('Userprofile'), $filename);
            $data['profile_picture'] = $filename;
        }

        Userprofile::updateOrCreate(
            ['user_id' => $user->id],
            $data
        );
        
        return back()->with('profile_success', 'Profile updated successfully');
    }

    public function passwordUpdate(Request $request)
    {
        // Custom validation with proper error messages
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ], [
            'current_password.required' => 'Please enter your current password',
            'new_password.required' => 'Please enter a new password',
            'new_password.min' => 'New password must be at least 6 characters',
            'new_password.confirmed' => 'New password and confirm password do not match',
        ]);

        $user = Auth::user();

        // Check if current password matches
        if (!Hash::check($request->current_password, $user->password)) {
            // Use withErrors for field-specific error messages
            return redirect()->back()->withErrors([
                'current_password' => 'The current password is incorrect'
            ])->withInput();
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();
        
        return back()->with('success', 'Password updated successfully');
    }
}