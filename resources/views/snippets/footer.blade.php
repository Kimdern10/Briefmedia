@unless(View::hasSection('auth_page'))
    <section class="bm-section--tight" style="margin-top: 40px;">
        <div class="bm-container">
            <div class="bm-cta bm-reveal">
                <div>
                    <span class="bm-section__eyebrow" style="color: var(--bm-accent);"><i class="bi bi-envelope-paper-heart"></i> Newsletter</span>
                    <h3 class="bm-cta__title">The day's best stories, in your inbox.</h3>
                    <p class="bm-cta__text">Join our readers and be the first to know when new posts drop. No spam. Unsubscribe anytime.</p>
                </div>
                <livewire:newsletter-signup />
            </div>
        </div>
    </section>
@endunless

<footer class="bm-footer">
    <div class="bm-container">
        <div class="bm-footer__grid">
            <div>
                <a href="{{ url('/') }}" class="bm-logo">
                    <span class="bm-logo__mark">B</span>
                    <span class="bm-logo__text">Brief<b>Media</b></span>
                </a>
                <p class="bm-footer__about">
                    Brief Media is a dynamic, fast-growing media platform delivering timely news, engaging entertainment,
                    events coverage and strategic media services, connecting people, organizations and communities
                    through credible, creative storytelling.
                </p>
                <div class="bm-socials">
                    <a href="https://www.facebook.com/BriefMediablog" target="_blank" rel="noopener" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
                    <a href="#" aria-label="X"><i class="bi bi-twitter-x"></i></a>
                </div>
            </div>

            <div>
                <h6 class="bm-footer__title">Sections</h6>
                <ul class="bm-footer__links">
                    @foreach($navCategories->concat($moreCategories)->take(7) as $footerCategory)
                        <li><a href="{{ route('category.page', $footerCategory->slug) }}">{{ $footerCategory->name }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h6 class="bm-footer__title">Company</h6>
                <ul class="bm-footer__links">
                    <li><a href="{{ route('contact') }}">Contact us</a></li>
                    <li><a href="{{ route('terms') }}">Terms &amp; Conditions</a></li>
                    <li><a href="{{ route('privacy') }}">Privacy Policy</a></li>
                    <li><a href="https://www.facebook.com/Briefmediablog" target="_blank" rel="noopener">Facebook page</a></li>
                    @guest
                        <li><a href="{{ route('sign-up') }}">Create an account</a></li>
                    @endguest
                </ul>
            </div>
        </div>

        <div class="bm-footer__bottom">
            <span>© {{ date('Y') }} BriefMedia. All rights reserved.</span>
            <nav>
                <a href="{{ route('terms') }}">Terms</a>
                <a href="{{ route('privacy') }}">Privacy</a>
                <a href="{{ route('contact') }}">Contact</a>
            </nav>
        </div>
    </div>
</footer>

<button type="button" class="bm-icon-btn bm-to-top" id="bm-to-top" aria-label="Back to top">
    <i class="bi bi-arrow-up"></i>
</button>
