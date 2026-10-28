/**
 * Marks scroll-reveal elements as visible when they enter the viewport.
 *
 * Uses IntersectionObserver rather than a scroll listener, reveals each element
 * once, and applies any stagger from data-reveal-delay. Elements already in view
 * on load reveal immediately.
 *
 * Lifecycle of a reveal:
 *  1. CSS declares a hidden start state for [data-reveal] (no transitions).
 *  2. This module attaches the transition inline and pushes the final values
 *     inline — the animation runs.
 *  3. After it finishes, every inline style is removed AND the data-reveal
 *     attribute is dropped, handing the element completely back to its own
 *     component rules.
 *
 * Why the choreography: a transition list or target transform declared in CSS
 * for [data-reveal] would outlive the reveal and override the component's own
 * hover transition/transform by cascade order (e.g. .skill-panel:hover would
 * never move). Inline styles beat both, and removing them — together with the
 * attribute the hidden state hangs on — leaves hover interactions untouched.
 *
 * It also settles .hero-anim elements after their CSS entrance finishes:
 * fill-mode animations hold onto `transform`, which would otherwise block
 * hover lifts on the hero's buttons and chips.
 */

const REVEAL_TRANSITION = [
    'opacity 0.85s var(--ease-out) var(--reveal-delay, 0ms)',
    'transform 0.85s var(--ease-out) var(--reveal-delay, 0ms)',
    'filter 0.85s var(--ease-out) var(--reveal-delay, 0ms)',
].join(', ');

export function initReveal() {
    const root = document.documentElement;
    const elements = Array.from(document.querySelectorAll('[data-reveal]'));

    // Tells the inline boot timeout in the layout that the bundle is alive.
    root.dataset.revealReady = 'true';

    settleHeroEntrances();

    if (elements.length === 0) {
        return;
    }

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const reveal = (element) => {
        const delayValue = Number.parseInt(element.dataset.revealDelay ?? '0', 10);
        const delay = Number.isFinite(delayValue) && delayValue > 0 ? delayValue : 0;

        if (delay > 0) {
            element.style.setProperty('--reveal-delay', `${delay}ms`);
        }

        element.classList.add('is-revealed');

        // Reduced-motion visitors never get the hidden start state (it lives
        // inside a no-preference media query): release immediately, animate
        // never.
        if (reduceMotion) {
            release(element);

            return;
        }

        element.style.transition = REVEAL_TRANSITION;

        // Final values. `transform: none` is the identity for every direction
        // (up/left/right/scale all land on it).
        element.style.opacity = '1';
        element.style.transform = 'none';
        element.style.filter = 'none';

        window.setTimeout(() => release(element), delay + 1000);
    };

    const supportsObserver = typeof IntersectionObserver === 'function';

    if (!supportsObserver || reduceMotion) {
        elements.forEach(reveal);

        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                reveal(entry.target);
                observer.unobserve(entry.target);
            });
        },
        {
            // Trigger slightly before the element is fully on screen.
            rootMargin: '0px 0px -12% 0px',
            threshold: 0.08,
        },
    );

    elements.forEach((element) => observer.observe(element));
}

/**
 * Hands an element back to its component styles: clears the inline reveal
 * properties and drops the attribute the hidden start state hangs on. Done in
 * one synchronous block so the browser never paints an intermediate frame.
 */
function release(element) {
    element.style.removeProperty('--reveal-delay');
    element.style.removeProperty('transition');
    element.style.removeProperty('opacity');
    element.style.removeProperty('transform');
    element.style.removeProperty('filter');
    element.removeAttribute('data-reveal');
}

/**
 * Drops the fill-mode from finished hero entrance animations so the animated
 * element's transform is available again (see .hero-anim--done in CSS).
 */
function settleHeroEntrances() {
    const heroes = Array.from(document.querySelectorAll('.hero-anim'));

    if (heroes.length === 0) {
        return;
    }

    heroes.forEach((element) => {
        element.addEventListener('animationend', (event) => {
            if (event.target !== element) {
                return;
            }

            element.classList.add('hero-anim--done');
        });

        // Safety net: if animationend never arrives (element hidden mid-
        // entrance, animation overridden elsewhere), settle it anyway so hover
        // interactions are never locked out. The longest entrance finishes
        // before 2s, so 3s is a safe margin.
        window.setTimeout(() => {
            element.classList.add('hero-anim--done');
        }, 3000);
    });
}
