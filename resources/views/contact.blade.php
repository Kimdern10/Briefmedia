@extends('layouts.app')

@section('title', 'Contact us | ' . ($globalSeo->site_name ?? config('app.name')))

@section('content')
<main>
    <section class="bm-page-hero">
        <div class="bm-container">
            <nav class="bm-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">Home</a>
                <i class="bi bi-chevron-right" style="font-size: 10px;"></i>
                <span>Contact</span>
            </nav>
            <h1 class="bm-page-hero__title">Let's <em>talk</em>.</h1>
            <p class="bm-page-hero__text">Story tips, partnerships, events coverage or brand work. Our team is a message away.</p>
        </div>
    </section>

    <section class="bm-section--tight">
        <div class="bm-container">
            <div class="bm-contact">
                <div class="bm-contact__card">
                    <i class="bi bi-telephone"></i>
                    <small>Phone</small>
                    <p><a href="tel:+2347041904305">+234 704 190 4305</a></p>
                </div>
                <div class="bm-contact__card">
                    <i class="bi bi-envelope"></i>
                    <small>Email</small>
                    <p><a href="mailto:support@briefMediablog.com">support@briefMediablog.com</a></p>
                </div>
                <div class="bm-contact__card">
                    <i class="bi bi-geo-alt"></i>
                    <small>Address</small>
                    <p>123 BriefMedia Street, Awka, Nigeria</p>
                </div>
            </div>

            <div class="bm-map">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d63461.03827227117!2d7.0356263407962585!3d6.222144681909829!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x104382bd8b94e753%3A0xcf2391eb8abd4753!2sAwka%2C%20Anambra!5e0!3m2!1sen!2sng!4v1772705731852!5m2!1sen!2sng"
                    allowfullscreen=""
                    loading="lazy"
                    title="BriefMedia location map"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </section>
</main>
@endsection
