/**
 * Boot the interactive parts of the admin dashboard.
 *
 * Everything here is progressive enhancement: forms submit, navigate and
 * delete without JavaScript, so the dashboard stays usable if a script fails
 * to load. Each helper exits early when its markup is not present.
 */

/**
 * Ask for confirmation before a destructive form is submitted.
 *
 * Any button (or submit control) carrying data-confirm is checked first, and
 * the message is read from the attribute.
 */
const initConfirmations = () => {
    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const trigger = event.submitter instanceof HTMLElement ? event.submitter : null;
        const message = trigger?.dataset.confirm ?? form.dataset.confirm;

        if (message && !window.confirm(message)) {
            event.preventDefault();
        }
    });
};

/**
 * Toggle the sidebar drawer on narrow screens.
 */
const initNavigation = () => {
    const shell = document.querySelector('[data-admin-shell]');
    const toggle = document.querySelector('[data-nav-toggle]');

    if (!(shell instanceof HTMLElement) || !(toggle instanceof HTMLElement)) {
        return;
    }

    let scrim = null;

    const setOpen = (open) => {
        shell.classList.toggle('is-nav-open', open);
        toggle.setAttribute('aria-expanded', String(open));

        if (open && !scrim) {
            scrim = document.createElement('div');
            scrim.className = 'admin-scrim';
            scrim.addEventListener('click', () => setOpen(false));
            shell.append(scrim);
        } else if (!open && scrim) {
            scrim.remove();
            scrim = null;
        }
    };

    toggle.addEventListener('click', () => {
        setOpen(!shell.classList.contains('is-nav-open'));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && shell.classList.contains('is-nav-open')) {
            setOpen(false);
        }
    });
};

/**
 * Show a live preview of a picture as soon as a file is chosen, so the person
 * uploading can see what will replace the current image before saving.
 */
const initPreviews = () => {
    document.querySelectorAll('input[type="file"][data-preview-target]').forEach((input) => {
        const target = document.querySelector(input.dataset.previewTarget);

        if (!(target instanceof HTMLImageElement)) {
            return;
        }

        const original = target.getAttribute('src');

        input.addEventListener('change', () => {
            const [file] = input.files ?? [];

            if (!file) {
                target.setAttribute('src', original ?? '');
                return;
            }

            target.src = URL.createObjectURL(file);
        });
    });
};

/**
 * Let a picture-only input act on a form it does not live in, which is how
 * "use the default picture again" is wired to its own small form.
 */
const initSingleFileForms = () => {
    document.querySelectorAll('form[data-submit-on-change]').forEach((form) => {
        form.addEventListener('change', () => form.requestSubmit());
    });
};

const boot = () => {
    initConfirmations();
    initNavigation();
    initPreviews();
    initSingleFileForms();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot, { once: true });
} else {
    boot();
}
