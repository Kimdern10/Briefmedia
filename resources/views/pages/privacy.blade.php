@extends('layouts.app')

@section('title', 'Privacy Policy | ' . ($globalSeo->site_name ?? config('app.name')))

@section('content')
<div class="bm-container">
<article class="bm-page">
    <span class="bm-section__eyebrow">Legal</span>

    <h1>Privacy Policy</h1>

    <p><em>Last updated: October 17, 2026</em></p>

    <p>
        This Privacy Policy explains what information <strong>BriefMedia</strong> collects when you use
        <a href="{{ url('/') }}">{{ parse_url(url('/'), PHP_URL_HOST) }}</a>, how we use it, and the choices you have.
        By using this website, you agree to the practices described here.
    </p>

    <h3>1. Information we collect</h3>
    <p>We only collect what we need to run the website and its features:</p>
    <ul>
        <li><strong>Account details:</strong> your name, email address and password when you create an account. Passwords are stored encrypted and we cannot read them.</li>
        <li><strong>Profile details:</strong> the phone number, address and profile picture you choose to add on your profile page.</li>
        <li><strong>Your activity:</strong> comments and replies you post, and the stories you like.</li>
        <li><strong>Newsletter:</strong> your email address if you subscribe to our newsletter.</li>
        <li><strong>Messages:</strong> your name, email address and message when you contact us through the contact page.</li>
        <li><strong>Technical information:</strong> basic data your browser sends, such as your IP address and device type, which our hosting provider records in server logs.</li>
    </ul>

    <h3>2. How we use your information</h3>
    <ul>
        <li>To create and secure your account, including sending one-time verification and password reset codes by email.</li>
        <li>To show your comments and likes on stories.</li>
        <li>To send our newsletter, if you subscribed.</li>
        <li>To reply to messages you send us.</li>
        <li>To count story views and understand which stories readers find useful.</li>
        <li>To keep the website safe and prevent abuse, spam and fraud.</li>
    </ul>
    <p>We do not sell your personal information.</p>

    <h3>3. Cookies and similar technologies</h3>
    <p>We use a small number of cookies and browser storage to make the website work:</p>
    <ul>
        <li><strong>Essential cookies</strong> keep you logged in and protect forms from misuse.</li>
        <li><strong>View counting:</strong> a cookie remembers which stories you have read so a view is only counted once.</li>
        <li><strong>Preferences:</strong> your light or dark mode choice is saved in your browser.</li>
    </ul>
    <p>You can block or delete cookies in your browser settings, but some features, such as staying logged in, may stop working.</p>

    <h3>4. Advertising and analytics</h3>
    <p>
        We use <strong>Google AdSense</strong> to show ads and <strong>Google Analytics</strong> to understand how visitors use the website.
        These services may use their own cookies to show relevant ads and measure traffic. Google's use of this data is covered by
        <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Google's Privacy Policy</a>.
        You can manage personalised ads at <a href="https://adssettings.google.com" target="_blank" rel="noopener">Google Ad Settings</a>.
    </p>

    <h3>5. Sharing your information</h3>
    <p>We only share information when it is needed to run the website or required by law:</p>
    <ul>
        <li>With service providers who help us operate, such as our web hosting and email providers.</li>
        <li>With Google, as described in section 4.</li>
        <li>When required by law, or to protect the rights and safety of BriefMedia, our users or the public.</li>
    </ul>
    <p>Your name is shown publicly next to comments you post. Your email address, phone number and address are never shown publicly.</p>

    <h3>6. Sharing stories on social media</h3>
    <p>
        When you use our share buttons, you are taken to Facebook, X (Twitter), LinkedIn or WhatsApp.
        Those services handle your information under their own privacy policies.
    </p>

    <h3>7. How long we keep your information</h3>
    <p>
        We keep your account information for as long as your account exists. Newsletter emails are kept until you unsubscribe.
        Contact messages are kept only as long as needed to respond. When you ask us to delete your account, we remove your
        personal information unless we must keep it for legal reasons.
    </p>

    <h3>8. Your choices and rights</h3>
    <ul>
        <li><strong>Update your details</strong> at any time from your profile page.</li>
        <li><strong>Unsubscribe</strong> from the newsletter using the link at the bottom of any newsletter email.</li>
        <li><strong>Ask for a copy, correction or deletion</strong> of your personal information by <a href="{{ route('contact') }}">contacting us</a>.</li>
    </ul>
    <p>If you are in Nigeria, you also have rights under the Nigeria Data Protection Act 2023.</p>

    <h3>9. Security</h3>
    <p>
        We take reasonable steps to protect your information, including encrypting passwords and using a secure (HTTPS) connection.
        No website can be completely secure, so please use a strong password that you don't use anywhere else.
    </p>

    <h3>10. Children</h3>
    <p>This website is not directed at children under 13, and we do not knowingly collect their personal information.</p>

    <h3>11. Changes to this policy</h3>
    <p>
        We may update this Privacy Policy from time to time. The date at the top shows when it was last changed.
        Continuing to use the website after an update means you accept the new policy.
    </p>

    <h3>12. Contact us</h3>
    <p>
        Questions about this policy or your information? <a href="{{ route('contact') }}">Contact us</a>
        or email <a href="mailto:support@briefMediablog.com">support@briefMediablog.com</a>.
    </p>
</article>
</div>
@endsection
