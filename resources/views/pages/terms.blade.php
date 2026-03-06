@extends('layouts.app')

@section('title', 'Terms & Conditions - BriefMedia')

@section('content')
<!-- Terms & Conditions page -->

<div class="container py-5" style="margin-top: 100px;">


    <h1 class="mb-12">Terms & Conditions</h1>


    <p>Welcome to <strong>BriefMedia</strong>. By accessing and using this website, you agree to comply with the following terms and conditions. Please read them carefully.</p>

    <h3>1. Acceptance of Terms</h3>
    <p>By using this website, you accept and agree to be bound by these Terms & Conditions. If you do not agree, please do not use the website.</p>

    <h3>2. Use of the Website</h3>
    <p>You agree to use this website only for lawful purposes and in a way that does not infringe the rights of others or restrict their use of the website. You must not:</p>
    <ul>
        <li>Post harmful, abusive, or illegal content.</li>
        <li>Attempt to hack, damage, or disrupt the website.</li>
        <li>Use the website for fraudulent activities.</li>
    </ul>

    <h3>3. User Accounts</h3>
    <p>If you create an account on BriefMedia, you are responsible for maintaining your login credentials and account security. You must provide accurate information and notify us immediately of any unauthorized use of your account.</p>

    <h3>4. Content Ownership</h3>
    <p>All content on BriefMedia, including text, images, and graphics, is owned by the website unless stated otherwise. You may not copy, reproduce, or distribute any content without our permission.</p>

    <h3>5. User-Generated Content</h3>
    <p>Users may submit comments or other content. By submitting content, you grant BriefMedia the right to display, modify, or remove such content if it violates our policies. We reserve the right to remove content that is offensive, illegal, or inappropriate.</p>

    <h3>6. Third-Party Links</h3>
    <p>This website may contain links to third-party websites. BriefMedia is not responsible for the content or privacy practices of those websites.</p>

    <h3>7. Limitation of Liability</h3>
    <p>BriefMedia does not guarantee that the website will always be available or error-free. We are not liable for any damages resulting from the use or inability to use this website.</p>

    <h3>8. Privacy</h3>
    <p>Your use of this website is also governed by our <a href="{{ route('privacy') }}">Privacy Policy</a>, which explains how we collect and use your information.</p>

    <h3>9. Changes to Terms</h3>
    <p>BriefMedia reserves the right to update or modify these Terms & Conditions at any time without prior notice. Continued use of the website after changes means you accept the new terms.</p>

    <h3>10. Contact Information</h3>
    <p>If you have any questions about these Terms & Conditions, you may <a href="{{ route('contact') }}">contact us</a>.</p>
</div>
@endsection