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

if (document.readyState !== 'loading') {
    initCarousels();
} else {
    document.addEventListener('DOMContentLoaded', initCarousels);
}
