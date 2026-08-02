const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

// Scroll reveal. Two contracts, both authored in the Blade:
//   [data-reveal]           the element itself rises in
//   [data-reveal-children]  its children rise in one after another
// Either way it is the marked element that is observed and it is unobserved
// the moment it lands — a reveal happens once per page, never on the way back
// up. The hidden starting state lives behind `.tc-js` (set in the layout head),
// so nothing here can leave a page blank if the script never runs.
function initReveal() {
    const targets = document.querySelectorAll('[data-reveal], [data-reveal-children]');
    if (!targets.length) return;

    const showAll = () => targets.forEach((el) => el.classList.add('is-in'));

    if (reducedMotion.matches || !('IntersectionObserver' in window)) {
        showAll();
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                // Anything already above the viewport — a restored scroll
                // position, a jump to an anchor — is shown rather than left as
                // a hole the shopper has to scroll back up to fill.
                const past = entry.boundingClientRect.bottom <= 0;
                if (!entry.isIntersecting && !past) return;
                entry.target.classList.add('is-in');
                observer.unobserve(entry.target);
            });
        },
        // Start a little before the element is fully in view, so it has
        // finished arriving by the time it is worth looking at.
        { threshold: 0.06, rootMargin: '0px 0px -8% 0px' },
    );

    targets.forEach((el) => observer.observe(el));
}

// Lightweight scroll-snap carousels.
// Markup contract:
//   [data-carousel]            wrapper
//     [data-carousel-track]    horizontally scrolling flex container
//     [data-carousel-prev]     previous button (optional)
//     [data-carousel-next]     next button (optional)
//     [data-carousel-autoplay] milliseconds between advances (optional)
function initCarousels() {
    document.querySelectorAll('[data-carousel]').forEach((root) => {
        const track = root.querySelector('[data-carousel-track]');
        if (!track) return;
        const prev = root.querySelector('[data-carousel-prev]');
        const next = root.querySelector('[data-carousel-next]');

        // Scroll by roughly one "page" (80% of the visible width).
        const step = () => Math.max(Math.round(track.clientWidth * 0.8), 200);
        const end = () => track.scrollWidth - track.clientWidth - 2;

        const advance = (direction) =>
            track.scrollBy({ left: direction * step(), behavior: 'smooth' });

        prev?.addEventListener('click', () => advance(-1));
        next?.addEventListener('click', () => advance(1));

        // Disable arrows at the track's extremes.
        const update = () => {
            if (prev) prev.disabled = track.scrollLeft <= 2;
            if (next) next.disabled = track.scrollLeft >= end();
        };
        track.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        update();

        autoplayCarousel(root, track, step, end);
    });
}

// Optional autoplay for a carousel: creep forward a page at a time and wrap
// back to the start once the track runs out.
//
// It only ever runs while it is worth running — the rail is on screen, the tab
// is in front, nobody is hovering, touching or tabbing through it — and never
// under prefers-reduced-motion, where a rail that moves on its own is exactly
// what the setting is asking us not to do.
function autoplayCarousel(root, track, step, end) {
    const delay = Number(root.dataset.carouselAutoplay);
    if (!delay || Number.isNaN(delay)) return;

    let timer = null;
    let visible = false;
    let held = false;

    const tick = () => {
        // Nothing to scroll (everything already fits) — leave it alone.
        if (end() <= 0) return;
        const atEnd = track.scrollLeft >= end();
        track.scrollTo({ left: atEnd ? 0 : track.scrollLeft + step(), behavior: 'smooth' });
    };

    const sync = () => {
        const run = visible && !held && !document.hidden && !reducedMotion.matches;
        if (run && !timer) timer = setInterval(tick, delay);
        if (!run && timer) {
            clearInterval(timer);
            timer = null;
        }
    };

    const hold = (value) => {
        held = value;
        sync();
    };

    // Anything the visitor does to the rail wins: while they are on it, it is
    // theirs, and the clock restarts clean when they leave.
    root.addEventListener('pointerenter', () => hold(true));
    root.addEventListener('pointerleave', () => hold(false));
    root.addEventListener('focusin', () => hold(true));
    root.addEventListener('focusout', () => hold(false));
    track.addEventListener('touchstart', () => hold(true), { passive: true });

    document.addEventListener('visibilitychange', sync);
    reducedMotion.addEventListener('change', sync);

    new IntersectionObserver((entries) => {
        visible = entries[0].isIntersecting;
        sync();
    }, { threshold: 0.25 }).observe(root);
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

        // Only the digits that actually changed animate, so the seconds tick
        // over on their own while the days sit still.
        const set = (el, value) => {
            if (!el || el.textContent === value) return;
            el.textContent = value;
            el.classList.remove('tc-tick');
            void el.offsetWidth; // restart the animation
            el.classList.add('tc-tick');
        };

        const tick = () => {
            const left = Math.max(target - Date.now(), 0);
            const seconds = Math.floor(left / 1000);
            set(parts.days, pad(Math.floor(seconds / 86400)));
            set(parts.hours, pad(Math.floor((seconds % 86400) / 3600)));
            set(parts.minutes, pad(Math.floor((seconds % 3600) / 60)));
            set(parts.seconds, pad(seconds % 60));
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

// Listing filters on a phone: one button, one panel. The hiding is CSS (gated
// on .tc-js, and only below lg), so this is just the switch and the ARIA state.
function initFilterPanel() {
    const toggle = document.querySelector('[data-filter-toggle]');
    const panel = document.querySelector('[data-filter-panel]');
    if (!toggle || !panel) return;

    toggle.addEventListener('click', () => {
        const open = panel.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', String(open));
    });
}

// Hero slideshow: cross-fading slides with dots and autoplay. Slide one is
// rendered active, so the hero is intact before this runs.
//
// It does *not* pause on hover: the hero is a full-bleed 640px band, so a
// resting cursor sits on it more often than not and hover-pause read as "the
// slideshow is broken". It pauses for the things that actually mean the visitor
// is not watching — a backgrounded tab — or is working the controls by keyboard.
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

    let timer = null;
    const stop = () => {
        clearInterval(timer);
        timer = null;
    };
    const restart = () => {
        stop();
        if (!document.hidden && !hero.contains(document.activeElement)) {
            timer = setInterval(() => show(index + 1), 6000);
        }
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

    // Keyboard users get the pause: while focus is inside the hero they are
    // driving it, and a slide changing underneath them loses their place.
    hero.addEventListener('focusin', stop);
    hero.addEventListener('focusout', restart);
    document.addEventListener('visibilitychange', restart);

    show(index);
    restart();
}

// Product gallery: clicking a thumbnail swaps the main image.
function initGalleries() {
    document.querySelectorAll('[data-gallery]').forEach((gallery) => {
        const main = gallery.querySelector('[data-gallery-main]');
        const thumbs = [...gallery.querySelectorAll('[data-gallery-thumb]')];
        if (!main || thumbs.length < 2) return;

        thumbs.forEach((thumb) => {
            thumb.addEventListener('click', () => {
                const src = thumb.dataset.full || thumb.querySelector('img')?.src;
                if (!src || main.src === src) return;

                thumbs.forEach((t) => t.classList.toggle('is-active', t === thumb));

                // srcset outranks src, so it has to move with it — otherwise the
                // main frame keeps rendering the previous photograph.
                const show = () => {
                    main.srcset = thumb.dataset.srcset || '';
                    main.src = src;
                };

                // Cross-fade rather than cut. The swap happens while the frame
                // is empty, so a slow image never shows half-loaded.
                const swap = () => {
                    show();
                    main.addEventListener('load', () => main.classList.remove('is-swapping'), { once: true });
                    // A cached image can be ready before the listener binds.
                    if (main.complete) main.classList.remove('is-swapping');
                };

                if (reducedMotion.matches) {
                    show();
                    return;
                }

                main.classList.add('is-swapping');
                setTimeout(swap, 180);
            });
        });
    });
}

// Zoom: scale the image and track the pointer with transform-origin, so the
// point under the finger or cursor is the point magnified. It used to bail
// outright on a touch screen, which left phones with no zoom at all; instead
// [data-zoom-toggle] arms it, and while armed a finger pans the photograph.
// It starts armed only where a cursor can drive it — arming it on touch takes
// the image out of the page scroll, which is the shopper's call to make.
function initZoom() {
    document.querySelectorAll('[data-zoom]').forEach((box) => {
        const img = box.querySelector('img');
        if (!img) return;

        const toggle = box.closest('[data-gallery]')?.querySelector('[data-zoom-toggle]');
        let on = !window.matchMedia('(pointer: coarse)').matches;

        // A finger can travel past the frame; clamping keeps the magnified
        // point on the photograph rather than panning off its edge.
        const pct = (v) => Math.min(Math.max(v, 0), 100);

        const focus = (clientX, clientY) => {
            const rect = box.getBoundingClientRect();
            const x = pct(((clientX - rect.left) / rect.width) * 100);
            const y = pct(((clientY - rect.top) / rect.height) * 100);
            img.style.transformOrigin = `${x}% ${y}%`;
        };

        const stop = () => {
            img.classList.remove('is-zoomed');
            img.style.transformOrigin = 'center';
        };

        const arm = () => {
            box.classList.toggle('is-zoom-on', on);
            toggle?.setAttribute('aria-pressed', String(on));
            if (!on) stop();
        };

        toggle?.addEventListener('click', () => { on = !on; arm(); });
        arm();

        box.addEventListener('mousemove', (e) => { if (on) focus(e.clientX, e.clientY); });
        box.addEventListener('mouseenter', () => { if (on) img.classList.add('is-zoomed'); });
        box.addEventListener('mouseleave', stop);

        box.addEventListener('touchstart', (e) => {
            if (!on) return;
            focus(e.touches[0].clientX, e.touches[0].clientY);
            img.classList.add('is-zoomed');
        }, { passive: true });
        box.addEventListener('touchmove', (e) => {
            if (on) focus(e.touches[0].clientX, e.touches[0].clientY);
        }, { passive: true });
        box.addEventListener('touchend', stop);
        box.addEventListener('touchcancel', stop);
    });
}

// Share the piece: the native sheet where the browser has one, the clipboard
// everywhere else. A dismissed sheet is not a failure, so it says nothing.
function initShare() {
    document.querySelectorAll('[data-share]').forEach((button) => {
        button.addEventListener('click', async () => {
            const url = button.dataset.share || window.location.href;
            const title = button.dataset.shareTitle || document.title;

            try {
                if (navigator.share) {
                    await navigator.share({ title, url });
                    return;
                }
                await navigator.clipboard.writeText(url);
                toast('Link copied');
            } catch (err) {
                if (err?.name !== 'AbortError') toast('Could not share that just now.');
            }
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
            // Every re-render (a quantity change, a removed line) fades its new
            // state in, so the drawer never appears to jump.
            body.classList.remove('tc-fade-up');
            void body.offsetWidth;
            body.classList.add('tc-fade-up');
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

// Update a header badge, and let it acknowledge a number that actually moved.
function setCount(selector, value) {
    document.querySelectorAll(selector).forEach((el) => {
        const changed = el.textContent !== String(value);
        el.textContent = value;
        if (!changed) return;
        el.classList.remove('tc-bump');
        void el.offsetWidth; // restart the animation
        el.classList.add('tc-bump');
    });
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
        // Only the button that was pressed breathes — the others just go quiet.
        submitter?.classList.add('is-busy');

        // Read the attribute, not form.action: the PDP form has submit buttons
        // named "action" (add vs buy), and a named control shadows the property
        // — form.action would hand back a RadioNodeList and we would POST to
        // /product/[object RadioNodeList].
        const url = form.getAttribute('action') || window.location.href;

        try {
            const response = await fetch(url, {
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
            submitter?.classList.remove('is-busy');
        }
    });
}

// Reflect the new favourite state on the button that was pressed — and, on the
// favourites page, drop the card entirely once it is no longer saved.
function applyFavorite(form, favorited) {
    const button = form.querySelector('button[type="submit"]');
    button?.setAttribute('aria-pressed', String(favorited));

    // The heart itself reacts — it is the only confirmation on a card.
    const heart = button?.querySelector('svg');
    if (heart && favorited) {
        heart.classList.remove('tc-pop');
        void heart.getBoundingClientRect(); // restart the animation
        heart.classList.add('tc-pop');
    }

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

// A tap on any wa.me link. The browser is about to leave for WhatsApp, so the
// report is sent with `keepalive` — an ordinary fetch would be cancelled by the
// navigation. Never blocks or delays the link itself: the tap must feel instant
// whether or not the beacon lands.
function initWhatsappTracking() {
    const endpoint = document.querySelector('meta[name="wa-track"]')?.content;
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!endpoint || !token) return;

    document.addEventListener('click', (e) => {
        const link = e.target.closest('a[href*="wa.me"]');
        if (!link) return;

        const body = new FormData();
        body.append('_token', token);
        body.append('from', location.pathname);

        try {
            fetch(endpoint, { method: 'POST', body, keepalive: true, credentials: 'same-origin' })
                .catch(() => {});
        } catch (err) {
            /* analytics must never break the link */
        }
    });
}

function init() {
    initReveal();
    initSearch();
    initDrawer();
    initAsyncForms();
    initCarousels();
    initTabs();
    initStickyBuy();
    initGalleries();
    initZoom();
    initShare();
    initQuantitySteppers();
    initClearables();
    initCountdowns();
    initAutoSubmit();
    initStickyHeader();
    initFilterPanel();
    initHero();
    initWhatsappTracking();
}

if (document.readyState !== 'loading') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}
