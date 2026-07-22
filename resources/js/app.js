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

// Header search. The form is a plain GET onto the listing and works on its
// own; JS only reveals the panel and puts the caret in the field.
function initSearch() {
    const panel = document.querySelector('[data-search-panel]');
    const input = panel?.querySelector('[data-search-input]');
    const toggles = document.querySelectorAll('[data-search-toggle]');
    if (!panel || !toggles.length) return;

    const setOpen = (open) => {
        panel.hidden = !open;
        toggles.forEach((t) => t.setAttribute('aria-expanded', String(open)));
        if (open) input?.focus();
    };

    toggles.forEach((toggle) => {
        toggle.addEventListener('click', () => setOpen(panel.hidden));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !panel.hidden) setOpen(false);
    });
}

// Toast, bottom-centre. Replaces the flash banner for anything posted over
// fetch — there is no page load to render a banner on.
function toast(message) {
    if (!message) return;

    let host = document.querySelector('[data-toasts]');
    if (!host) {
        host = document.createElement('div');
        host.setAttribute('data-toasts', '');
        host.className = 'tc-toasts';
        document.body.appendChild(host);
    }

    const note = document.createElement('div');
    note.className = 'tc-toast';
    note.setAttribute('role', 'status');
    note.textContent = message;
    host.appendChild(note);

    requestAnimationFrame(() => note.classList.add('is-in'));
    setTimeout(() => {
        note.classList.remove('is-in');
        note.addEventListener('transitionend', () => note.remove(), { once: true });
    }, 3200);
}

// The shared slide-over (bag / favourites). The trigger carries the fragment
// URL in data-drawer-open; the panel fetches it on open and again after any
// change made inside it, so it is never showing a stale bag.
const drawer = {
    url: null,

    open(url) {
        const panel = document.querySelector('[data-drawer]');
        const overlay = document.querySelector('[data-drawer-overlay]');
        if (!panel || !overlay) return;

        this.url = url;
        panel.classList.add('is-open');
        overlay.classList.add('is-open');
        document.body.classList.add('has-drawer');
        this.load();
    },

    close() {
        document.querySelector('[data-drawer]')?.classList.remove('is-open');
        document.querySelector('[data-drawer-overlay]')?.classList.remove('is-open');
        document.body.classList.remove('has-drawer');
    },

    get isOpen() {
        return document.querySelector('[data-drawer]')?.classList.contains('is-open') ?? false;
    },

    async load() {
        const body = document.querySelector('[data-drawer-body]');
        if (!body || !this.url) return;

        try {
            const response = await fetch(this.url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            body.innerHTML = await response.text();
        } catch {
            body.innerHTML = '<div class="flex h-full items-center justify-center px-6 text-center text-[14px] font-light text-muted-2">Could not load that just now.</div>';
        }
    },
};

function initDrawer() {
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-drawer-open]');
        if (trigger) {
            event.preventDefault();
            drawer.open(trigger.dataset.drawerOpen);
            return;
        }

        if (event.target.closest('[data-drawer-close], [data-drawer-overlay]')) {
            drawer.close();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && drawer.isOpen) drawer.close();
    });
}

function setCount(selector, value) {
    document.querySelectorAll(selector).forEach((el) => { el.textContent = value; });
}

// Add-to-bag and favourite, without losing the page.
//
// Any [data-async] form posts over fetch; the controllers answer those with
// JSON (bag count, favourite state) instead of a redirect. "Buy now" is the
// deliberate exception — it submits normally, because it has to land on
// checkout.
function initAsyncForms() {
    document.addEventListener('submit', async (event) => {
        const form = event.target.closest('form[data-async]');
        if (!form) return;

        const submitter = event.submitter;
        if (submitter?.value === 'buy') return;

        event.preventDefault();

        const body = new FormData(form);
        if (submitter?.name) body.append(submitter.name, submitter.value);

        const buttons = form.querySelectorAll('button[type="submit"]');
        buttons.forEach((b) => { b.disabled = true; });

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body,
                headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
                credentials: 'same-origin',
            });
            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                toast(data.message || 'Sorry — that did not work. Please try again.');
                return;
            }

            if (data.bagCount !== undefined) setCount('[data-bag-count]', data.bagCount);
            if (data.favoritesCount !== undefined) setCount('[data-fav-count]', data.favoritesCount);
            if (data.favorited !== undefined) applyFavorite(form, data.favorited);

            // Forms living inside the drawer redraw it, so quantities, totals
            // and the free-shipping line all come back from the same render.
            if (form.hasAttribute('data-drawer-refresh')) await drawer.load();

            toast(data.status);
        } catch {
            toast('Sorry — that did not work. Please try again.');
        } finally {
            buttons.forEach((b) => { b.disabled = false; });
        }
    });
}

// Reflect the new favourite state on the button that was pressed — and, on the
// favourites page, drop the card entirely once it is no longer saved.
function applyFavorite(form, favorited) {
    const button = form.querySelector('button[type="submit"]');
    button?.setAttribute('aria-pressed', String(favorited));

    const label = form.querySelector('[data-favorite-label]');
    if (label) label.textContent = favorited ? 'Saved to favourites' : 'Add to favourites';

    const grid = form.closest('[data-favorites-grid]');
    const card = form.closest('[data-favorites-grid] > *');
    if (!favorited && grid && card) {
        card.style.transition = 'opacity .25s';
        card.style.opacity = '0';
        setTimeout(() => {
            card.remove();
            if (!grid.children.length) window.location.reload();
        }, 250);
    }
}

function init() {
    initSearch();
    initDrawer();
    initAsyncForms();
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
