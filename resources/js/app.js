// Lightweight scroll-snap carousels.
// Markup contract:
//   [data-carousel]            wrapper
//     [data-carousel-track]    horizontally scrolling flex container
//     [data-carousel-prev]     previous button (optional)
//     [data-carousel-next]     next button (optional)
function initCarousels() {
    document.querySelectorAll('[data-carousel]').forEach((root) => {
        const track = root.querySelector('[data-carousel-track]');
        if (!track) return;
        const prev = root.querySelector('[data-carousel-prev]');
        const next = root.querySelector('[data-carousel-next]');

        // Scroll by roughly one "page" (80% of the visible width).
        const step = () => Math.max(Math.round(track.clientWidth * 0.8), 200);

        prev?.addEventListener('click', () =>
            track.scrollBy({ left: -step(), behavior: 'smooth' }));
        next?.addEventListener('click', () =>
            track.scrollBy({ left: step(), behavior: 'smooth' }));

        // Disable arrows at the track's extremes.
        const update = () => {
            const max = track.scrollWidth - track.clientWidth - 2;
            if (prev) prev.disabled = track.scrollLeft <= 2;
            if (next) next.disabled = track.scrollLeft >= max;
        };
        track.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        update();
    });
}

// Deal-of-the-week clock. The server renders the opening numbers from the
// soonest sale_ends_at, so the block is correct without JS; this just keeps
// ticking from the ISO target on [data-countdown].
function initCountdowns() {
    document.querySelectorAll('[data-countdown]').forEach((root) => {
        const target = new Date(root.dataset.countdown).getTime();
        if (Number.isNaN(target)) return;

        const parts = {};
        root.querySelectorAll('[data-countdown-part]').forEach((el) => {
            parts[el.dataset.countdownPart] = el;
        });

        const pad = (n) => String(Math.max(n, 0)).padStart(2, '0');

        const tick = () => {
            const left = Math.max(target - Date.now(), 0);
            const seconds = Math.floor(left / 1000);
            if (parts.days) parts.days.textContent = pad(Math.floor(seconds / 86400));
            if (parts.hours) parts.hours.textContent = pad(Math.floor((seconds % 86400) / 3600));
            if (parts.minutes) parts.minutes.textContent = pad(Math.floor((seconds % 3600) / 60));
            if (parts.seconds) parts.seconds.textContent = pad(seconds % 60);
            if (left === 0) clearInterval(timer);
        };

        const timer = setInterval(tick, 1000);
        tick();
    });
}

// Selects that submit their form on change (the listing sort control).
// A <noscript> button covers the JS-off case.
function initAutoSubmit() {
    document.querySelectorAll('[data-auto-submit]').forEach((el) => {
        el.addEventListener('change', () => el.form?.submit());
    });
}

// Sticky header: once the page has scrolled past the announcement bar, mark
// the header so CSS can collapse the bar and drop a shadow. The header itself
// is position:sticky, so no layout maths here.
function initStickyHeader() {
    const header = document.querySelector('[data-header]');
    if (!header) return;

    const update = () => header.classList.toggle('is-scrolled', window.scrollY > 30);

    window.addEventListener('scroll', update, { passive: true });
    update();
}

// Hero slideshow: cross-fading slides with dots, autoplay, and a pause while
// the pointer is over it. Slide one is rendered active, so the hero is intact
// before this runs.
function initHero() {
    const hero = document.querySelector('[data-hero]');
    if (!hero) return;

    const slides = [...hero.querySelectorAll('[data-hero-slide]')];
    const dots = [...hero.querySelectorAll('[data-hero-dot]')];
    if (slides.length < 2) return;

    let index = slides.findIndex((s) => s.classList.contains('is-active'));
    if (index < 0) index = 0;

    const show = (next) => {
        index = (next + slides.length) % slides.length;
        slides.forEach((slide, i) => slide.classList.toggle('is-active', i === index));
        dots.forEach((dot, i) => dot.classList.toggle('is-active', i === index));
    };

    let timer = setInterval(() => show(index + 1), 6000);
    const restart = () => {
        clearInterval(timer);
        timer = setInterval(() => show(index + 1), 6000);
    };

    dots.forEach((dot, i) =>
        dot.addEventListener('click', () => {
            show(i);
            restart();
        }));

    hero.querySelector('[data-hero-prev]')?.addEventListener('click', () => {
        show(index - 1);
        restart();
    });
    hero.querySelector('[data-hero-next]')?.addEventListener('click', () => {
        show(index + 1);
        restart();
    });

    hero.addEventListener('mouseenter', () => clearInterval(timer));
    hero.addEventListener('mouseleave', restart);

    show(index);
}

// Product gallery: clicking a thumbnail swaps the main image.
function initGalleries() {
    document.querySelectorAll('[data-gallery]').forEach((gallery) => {
        const main = gallery.querySelector('[data-gallery-main]');
        const thumbs = [...gallery.querySelectorAll('[data-gallery-thumb]')];
        if (!main || thumbs.length < 2) return;

        thumbs.forEach((thumb) => {
            thumb.addEventListener('click', () => {
                main.src = thumb.dataset.full || thumb.querySelector('img')?.src;
                thumbs.forEach((t) => t.classList.toggle('is-active', t === thumb));
            });
        });
    });
}

// Hover zoom: scale the image and track the cursor with transform-origin, so
// the point under the pointer is the point magnified.
function initZoom() {
    document.querySelectorAll('[data-zoom]').forEach((box) => {
        const img = box.querySelector('img');
        if (!img || window.matchMedia('(hover: none)').matches) return;

        box.addEventListener('mousemove', (e) => {
            const rect = box.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            img.style.transformOrigin = `${x}% ${y}%`;
        });
        box.addEventListener('mouseenter', () => img.classList.add('is-zoomed'));
        box.addEventListener('mouseleave', () => {
            img.classList.remove('is-zoomed');
            img.style.transformOrigin = 'center';
        });
    });
}

// Quantity stepper on the product page.
function initQuantitySteppers() {
    document.querySelectorAll('[data-qty]').forEach((stepper) => {
        const input = stepper.querySelector('input');
        if (!input) return;

        const step = (delta) => {
            const min = Number(input.min || 1);
            const max = Number(input.max || 99);
            input.value = Math.min(Math.max(Number(input.value || 1) + delta, min), max);
        };

        stepper.querySelector('[data-qty-down]')?.addEventListener('click', () => step(-1));
        stepper.querySelector('[data-qty-up]')?.addEventListener('click', () => step(1));
    });
}

// "Clear" next to the size picker unselects the chosen size.
function initClearables() {
    document.querySelectorAll('[data-clear-target]').forEach((button) => {
        button.addEventListener('click', () => {
            document
                .querySelectorAll(`input[name="${button.dataset.clearTarget}"]`)
                .forEach((input) => { input.checked = false; });
        });
    });
}

// Tab strips (product details). The first panel is rendered visible, so with
// JS off the page still shows the description.
function initTabs() {
    document.querySelectorAll('[data-tabs]').forEach((root) => {
        const tabs = [...root.querySelectorAll('[data-tab]')];
        const panels = [...root.querySelectorAll('[data-tab-panel]')];

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                tabs.forEach((t) => t.classList.toggle('is-active', t === tab));
                panels.forEach((p) => p.classList.toggle('hidden', p.dataset.tabPanel !== tab.dataset.tab));
            });
        });
    });
}

// Sticky buy bar on the product page: show it once the main Add To Bag form
// has scrolled out of view, and keep its size select in sync with the radios.
function initStickyBuy() {
    const bar = document.querySelector('[data-sticky-buy]');
    const form = document.querySelector('[data-buy-form]');
    if (!bar || !form) return;

    const toggle = (visible) => {
        bar.classList.toggle('is-visible', visible);
        // Lets the floating WhatsApp button step out of the bar's way.
        document.body.classList.toggle('has-sticky-buy', visible);
    };

    const past = () => form.getBoundingClientRect().bottom < 0;

    new IntersectionObserver(
        ([entry]) => toggle(!entry.isIntersecting && entry.boundingClientRect.top < 0),
        { threshold: 0 },
    ).observe(form);

    // The observer's first callback reflects the state at registration time,
    // which is wrong when the browser restores a scrolled position. Settle it.
    toggle(past());
    window.addEventListener('scroll', () => toggle(past()), { passive: true });

    // Keep the two size pickers pointing at the same variant.
    const select = bar.querySelector('[data-sticky-size]');
    if (!select) return;

    const radios = [...form.querySelectorAll('input[name="variant_id"]')];
    radios.forEach((radio) =>
        radio.addEventListener('change', () => {
            if (radio.checked) select.value = radio.value;
        }));

    select.addEventListener('change', () => {
        radios.forEach((radio) => { radio.checked = radio.value === select.value; });
    });
}

function init() {
    initCarousels();
    initTabs();
    initStickyBuy();
    initGalleries();
    initZoom();
    initQuantitySteppers();
    initClearables();
    initCountdowns();
    initAutoSubmit();
    initStickyHeader();
    initHero();
}

if (document.readyState !== 'loading') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}
