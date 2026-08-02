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
        document.documentElement.classList.toggle('ad-collapsed', collapsed);
        try {
            localStorage.setItem('ad-collapsed', collapsed ? '1' : '0');
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
                img.className = 'h-20 w-16 rounded-md border border-slate-200 object-cover';
                img.onload = () => URL.revokeObjectURL(img.src);
                target.appendChild(img);
            });
        });
    });
}

function init() {
    initAdminNav();
    initAdminMenu();
    initModals();
    initRepeater();
    initUploadPreviews();
}

if (document.readyState !== 'loading') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', init);
}
