@extends('layouts.app')

@section('content')
<section class="contact-page py-5">
    <div class="container">
        <!-- Section Title -->
        <h2 class="mb-5 text-center">Contact Us</h2>

        <!-- Contact Info -->
        <div class="row justify-content-center mb-5">
            <div class="col-md-8 text-center">

            <h2 class="mb-5 text-center">Contact Us</h2>
                <p class="mb-2">
                    <strong>Phone:</strong> +234 7041904305
                </p>
                <p class="mb-2">
                    <strong>Email:</strong> info@briefMedia.com
                </p>
                <p>
                    <strong>Address:</strong> 123 BriefMedia Street, Lagos, Nigeria
                </p>
            </div>
        </div>

        <!-- Map -->
        <div class="row justify-content-center">
            <div class="col-md-10">
                <h5 class="mb-3 text-center">Our Location</h5>
                <div class="map-responsive" style="overflow:hidden; padding-bottom:56.25%; position:relative; height:0;">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d63461.03827227117!2d7.0356263407962585!3d6.222144681909829!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x104382bd8b94e753%3A0xcf2391eb8abd4753!2sAwka%2C%20Anambra!5e0!3m2!1sen!2sng!4v1772705731852!5m2!1sen!2sng"
                        width="100%" 
                        height="500" 
                        style="border:0; position:absolute; top:0; left:0; width:100%; height:100%;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection