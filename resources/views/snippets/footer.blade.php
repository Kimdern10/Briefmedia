<footer class="footer">
    <div class="container-fluid">
        <!-- Footer Widget Area -->
        <div class="row py-5">
            <!-- Widget 1: About Blog - Now col-lg-6 -->
            <div class="col-lg-6 col-md-12 mb-4 mb-lg-0">
                <div class="footer-widget">
                    <h5 class="footer-widget__title">About BriefMedia</h5>
                    <p class="footer-widget__text">
                  Brief Media is a dynamic and fast-growing media platform committed to delivering timely information, engaging entertainment, and strategic media services. With a strong focus on credibility, creativity, and audience engagement, Brief Media positions itself as a trusted voice in the evolving media landscape.
The platform operates across multiple segments, including news reporting, entertainment updates, comprehensive events coverage, brand management, and a broad spectrum of media-related activities. Through professional storytelling and digital innovation, Brief Media connects individuals, organizations, and communities to relevant information while helping brands build visibility and influence.
Driven by excellence and a passion for impactful communication, Brief Media continues to serve as a reliable hub for media solutions and public engagement.
                    </p>
                    <div class="footer-widget__social">
                        <a href="#" class="footer-widget__social-link"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="footer-widget__social-link"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="footer-widget__social-link"><i class="bi bi-tiktok"></i></a>
                        <a href="#" class="footer-widget__social-link"><i class="bi bi-twitter-x"></i></a>
                    </div>
                </div>
            </div>

            <!-- Widget 2: Newsletter & Social - Now col-lg-6 -->
            <div class="col-lg-6 col-md-12">
    <div class="footer-widget">
        <h5 class="footer-widget__title">Newsletter</h5>
        <p class="footer-widget__text">Subscribe to get the latest posts in your inbox.</p>

        <!-- Newsletter Form -->
        <form action="{{ route('newsletter.subscribe') }}" method="POST" class="footer-widget__newsletter">
            @csrf
            <div class="input-group">
                <input type="email" name="email" class="form-control" placeholder="Your email" required>
                <button class="btn btn-subscribe" type="submit">Join</button>
            </div>
        </form>

        <!-- Success / Error Messages -->
        @if(session('success'))
            <p class="text-success mt-2">{{ session('success') }}</p>
        @endif
        @if(session('error'))
            <p class="text-danger mt-2">{{ session('error') }}</p>
        @endif

        <!-- Contact Info -->
        <div class="footer-widget__contact mt-4">
            <h6 class="text-white mb-3">Connect With Us:</h6>
            <div class="footer-widget__handles">
                <p class="mb-2"><i class="bi bi-facebook me-2"></i>BriefMedia</p>
                <p class="mb-2"><i class="bi bi-instagram me-2"></i>@BriefMedia</p>
                <p class="mb-2"><i class="bi bi-tiktok me-2"></i>@BriefMedia</p>
                <p class="mb-0"><i class="bi bi-twitter-x me-2"></i>@BriefMedia</p>
            </div>
        </div>
    </div>
</div>
        </div>

        <!-- Back to Top Button -->
        <div class="row">
            <div class="col-12 text-center mb-2">
                <div class="btn-back-top" id="backToTopContainer">
                    <a href="javascript:void(0)" class="btn-back-top__link" id="backToTopBtn">
                        <i class="bi bi-arrow-up btn-back-top__icon"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Bottom Footer -->
        <div class="row py-3 footer-bottom">
            <div class="col-12 text-center">
                <h6 class="footer-copyright mb-0">
                    © <script>document.write(new Date().getFullYear())</script> 
                    <span class="text-pink">BriefMedia</span>. All rights reserved.
                </h6>
            </div>
        </div>
    </div>
</footer>

<!-- Scroll to Top JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get the button
    const backToTopBtn = document.getElementById('backToTopBtn');
    const backToTopContainer = document.getElementById('backToTopContainer');
    
    if (backToTopBtn) {
        // Add click event
        backToTopBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Show/hide button based on scroll position
    if (backToTopContainer) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTopContainer.classList.add('show');
            } else {
                backToTopContainer.classList.remove('show');
            }
        });
    }
});
</script>

<style>
.footer {
    background: #1e2a3a;
    color: #94a3b8;
    font-size: 0.95rem;
    border-top: 5px solid #ff69b4;
}

/* Footer Widgets */
.footer-widget__title {
    color: #fff;
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    position: relative;
    padding-bottom: 10px;
}

.footer-widget__title::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 40px;
    height: 2px;
    background: #ff69b4;
}

.footer-widget__text {
    line-height: 1.7;
    margin-bottom: 1rem;
    white-space: pre-line;
}

/* Social Icons */
.footer-widget__social {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.footer-widget__social-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    background: rgba(255,255,255,0.05);
    color: #94a3b8;
    border-radius: 50%;
    text-decoration: none;
    transition: all 0.3s ease;
}

.footer-widget__social-link:hover {
    background: #ff69b4;
    color: #fff;
    transform: translateY(-3px);
}

/* Newsletter */
.footer-widget__newsletter .input-group {
    background: rgba(255,255,255,0.05);
    border-radius: 6px;
    overflow: hidden;
}

.footer-widget__newsletter .form-control {
    background: transparent;
    border: none;
    color: #fff;
    padding: 10px 15px;
}

.footer-widget__newsletter .form-control::placeholder {
    color: #64748b;
}

.footer-widget__newsletter .form-control:focus {
    box-shadow: none;
    background: rgba(255,255,255,0.1);
}

.btn-subscribe {
    background: #ff69b4;
    color: #fff;
    border: none;
    padding: 10px 20px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-subscribe:hover {
    background: #ff4da6;
    color: #fff;
}

/* Contact Info */
.footer-widget__contact h6 {
    color: #fff;
    font-size: 1rem;
}

.footer-widget__handles p {
    color: #94a3b8;
    transition: color 0.3s ease;
    margin-bottom: 8px;
}

.footer-widget__handles p:hover {
    color: #ff69b4;
}

.footer-widget__handles i {
    color: #ff69b4;
    width: 20px;
    display: inline-block;
}

/* Back to Top Button */
.btn-back-top {
    margin: 20px 0;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease;
}

.btn-back-top.show {
    opacity: 1;
    visibility: visible;
}

.btn-back-top__link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    background: rgba(255, 105, 180, 0.1);
    color: #ff69b4;
    border-radius: 50%;
    text-decoration: none;
    transition: all 0.3s ease;
    font-size: 1.2rem;
    border: 2px solid #ff69b4;
    cursor: pointer;
}

.btn-back-top__link:hover {
    background: #ff69b4;
    color: #fff;
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(255, 105, 180, 0.4);
}

/* Bottom Footer */
.footer-bottom {
    border-top: 1px solid rgba(255,255,255,0.05);
    background: rgba(0,0,0,0.2);
}

.footer-copyright {
    color: #94a3b8;
    font-weight: 400;
    font-size: 0.9rem;
}

.text-pink {
    color: #ff69b4;
    font-weight: 500;
}

/* Responsive */
@media (max-width: 768px) {
    .footer-widget__title::after {
        left: 50%;
        transform: translateX(-50%);
    }
    
    .footer-widget {
        text-align: center;
    }
    
    .footer-widget__social {
        justify-content: center;
    }
    
    .footer-widget__handles p {
        display: inline-block;
        margin: 5px 10px;
    }
}
</style>