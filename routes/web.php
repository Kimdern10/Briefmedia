<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\User\CategoryPostController;
use App\Http\Controllers\NewsletterController;
use Illuminate\Support\Facades\Artisan; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::view('/terms', 'pages.terms')->name('terms');
Route::view('/privacy', 'pages.privacy')->name('privacy');
// register view
Route::get('/sign-up', [UserController::class, 'signUp'])->name('sign-up');

// submit registration from route
Route::post('/create-user', [UserController::class, 'createUser'])->name('create-user');


Route::get('/verify.otp/{email}', [UserController::class, 'showOtpForm'])->name('verify.otp');

Route::post('/verify.otp/{email}/submit', [UserController::class, 'submitOtp'])->name('verify.otp.submit'); // email is fetching from verify otp get it from url

Route::get('/resend.otp/{email}/resend', [UserController::class, 'resendOtp'])->name('resend.otp');


// forgot password view
Route::get('forget-password', [ForgotPasswordController::class, 'forgetPassword'])->name('forgetPassword');

Route::post('forgotpassword', [ForgotPasswordController::class, 'forgotPassword'])->name('forgotPassword.email');

Route::get('confirm-code', [ForgotPasswordController::class, 'confirmCode'])->name('confirmCode.email');

Route::post('submit-password-reset-code', [ForgotPasswordController::class, 'submitPasswordResetCode'])->name('submitPasswordResetCode');

Route::get('/password-reset', [ForgotPasswordController::class, 'passwordReset'])->name('password-reset');


Route::post('/create-new-password', [ForgotPasswordController::class, 'createNewPassword'])->name('create.new-password');

Route::get('/resend.code/{email}/resend', [ForgotPasswordController::class, 'resendCode'])->name('resend.code');
Auth::routes();


Route::get('/category/{category:slug}', [CategoryPostController::class, 'category'])
    ->name('category.page');
Route::get('/posts/{post}', [CategoryPostController::class, 'show'])->name('posts.show');
// Add this with your other routes
Route::get('/user/posts/search', [UserController::class, 'searchPosts'])->name('user.posts.search');

Route::get('/contact-us', [ContactController::class, 'index'])->name('contact');
Route::post('/contact-us', [ContactController::class, 'store'])->name('contact.store');

Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

Route::get('/', [UserController::class, 'blade'])->name('home');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');



Route::middleware(['auth', 'isAdmin'])->prefix('admin')->group(function () {
Route::get('dashboard', [AdminController::class, 'admin_dashboard'])->name('admin.dashboard');



  Route::get('/categories', [CategoryController::class, 'index']) ->name('admin.categories.index');

Route::get('/categories/create', [CategoryController::class, 'create'])  ->name('admin.categories.create');

  Route::post('/categories', [CategoryController::class, 'store'])   ->name('admin.categories.store');

  Route::get('/categories/{category}/edit', [CategoryController::class, 'edit']) ->name('admin.categories.edit');

Route::put('/categories/{category}', [CategoryController::class, 'update'])  ->name('admin.categories.update');

Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])    ->name('admin.categories.destroy');

Route::get('/categories/trash', [CategoryController::class, 'trash'])  ->name('admin.categories.trash');

 Route::post('/categories/{id}/restore', [CategoryController::class, 'restore']) ->name('admin.categories.restore');
Route::delete('/categories/{id}/force-delete', [CategoryController::class, 'forceDelete'])->name('admin.categories.forceDelete');




// Trash management (should come before resource routes to avoid conflicts)
Route::get('/posts/trash', [PostController::class, 'trash'])->name('admin.posts.trash');

// Post restoration and force delete (uses ID parameter)
Route::post('/posts/{id}/restore', [PostController::class, 'restore'])->name('admin.posts.restore');
Route::delete('/posts/{id}/force-delete', [PostController::class, 'forceDelete'])->name('admin.posts.forceDelete');

// Toggle post status
Route::patch('/posts/{post}/toggle-status', [PostController::class, 'toggleStatus'])->name('admin.posts.toggle-status');

// ===============================
// SEO ROUTES
// ===============================

// SEO Preview - POST request for previewing SEO data
Route::post('/posts/seo-preview', [PostController::class, 'previewSeo'])->name('admin.posts.seo.preview');

// SEO Suggestions - Get AI-like suggestions for a specific post
Route::get('/posts/{id}/seo-suggestions', [PostController::class, 'seoSuggestions'])->name('admin.posts.seo.suggestions');

// Bulk SEO Update - Update SEO settings for multiple posts at once
Route::post('/posts/bulk-seo-update', [PostController::class, 'bulkSeoUpdate'])->name('admin.posts.seo.bulk');

// ===============================
// STANDARD CRUD ROUTES
// ===============================

// List posts
Route::get('/posts', [PostController::class, 'index'])->name('admin.posts.index');

// Create post form
Route::get('/posts/create', [PostController::class, 'create'])->name('admin.posts.create');

// Store new post
Route::post('/posts', [PostController::class, 'store'])->name('admin.posts.store');

// Edit post form (uses route model binding)
Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('admin.posts.edit');

// Update post (uses route model binding)
Route::put('/posts/{post}', [PostController::class, 'update'])->name('admin.posts.update');

// Soft delete post (uses route model binding)
Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('admin.posts.destroy');



  Route::get('/users', [AdminController::class, 'userList'])->name('user.list');
    Route::patch('user/{user}/ban', [AdminController::class, 'ban'])->name('user.ban');
    Route::patch('user/{user}/unban', [AdminController::class, 'unban'])->name('user.unban');

    Route::get('/users/trashed', [AdminController::class, 'trashedUsers'])->name('users.trashed');


    Route::delete('/users/{user}/delete', [AdminController::class, 'deleteUser'])->name('user.delete');
    Route::patch('/users/{id}/restore', [AdminController::class, 'restoreUser'])->name('user.restore');
    Route::delete('/users/{id}/force-delete', [AdminController::class, 'forceDeleteUser'])->name('user.forceDelete');


Route::get('seo-settings', [SeoController::class, 'edit'])->name('admin.seo.edit');
Route::post('seo-settings', [SeoController::class, 'update'])->name('admin.seo.update');

  Route::get('comments', [AdminController::class, 'comments'])->name('admin.comments');
    Route::get('subscribers', [AdminController::class, 'subscribers'])->name('admin.subscribers');

Route::get('/admin/send-newsletter', function () {
    Artisan::call('newsletter:weekly');
    return redirect()->back()->with('success', 'Newsletter sent successfully!');
})->name('admin.send-newsletter');
});




Route::middleware(['auth'])->prefix('user')->group(function () {
Route::get('dashboard', [UserController::class, 'user_dashboard'])->name('user.dashboard');

Route::get('/profile', [ProfileController::class, 'profile'])->name('profile');

Route::put('/update', [ProfileController::class, 'update'])->name('update');

Route::post('upadte/password', [ProfileController::class, 'passwordUpdate'])->name('password-update');



Route::post('/users/posts/{post}/like', [CategoryPostController::class, 'toggleLike'])->name('posts.like');
  Route::post('/posts/{post}/comment', [CategoryPostController::class, 'storeComment'])  ->name('posts.comment');



});