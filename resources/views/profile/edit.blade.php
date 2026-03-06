@extends('layouts.app')

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <div class="container-fluid px-4">
            
            <!-- Dashboard Header -->
            <div class="dashboard-header mb-4">
                <h1 class="dashboard-title">My Dashboard</h1>
                <p class="dashboard-subtitle">Manage your profile and account settings</p>
            </div>

            <div class="row g-4">
                <!-- Sidebar - Profile Card -->
                <div class="col-xl-3 col-lg-4">
                    <div class="profile-sidebar">
                        <!-- Profile Card -->
                        <div class="profile-card">
                            <!-- Profile Image with Upload - FIXED with pink fallback -->
                            <div class="profile-image-container">
                                <div class="profile-image-wrapper" onclick="document.getElementById('profile-upload').click();">
                                    @php
                                        $firstLetter = strtoupper(substr($user->name, 0, 1));
                                    @endphp
                                    <img 
                                        src="{{ optional($user->Userprofile)->profile_picture ? asset('Userprofile/' . $user->Userprofile->profile_picture) : asset('assets/img/user/1.jpg') }}" 
                                        alt="{{ $user->name }}" 
                                        class="profile-image"
                                        onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'150\' height=\'150\' viewBox=\'0 0 150 150\'%3E%3Ccircle cx=\'75\' cy=\'75\' r=\'75\' fill=\'%23ff69b4\'/%3E%3Ctext x=\'75\' y=\'105\' font-size=\'70\' text-anchor=\'middle\' fill=\'%23ffffff\' font-family=\'Arial, sans-serif\' font-weight=\'bold\'%3E{{ $firstLetter }}%3C/text%3E%3C/svg%3E'"
                                    >
                                    <div class="upload-overlay">
                                        <i class="fas fa-camera"></i>
                                    </div>
                                </div>
                                <form id="profile-upload-form" action="{{ route('update') }}" method="POST" enctype="multipart/form-data" style="display: none;">
                                    @csrf
                                    @method('put')
                                    <input type="file" id="profile-upload" name="profile_picture" accept="image/*" onchange="this.form.submit()">
                                </form>
                            </div>

                            <!-- User Info -->
                            <div class="user-info">
                                <h3 class="user-name">{{ $user->name }}</h3>
                                <p class="user-email">{{ $user->email }}</p>
                                
                                @if($user->Userprofile && $user->Userprofile->phone)
                                    <p class="user-phone">
                                        <i class="fas fa-phone-alt me-2"></i>{{ $user->Userprofile->phone }}
                                    </p>
                                @endif
                            </div>

                            <!-- Member Since -->
                            <div class="member-info">
                                <i class="far fa-calendar-alt me-2"></i>
                                Member since {{ $user->created_at->format('F Y') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-xl-9 col-lg-8">
                    <!-- Tabs Navigation -->
                    <div class="tabs-container">
                        <div class="tabs-nav">
                            <button class="tab-btn {{ !session('success') && !$errors->has('current_password') && !$errors->has('new_password') ? 'active' : '' }}" data-tab="profile" id="profile-tab-btn">
                                <i class="fas fa-user me-2"></i>Profile
                            </button>
                            <button class="tab-btn {{ session('success') || $errors->has('current_password') || $errors->has('new_password') ? 'active' : '' }}" data-tab="security" id="security-tab-btn">
                                <i class="fas fa-shield-alt me-2"></i>Security
                            </button>
                        </div>
                    </div>

                    <!-- Tab Content -->
                    <div class="tab-content-container">
                        <!-- Profile Tab -->
                        <div class="tab-pane {{ !session('success') && !$errors->has('current_password') && !$errors->has('new_password') ? 'active' : '' }}" id="profile-tab">
                            <div class="content-card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-user-circle me-2"></i>Profile Information
                                    </h5>
                                    <span class="status-badge">Active</span>
                                </div>

                                <!-- Profile Success Message -->
                                @if(session('profile_success'))
                                    <div class="form-group full-width mb-4">
                                        <span class="success-message"><i class="fas fa-check-circle me-2"></i>{{ session('profile_success') }}</span>
                                    </div>
                                @endif

                                <form action="{{ route('update') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('put')
                                    
                                    <div class="form-grid">
                                        <!-- Full Name -->
                                        <div class="form-group full-width">
                                            <label class="form-label">Full Name</label>
                                            <div class="input-wrapper">
                                                <i class="fas fa-user input-icon"></i>
                                                <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                                                       class="form-input @error('name') error-border @enderror" 
                                                       placeholder="Enter your full name">
                                            </div>
                                            @error('name')
                                                <span class="error-message"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Email (Read Only) -->
                                        <div class="form-group">
                                            <label class="form-label">Email Address</label>
                                            <div class="input-wrapper">
                                                <i class="fas fa-envelope input-icon"></i>
                                                <input type="email" value="{{ $user->email }}" 
                                                       class="form-input" readonly>
                                            </div>
                                        </div>

                                        <!-- Phone -->
                                        <div class="form-group">
                                            <label class="form-label">Phone Number</label>
                                            <div class="input-wrapper">
                                                <i class="fas fa-phone-alt input-icon"></i>
                                                <input type="text" name="phone" value="{{ old('phone', $user->Userprofile->phone ?? '') }}" 
                                                       class="form-input @error('phone') error-border @enderror" 
                                                       placeholder="Enter your phone number">
                                            </div>
                                            @error('phone')
                                                <span class="error-message"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Profile Picture Upload -->
                                        <div class="form-group full-width">
                                            <label class="form-label">Profile Picture</label>
                                            <div class="file-input-wrapper @error('profile_picture') error-border @enderror">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                                <input type="file" name="profile_picture" id="file-input" accept="image/*">
                                                <span class="file-input-text">Click to upload or drag and drop</span>
                                                <span class="file-input-info">PNG, JPG, GIF up to 2MB</span>
                                            </div>
                                            @error('profile_picture')
                                                <span class="error-message"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Address -->
                                        <div class="form-group full-width">
                                            <label class="form-label">Address</label>
                                            <div class="input-wrapper">
                                                <i class="fas fa-map-marker-alt input-icon"></i>
                                                <input type="text" name="address" value="{{ old('address', $user->Userprofile->address ?? '') }}" 
                                                       class="form-input @error('address') error-border @enderror" 
                                                       placeholder="Enter your address">
                                            </div>
                                            @error('address')
                                                <span class="error-message"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" class="btn-primary">
                                            <i class="fas fa-save me-2"></i>Save Changes
                                        </button>
                                        <a href="{{ url('/') }}" class="btn-secondary">
                                            <i class="fas fa-times me-2"></i>Cancel
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Security Tab -->
                        <div class="tab-pane {{ session('success') || $errors->has('current_password') || $errors->has('new_password') || $errors->has('new_password_confirmation') ? 'active' : '' }}" id="security-tab">
                            <div class="content-card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-lock me-2"></i>Change Password
                                    </h5>
                                </div>

                                <!-- Password Success Message -->
                                @if(session('success'))
                                    <div class="form-group full-width mb-4">
                                        <span class="success-message"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</span>
                                    </div>
                                @endif

                                <form action="{{ route('password-update') }}" method="POST">
                                    @csrf
                                    
                                    <div class="form-grid">
                                        <!-- Current Password -->
                                        <div class="form-group full-width">
                                            <label class="form-label">Current Password</label>
                                            <div class="input-wrapper">
                                                <i class="fas fa-lock input-icon"></i>
                                                <input type="password" name="current_password" 
                                                       class="form-input @error('current_password') error-border @enderror" 
                                                       placeholder="Enter current password">
                                            </div>
                                            @error('current_password')
                                                <span class="error-message"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- New Password -->
                                        <div class="form-group">
                                            <label class="form-label">New Password</label>
                                            <div class="input-wrapper">
                                                <i class="fas fa-key input-icon"></i>
                                                <input type="password" name="new_password" 
                                                       class="form-input @error('new_password') error-border @enderror" 
                                                       placeholder="Enter new password">
                                            </div>
                                            @error('new_password')
                                                <span class="error-message"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Confirm Password -->
                                        <div class="form-group">
                                            <label class="form-label">Confirm Password</label>
                                            <div class="input-wrapper">
                                                <i class="fas fa-check-circle input-icon"></i>
                                                <input type="password" name="new_password_confirmation" 
                                                       class="form-input @error('new_password_confirmation') error-border @enderror" 
                                                       placeholder="Confirm new password">
                                            </div>
                                            @error('new_password_confirmation')
                                                <span class="error-message"><i class="fas fa-exclamation-circle me-1"></i>{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <!-- Password Requirements -->
                                        <div class="form-group full-width">
                                            <div class="requirements-box">
                                                <h6 class="requirements-title">
                                                    <i class="fas fa-shield-alt me-2"></i>Password Requirements:
                                                </h6>
                                                <ul class="requirements-list">
                                                    <li><i class="fas fa-circle"></i>At least 8 characters long</li>
                                                    <li><i class="fas fa-circle"></i>Include uppercase & lowercase letters</li>
                                                    <li><i class="fas fa-circle"></i>Include at least one number</li>
                                                    <li><i class="fas fa-circle"></i>Include at least one special character</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" class="btn-primary">
                                            <i class="fas fa-key me-2"></i>Update Password
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Styles -->
<style>
/* Reset and Base Styles */
.dashboard-wrapper * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.dashboard-wrapper {
    background-color: #000000 !important;
    min-height: 100vh !important;
    padding: 40px 0 !important;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif !important;
}

.dashboard-container {
    max-width: 1400px !important;
    margin: 0 auto !important;
}

/* Header Styles */
.dashboard-header {
    margin-bottom: 30px !important;
    padding: 20px 0 !important;
}

.dashboard-title {
    color: #ff69b4 !important;
    font-size: 2.5rem !important;
    font-weight: 700 !important;
    margin-bottom: 10px !important;
    text-shadow: 0 0 10px rgba(255, 105, 180, 0.3) !important;
}

.dashboard-subtitle {
    color: #ff99cc !important;
    font-size: 1.1rem !important;
    opacity: 0.9 !important;
}

/* Profile Card Styles */
.profile-sidebar {
    position: sticky !important;
    top: 100px !important;
}

.profile-card {
    background-color: #111111 !important;
    border: 1px solid #333333 !important;
    border-radius: 20px !important;
    padding: 30px !important;
    box-shadow: 0 10px 30px rgba(255, 105, 180, 0.1) !important;
}

/* Profile Image - UPDATED with pink background */
.profile-image-container {
    text-align: center !important;
    margin-bottom: 25px !important;
}

.profile-image-wrapper {
    position: relative !important;
    width: 150px !important;
    height: 150px !important;
    margin: 0 auto !important;
    border-radius: 50% !important;
    overflow: hidden !important;
    cursor: pointer !important;
    border: 3px solid #ff69b4 !important;
    box-shadow: 0 0 20px rgba(255, 105, 180, 0.3) !important;
    background-color: #ff69b4 !important; /* Pink background */
}

.profile-image {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    display: block !important;
    background-color: #ff69b4 !important; /* Pink background */
}

.upload-overlay {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background: rgba(255, 105, 180, 0.8) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    color: #ffffff !important;
    font-size: 24px !important;
    opacity: 0 !important;
    transition: opacity 0.3s ease !important;
}

.profile-image-wrapper:hover .upload-overlay {
    opacity: 1 !important;
}

/* User Info */
.user-info {
    text-align: center !important;
    margin-bottom: 20px !important;
}

.user-name {
    color: #ff69b4 !important;
    font-size: 1.5rem !important;
    font-weight: 600 !important;
    margin-bottom: 5px !important;
}

.user-email {
    color: #ff99cc !important;
    font-size: 0.95rem !important;
    margin-bottom: 8px !important;
}

.user-phone {
    color: #ffb6c1 !important;
    font-size: 0.95rem !important;
}

.member-info {
    color: #ff99cc !important;
    font-size: 0.9rem !important;
    text-align: center !important;
    padding: 10px !important;
    background-color: #1a1a1a !important;
    border-radius: 10px !important;
    border: 1px solid #333333 !important;
}

/* Tabs Container */
.tabs-container {
    background-color: #111111 !important;
    border: 1px solid #333333 !important;
    border-radius: 15px !important;
    padding: 10px !important;
    margin-bottom: 20px !important;
}

.tabs-nav {
    display: flex !important;
    gap: 10px !important;
    flex-wrap: wrap !important;
}

.tab-btn {
    background: none !important;
    border: 1px solid #333333 !important;
    padding: 12px 25px !important;
    border-radius: 10px !important;
    color: #ff99cc !important;
    font-size: 0.95rem !important;
    font-weight: 500 !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
    flex: 1 !important;
    max-width: 200px !important;
}

.tab-btn i {
    color: #ff69b4 !important;
}

.tab-btn:hover {
    background-color: #1a1a1a !important;
    border-color: #ff69b4 !important;
    transform: translateY(-2px) !important;
}

.tab-btn.active {
    background-color: #ff69b4 !important;
    border-color: #ff69b4 !important;
    color: #000000 !important;
}

.tab-btn.active i {
    color: #000000 !important;
}

/* Content Cards */
.content-card {
    background-color: #111111 !important;
    border: 1px solid #333333 !important;
    border-radius: 20px !important;
    padding: 30px !important;
    box-shadow: 0 10px 30px rgba(255, 105, 180, 0.1) !important;
}

.card-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 25px !important;
    padding-bottom: 15px !important;
    border-bottom: 1px solid #333333 !important;
}

.card-title {
    color: #ff69b4 !important;
    font-size: 1.25rem !important;
    font-weight: 600 !important;
    margin: 0 !important;
}

.status-badge {
    background-color: #1a1a1a !important;
    color: #ff69b4 !important;
    padding: 5px 15px !important;
    border-radius: 20px !important;
    font-size: 0.85rem !important;
    border: 1px solid #ff69b4 !important;
}

/* Form Styles */
.form-grid {
    display: grid !important;
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 20px !important;
    margin-bottom: 25px !important;
}

.form-group.full-width {
    grid-column: span 2 !important;
}

.form-label {
    color: #ff99cc !important;
    font-size: 0.95rem !important;
    font-weight: 500 !important;
    margin-bottom: 8px !important;
    display: block !important;
}

.input-wrapper {
    position: relative !important;
}

.input-icon {
    position: absolute !important;
    left: 15px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    color: #ff69b4 !important;
    z-index: 1 !important;
}

.form-input {
    width: 100% !important;
    padding: 12px 15px 12px 45px !important;
    background-color: #1a1a1a !important;
    border: 1px solid #333333 !important;
    border-radius: 10px !important;
    color: #ffffff !important;
    font-size: 0.95rem !important;
    transition: all 0.3s ease !important;
}

.form-input:focus {
    outline: none !important;
    border-color: #ff69b4 !important;
    box-shadow: 0 0 10px rgba(255, 105, 180, 0.2) !important;
}

.form-input[readonly] {
    background-color: #000000 !important;
    color: #ff99cc !important;
    cursor: not-allowed !important;
}

textarea.form-input {
    resize: vertical !important;
    min-height: 100px !important;
}

/* Error Border */
.error-border {
    border-color: #ff69b4 !important;
    box-shadow: 0 0 5px rgba(255, 105, 180, 0.5) !important;
}

/* File Input */
.file-input-wrapper {
    position: relative !important;
    padding: 30px !important;
    background-color: #1a1a1a !important;
    border: 2px dashed #333333 !important;
    border-radius: 10px !important;
    text-align: center !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
}

.file-input-wrapper:hover {
    border-color: #ff69b4 !important;
    background-color: #222222 !important;
}

.file-input-wrapper i {
    font-size: 40px !important;
    color: #ff69b4 !important;
    margin-bottom: 10px !important;
}

.file-input-wrapper input {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    opacity: 0 !important;
    cursor: pointer !important;
}

.file-input-text {
    display: block !important;
    color: #ff99cc !important;
    margin-bottom: 5px !important;
}

.file-input-info {
    color: #666666 !important;
    font-size: 0.85rem !important;
}

/* Error Message */
.error-message {
    color: #ff69b4 !important;
    font-size: 0.85rem !important;
    margin-top: 5px !important;
    margin-bottom: 0 !important;
    display: block !important;
    font-weight: 500 !important;
    padding-left: 5px !important;
}

/* Success Message */
.success-message {
    color: #00ff00 !important;
    font-size: 0.85rem !important;
    margin-top: 5px !important;
    margin-bottom: 0 !important;
    display: block !important;
    font-weight: 500 !important;
    padding: 10px !important;
    background-color: #1a1a1a !important;
    border: 1px solid #00ff00 !important;
    border-radius: 10px !important;
}

/* Form Actions */
.form-actions {
    display: flex !important;
    gap: 15px !important;
    justify-content: flex-start !important;
}

.btn-primary {
    background-color: #ff69b4 !important;
    border: 1px solid #ff69b4 !important;
    color: #000000 !important;
    padding: 12px 30px !important;
    border-radius: 10px !important;
    font-size: 0.95rem !important;
    font-weight: 500 !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
}

.btn-primary:hover {
    background-color: #ff4da6 !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 5px 15px rgba(255, 105, 180, 0.3) !important;
}

.btn-secondary {
    background-color: transparent !important;
    border: 1px solid #333333 !important;
    color: #ff99cc !important;
    padding: 12px 30px !important;
    border-radius: 10px !important;
    font-size: 0.95rem !important;
    font-weight: 500 !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
}

.btn-secondary:hover {
    border-color: #ff69b4 !important;
    color: #ff69b4 !important;
}

/* Requirements Box */
.requirements-box {
    background-color: #1a1a1a !important;
    border: 1px solid #333333 !important;
    border-radius: 10px !important;
    padding: 15px !important;
}

.requirements-title {
    color: #ff99cc !important;
    font-size: 0.95rem !important;
    margin-bottom: 10px !important;
}

.requirements-list {
    list-style: none !important;
    padding: 0 !important;
    margin: 0 !important;
}

.requirements-list li {
    color: #666666 !important;
    font-size: 0.9rem !important;
    margin-bottom: 5px !important;
    display: flex !important;
    align-items: center !important;
}

.requirements-list li i {
    color: #ff69b4 !important;
    font-size: 8px !important;
    margin-right: 10px !important;
}

/* Tab Panes */
.tab-pane {
    display: none !important;
}

.tab-pane.active {
    display: block !important;
    animation: fadeIn 0.3s ease !important;
}

/* Animations */
@keyframes fadeIn {
    from {
        opacity: 0 !important;
        transform: translateY(10px) !important;
    }
    to {
        opacity: 1 !important;
        transform: translateY(0) !important;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr !important;
    }
    
    .form-group.full-width {
        grid-column: span 1 !important;
    }
    
    .tabs-nav {
        flex-direction: column !important;
    }
    
    .tab-btn {
        max-width: 100% !important;
    }
    
    .form-actions {
        flex-direction: column !important;
    }
    
    .btn-primary, .btn-secondary {
        width: 100% !important;
    }
}
</style>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if there are password errors or success message
    const hasPasswordErrors = {{ $errors->has('current_password') || $errors->has('new_password') || $errors->has('new_password_confirmation') ? 'true' : 'false' }};
    const hasPasswordSuccess = {{ session('success') ? 'true' : 'false' }};
    
    // Set active tab based on errors or success
    if (hasPasswordErrors || hasPasswordSuccess) {
        document.getElementById('security-tab').classList.add('active');
        document.getElementById('profile-tab').classList.remove('active');
        document.querySelector('[data-tab="security"]').classList.add('active');
        document.querySelector('[data-tab="profile"]').classList.remove('active');
    }
    
    // Tab switching
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Remove active class from all buttons and panes
            tabBtns.forEach(b => b.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));
            
            // Add active class to current button and pane
            this.classList.add('active');
            document.getElementById(tabId + '-tab').classList.add('active');
        });
    });
    
    // File input display
    const fileInput = document.getElementById('file-input');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const fileName = this.files[0]?.name;
            const textElement = this.parentElement.querySelector('.file-input-text');
            if (fileName && textElement) {
                textElement.textContent = fileName;
            }
        });
    }
});
</script>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endsection