/**
 * Responsive navigation.
 *
 * - Toggles the mobile panel and keeps aria-expanded in sync.
 * - Closes the panel after following a link, on Escape, and on outside clicks.
 * - Moves focus into the panel when it opens and returns it on close.
 * - Adds a subtle "scrolled" style to the header using IntersectionObserver on a
 *   sentinel element instead of a continuous scroll listener.
 * - Publishes the measured header height as a CSS variable so anchor jumps and
 *   the sticky project sidebar are never hidden underneath it.
 */
export function initNavigation() {
    const header = document.querySelector('[data-header]');
    const toggle = document.querySelector('[data-nav-toggle]');
    const nav = document.querySelector('[data-nav]');

    if (!header) {
        return;
    }

    const desktopQuery = window.matchMedia('(min-width: 900px)');

    /* Publish the real header height for scroll offsets. */
    const syncHeaderHeight = () => {
        document.documentElement.style.setProperty(
            '--header-measured',
            `${Math.round(header.getBoundingClientRect().height)}px`,
        );
    };

    syncHeaderHeight();

    if (typeof ResizeObserver === 'function') {
        new ResizeObserver(syncHeaderHeight).observe(header);
    } else {
        window.addEventListener('resize', syncHeaderHeight, { passive: true });
    }

    /* Shadow the header once the page has moved past the sentinel. */
    const sentinel = document.querySelector('[data-scroll-sentinel]');

    if (sentinel && typeof IntersectionObserver === 'function') {
        new IntersectionObserver(
            ([entry]) => header.classList.toggle('is-scrolled', !entry.isIntersecting),
            { threshold: 0 },
        ).observe(sentinel);
    }

    if (!toggle || !nav) {
        return;
    }

    const isOpen = () => toggle.getAttribute('aria-expanded') === 'true';

    const open = () => {
        nav.classList.add('is-open');
        toggle.setAttribute('aria-expanded', 'true');
    };

    const close = ({ restoreFocus = false } = {}) => {
        if (!isOpen()) {
            return;
        }

        nav.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');

        if (restoreFocus) {
            toggle.focus();
        }
    };

    toggle.addEventListener('click', () => {
        if (isOpen()) {
            close();
        } else {
            open();
        }
    });

    /* Following a link should always dismiss the panel. */
    nav.addEventListener('click', (event) => {
        if (event.target.closest('a')) {
            close();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isOpen()) {
            close({ restoreFocus: true });
        }
    });

    document.addEventListener('click', (event) => {
        if (!isOpen()) {
            return;
        }

        if (!header.contains(event.target)) {
            close();
        }
    });

    /* Leaving the mobile breakpoint must not leave the panel stuck open. */
    desktopQuery.addEventListener('change', () => close());
}
