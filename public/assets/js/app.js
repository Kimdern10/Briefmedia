{
    const updateScrollWidth = () => {
        const scrollWidth = window.innerWidth - document.documentElement.clientWidth;
        // Ensure it's never an invalid large value
        const validScrollWidth = scrollWidth > 0 && scrollWidth < 50 ? scrollWidth : 0;
        document.documentElement.style.setProperty("--body-scroll-width", `${validScrollWidth}px`);
    };

    window.addEventListener("resize", updateScrollWidth);
    updateScrollWidth();
}
{
    const e = () => {
            setDarkMode(!isDarkMode());
            const e = isDarkMode();
            localStorage.setItem("darkMode", e ? "1" : "0")
        },
        t = e => {
            e.checked = isDarkMode()
        };
    document.querySelectorAll("[data-darkmode-toggle] input, [data-darkmode-switch] input").forEach((n => {
        n.addEventListener("change", e), t(n)
    }))
}
document.querySelectorAll(".uc-horizontal-scroll").forEach((e => {
    e.addEventListener("wheel", (t => {
        t.preventDefault(), e.scrollBy({
            left: t.deltaY,
            behavior: "smooth"
        })
    }))
})), document.addEventListener("DOMContentLoaded", (() => {
    const e = document.querySelector("[data-uc-backtotop]");
    if (!e) return;
    e.addEventListener("click", (e => {
        e.preventDefault(), window.scrollTo({
            top: 0,
            behavior: "smooth"
        })
    }));
    let t = 0;
    window.addEventListener("scroll", (() => {
        const n = document.body.getBoundingClientRect().top;
        e.parentNode.classList.toggle("uc-active", n <= t), t = n
    }))
})), document.addEventListener("DOMContentLoaded", (function() {
    let e = [].slice.call(document.querySelectorAll("video.video-lazyload"));

    function t(e) {
        let t = e.querySelector("source");
        t.src = t.dataset.src, e.load(), e.muted = !0, "visible" === document.visibilityState ? e.play() : document.addEventListener("visibilitychange", (function t() {
            "visible" === document.visibilityState && (e.play(), document.removeEventListener("visibilitychange", t))
        }))
    }
    if ("IntersectionObserver" in window) {
        let n = new IntersectionObserver((function(e, o) {
            e.forEach((function(e) {
                if (e.isIntersecting) {
                    let o = e.target;
                    t(o), n.unobserve(o)
                }
            }))
        }));
        e.forEach((function(e) {
            n.observe(e), e.getBoundingClientRect().top < window.innerHeight && e.getBoundingClientRect().bottom > 0 && (t(e), n.unobserve(e))
        }))
    } else e.forEach((function(e) {
        t(e)
    }))
}));

const header = document.querySelector('.uc-header');

window.addEventListener('scroll', () => {
  if (window.pageYOffset > 150) {
    header.classList.add('uc-navbar-sticky'); // 100px er por add
  } else {
    header.classList.remove('uc-navbar-sticky'); // 100px er niche remove
  }
});