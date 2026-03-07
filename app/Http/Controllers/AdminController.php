<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // Admin dashboard
    public function admin_dashboard()
    {
        return view('admin.index');
    }

    // List active users
    public function userList()
    {
        $users = User::where('role_as', 0)
                    ->latest()
                    ->paginate(10);

        return view('admin.users.users-list', compact('users'));
    }

    // Soft delete (trash) user
    public function deleteUser(User $user)
    {
        $user->delete();

        return redirect()->back()->with('success', 'User has been moved to trash.');
    }

    // Ban user
    public function ban(User $user)
    {
        $user->active = 0;
        $user->save();

        return redirect()->back()->with('success', 'User has been banned.');
    }

    // Unban user
    public function unban(User $user)
    {
        $user->active = 1;
        $user->save();

        return redirect()->back()->with('success', 'User has been unbanned.');
    }

    // List trashed (soft deleted) users
    public function trashedUsers()
    {
        $users = User::onlyTrashed()
                    ->paginate(10);

        return view('admin.users.trashed', compact('users'));
    }

    // Restore soft-deleted user
    public function restoreUser($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->back()->with('success', 'User has been restored successfully.');
    }

    // Permanently delete a soft-deleted user
    public function forceDeleteUser($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->forceDelete();

        return redirect()->back()->with('success', 'User has been permanently deleted.');
    }

        // Return all comments
    public function comments()
    {
       $comments = Comment::with(['user','post'])->latest()->paginate(10);
        return view('admin.comments.index', compact('comments'));
    }

    public function destroy($id)
{
    $comment = Comment::findOrFail($id);
    $comment->delete();

    return back()->with('success', 'Comment deleted successfully');
}

    // Return all subscribers
    public function subscribers()
    {
        $subscribers = Subscriber::latest()->paginate(10);
        return view('admin.subscribers.index', compact('subscribers'));
    }

    public function destroys($id)
{
    $subscriber = Subscriber::findOrFail($id);
    $subscriber->delete();

    return back()->with('success', 'Subscriber deleted successfully');
}
}
