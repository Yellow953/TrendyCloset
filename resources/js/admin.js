// Back office. Kept apart from the storefront's app.js: none of these hooks
// exist on a shop page, and none of the shop's carousels, drawers or galleries
// exist here. Vanilla, no dependencies — the same house style as app.js.

// The admin sidebar is a fixed rail on desktop and a slide-in panel below `lg`.
// CSS owns the transform; this only toggles `.is-open` on the panel and scrim.
function initAdminNav() {
    const nav = document.querySelector('[data-admin-nav]');
    const scrim = document.querySelector('[data-admin-scrim]');
    if (!nav) return;

    const setOpen = (open) => {
        nav.classList.toggle('is-open', open);
        scrim?.classList.toggle('is-open', open);
        document.body.classList.toggle('has-drawer', open);
    };

    document.querySelector('[data-admin-nav-open]')?.addEventListener('click', () => setOpen(true));
    document.querySelector('[data-admin-nav-close]')?.addEventListener('click', () => setOpen(false));
    scrim?.addEventListener('click', () => setOpen(false));

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') setOpen(false);
    });

    // Desktop collapse: an icon-only rail, remembered across visits. The saved
    // state is already applied in <head> before paint; this only toggles it.
    const setCollapsed = (collapsed) => {
        document.documentElement.classList.toggle('bo-collapsed', collapsed);
        try {
            localStorage.setItem('bo-collapsed', collapsed ? '1' : '0');
        } catch (e) {
            /* private mode — the rail just won't be remembered */
        }
    };

    document.querySelector('[data-admin-nav-collapse]')?.addEventListener('click', () => setCollapsed(true));
    document.querySelector('[data-admin-nav-expand]')?.addEventListener('click', () => setCollapsed(false));
}

// The account menu in the top bar is a <details>, so it opens and closes on its
// own with no script at all. This adds only what a bare <details> lacks: an
// outside click and Escape should shut it, the way every other menu behaves.
function initAdminMenu() {
    const menus = document.querySelectorAll('[data-admin-menu]');
    if (!menus.length) return;

    const closeAll = (except) => {
        menus.forEach((menu) => {
            if (menu !== except) menu.open = false;
        });
    };

    document.addEventListener('click', (e) => {
        closeAll(e.target.closest('[data-admin-menu]'));
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeAll();
    });
}

// Back-office dialogs. Every [data-modal-open="id"] opens the matching
// [data-modal="id"]; ESC, the backdrop and [data-modal-close] close it. The
// dialog's markup is already on the page, so what is inside is an ordinary
// server-rendered form.
function initModals() {
    const dialogs = document.querySelectorAll('[data-modal]');
    if (!dialogs.length) return;

    let lastFocused = null;

    const close = (modal) => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        if (!document.querySelector('[data-modal].flex')) {
            document.body.classList.remove('has-drawer');
        }
        lastFocused?.focus();
    };

    const open = (modal) => {
        lastFocused = document.activeElement;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('has-drawer');
        // Land on the first thing worth typing into, but never on a
        // destructive button — that should take a deliberate click.
        modal.querySelector('input:not([type=hidden]), select, textarea')?.focus();
    };

    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-modal-open]');
        if (trigger) {
            const modal = document.querySelector(`[data-modal="${trigger.dataset.modalOpen}"]`);
            if (modal) {
                e.preventDefault();
                open(modal);
            }
            return;
        }

        const dismiss = e.target.closest('[data-modal-close], [data-modal-backdrop]');
        if (dismiss) {
            e.preventDefault();
            close(dismiss.closest('[data-modal]'));
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('[data-modal].flex').forEach(close);
    });

    // A dialog whose form failed validation comes back marked, so the person
    // lands straight back in it with their input and the errors intact.
    document.querySelector('[data-modal-autoopen]') &&
        open(document.querySelector('[data-modal-autoopen]'));
}

// The inline size/colour repeater on the product form. Rows are cloned from a
// <template>, so a brand-new product and an existing one use identical markup.
function initRepeater() {
    document.querySelectorAll('[data-repeater]').forEach((root) => {
        const list = root.querySelector('[data-repeater-rows]');
        const template = root.querySelector('template');
        if (!list || !template) return;

        // Keep appending at a fresh index so two new rows never collide.
        let index = Number(root.dataset.repeaterNext || list.children.length);

        root.querySelector('[data-repeater-add]')?.addEventListener('click', () => {
            const html = template.innerHTML.replaceAll('__INDEX__', index++);
            list.insertAdjacentHTML('beforeend', html);
            list.lastElementChild?.querySelector('input')?.focus();
            root.querySelector('[data-repeater-empty]')?.classList.add('hidden');
        });

        list.addEventListener('click', (e) => {
            if (!e.target.closest('[data-repeater-remove]')) return;
            e.preventDefault();
            e.target.closest('[data-repeater-row]')?.remove();
        });
    });
}

// A colour field's eyedropper button. Deliberately NOT the browser's own
// EyeDropper API: that samples the whole screen and locks the page — the
// person can't scroll down to the photo they want before it captures a
// click. Instead this arms the field, and any product photo already on the
// page (saved images or a fresh upload preview) becomes a click target;
// clicking one reads its pixel via canvas and matches it to the nearest
// swatch Swatch.php knows. Ordinary scrolling works the entire time.
// Delegated on the repeater root so it keeps working on rows cloned later by
// initRepeater(), and on `document` for the images, which live outside it.
function initColorPicker() {
    const root = document.querySelector('[data-repeater][data-swatch-map]');
    if (!root) return;

    let swatches = {};
    try {
        swatches = JSON.parse(root.dataset.swatchMap || '{}');
    } catch (e) {
        /* malformed map — picking still works, it just won't name the colour */
    }

    const toRgb = (hex) => [1, 3, 5].map((i) => parseInt(hex.slice(i, i + 2), 16));

    const nearestName = (hex) => {
        const [r, g, b] = toRgb(hex);
        let best = null;
        let bestDist = Infinity;

        for (const [name, swatchHex] of Object.entries(swatches)) {
            const [sr, sg, sb] = toRgb(swatchHex);
            const dist = (r - sr) ** 2 + (g - sg) ** 2 + (b - sb) ** 2;
            if (dist < bestDist) {
                bestDist = dist;
                best = name;
            }
        }

        // Past this distance the nearest named swatch is not a fair guess —
        // hand back the raw hex instead of mislabelling, e.g., a bright teal
        // photo sample as "Navy".
        return bestDist <= 3600 ? best : null;
    };

    const hint = document.querySelector('[data-picking-hint]');
    const hintDefault = hint?.textContent ?? '';
    let target = null;
    let armedButton = null;
    let messageTimer = null;

    // A non-blocking substitute for alert() — a real alert() halts all script
    // on the page until dismissed, which would freeze the crosshair mid-pick.
    const showMessage = (text, isError = false) => {
        if (!hint) return;
        clearTimeout(messageTimer);
        hint.textContent = text;
        hint.classList.toggle('bg-rose-600', isError);
        hint.classList.toggle('bg-slate-900', !isError);
        hint.classList.remove('hidden');
        hint.classList.add('flex');
        if (isError) {
            messageTimer = setTimeout(() => {
                hint.classList.add('hidden');
                hint.classList.remove('flex', 'bg-rose-600');
                hint.classList.add('bg-slate-900');
                hint.textContent = hintDefault;
            }, 3500);
        }
    };

    const arm = (button, input) => {
        target = input;
        armedButton = button;
        button.classList.add('bg-slate-900', 'text-white');
        showMessage(hintDefault);
        document.querySelectorAll('[data-pickable-image]').forEach((img) => {
            img.classList.add('cursor-crosshair', 'ring-2', 'ring-inset', 'ring-slate-900');
        });
        document.querySelector('[data-pickable-image]')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    };

    const disarm = () => {
        target = null;
        armedButton?.classList.remove('bg-slate-900', 'text-white');
        armedButton = null;
        clearTimeout(messageTimer);
        hint?.classList.add('hidden');
        hint?.classList.remove('flex', 'bg-rose-600');
        hint?.classList.add('bg-slate-900');
        if (hint) hint.textContent = hintDefault;
        document.querySelectorAll('[data-pickable-image]').forEach((img) => {
            img.classList.remove('cursor-crosshair', 'ring-2', 'ring-inset', 'ring-slate-900');
        });
    };

    root.addEventListener('click', (e) => {
        const button = e.target.closest('[data-color-pick]');
        if (!button) return;
        e.preventDefault();

        const input = button.closest('td')?.querySelector('input[name*="[color]"]');
        if (!input) return;

        if (button === armedButton) {
            disarm();
        } else {
            disarm();
            arm(button, input);
        }
    });

    document.addEventListener('click', (e) => {
        if (!target) return;

        const img = e.target.closest('[data-pickable-image]');
        if (!img) return;
        e.preventDefault();

        try {
            const canvas = document.createElement('canvas');
            canvas.width = img.naturalWidth;
            canvas.height = img.naturalHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0);

            const rect = img.getBoundingClientRect();
            const x = Math.min(canvas.width - 1, Math.max(0, Math.round((e.clientX - rect.left) / rect.width * canvas.width)));
            const y = Math.min(canvas.height - 1, Math.max(0, Math.round((e.clientY - rect.top) / rect.height * canvas.height)));
            const [r, g, b] = ctx.getImageData(x, y, 1, 1).data;
            const hex = '#' + [r, g, b].map((v) => v.toString(16).padStart(2, '0')).join('');

            target.value = nearestName(hex) || hex;
            target.dispatchEvent(new Event('input', { bubbles: true }));
            disarm();
        } catch (err) {
            // A cross-origin photo (the demo catalogue's "Linked" images, or
            // any external URL) taints the canvas and getImageData throws —
            // no way to read it. Say so and stay armed so another, same-origin
            // photo can still be tried without re-arming.
            showMessage("Can't read this photo's colour (it's hosted elsewhere) — try another, or type the name.", true);
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') disarm();
    });
}

// Preview picked images before the form is posted, so someone uploading eight
// photographs can see what they chose without saving first.
function initUploadPreviews() {
    document.querySelectorAll('[data-upload]').forEach((input) => {
        const target = document.querySelector(input.dataset.upload);
        if (!target) return;

        input.addEventListener('change', () => {
            target.innerHTML = '';

            Array.from(input.files || []).forEach((file) => {
                if (!file.type.startsWith('image/')) return;
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.dataset.pickableImage = '';
                img.className = 'h-40 w-40 rounded-lg border border-slate-200 object-cover transition-shadow';
                img.onload = () => URL.revokeObjectURL(img.src);
                target.appendChild(img);
            });
        });
    });
}

// Show/hide field groups by another field's value — e.g. an offer form whose
// type select decides which of the spend/category/BOGO groups apply. The
// controlling field carries `data-toggle-field="<name>"`; each group carries
// `data-toggle-when="value-a,value-b"`. Scoped to its nearest controller (not
// just the form) so a toggle can nest inside another — e.g. Category %
// off / BOGO's scope picker (category vs. specific products) sits inside the
// type toggle without the two cross-wiring each other.
function initFieldToggles() {
    document.querySelectorAll('[data-toggle-field]').forEach((scope) => {
        const control = scope.querySelector(`[name="${scope.dataset.toggleField}"]`);
        if (!control) return;

        const groups = Array.from(scope.querySelectorAll('[data-toggle-when]'))
            .filter((group) => group.closest('[data-toggle-field]') === scope);

        const apply = () => {
            groups.forEach((group) => {
                group.classList.toggle('hidden', !group.dataset.toggleWhen.split(',').includes(control.value));
            });
        };

        control.addEventListener('change', apply);
        apply();
    });
}

function init() {
    initAdminNav();
    initAdminMenu();
    initModals();
    initRepeater();
    initColorPicker();
    initUploadPreviews();
    initFieldToggles();
}

if (document.readyState !== 'loading') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}
