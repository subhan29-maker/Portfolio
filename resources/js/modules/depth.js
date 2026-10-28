/**
 * Pointer depth effects: parallax, 3D tilt and pointer-tracked glows.
 *
 * Design rules:
 * - Only ever active for mouse/trackpad users on devices that both hover and
 *   report a fine pointer, and never when the visitor prefers reduced motion.
 *   Every query is re-evaluated on change, so plugging in a mouse or flipping
 *   the OS setting takes effect without a reload.
 * - Parallax writes the CSS `translate` property on depth layers (never
 *   `transform`), so entrance animations and hover transforms never conflict.
 * - Work is rAF-throttled: one frame at most in flight per element, passive
 *   listeners, no layout reads outside the frame.
 *
 * Without JavaScript every affected element keeps its static layout — the
 * CSS supplies neutral defaults (--par-x: 0, --rx: 0deg, …).
 */

const PARALLAX_TRAVEL = 26; // px at depth 1
const MAX_TILT = 7; // degrees

export function initDepth() {
    const pointerFine = window.matchMedia('(hover: hover) and (pointer: fine)');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    let teardown = null;

    const sync = () => {
        if (teardown !== null) {
            teardown();
            teardown = null;
        }

        if (!pointerFine.matches || reducedMotion.matches) {
            return;
        }

        teardown = bindEffects();
    };

    sync();

    if (typeof pointerFine.addEventListener === 'function') {
        pointerFine.addEventListener('change', sync);
        reducedMotion.addEventListener('change', sync);
    }
}

function bindEffects() {
    const cleanups = [];

    document.querySelectorAll('[data-parallax-root]').forEach((root) => {
        cleanups.push(bindParallax(root));
    });

    document.querySelectorAll('[data-tilt]').forEach((element) => {
        cleanups.push(bindTilt(element));
    });

    document.querySelectorAll('[data-glow]').forEach((element) => {
        cleanups.push(bindGlow(element));
    });

    return () => cleanups.forEach((cleanup) => cleanup());
}

/**
 * Runs a handler in a rAF slot, coalescing burst events to one call/frame.
 */
function frameThrottle(handler) {
    let frame = null;
    let latest = null;

    const run = () => {
        frame = null;
        handler(latest);
    };

    return (event) => {
        latest = event;

        if (frame === null) {
            frame = window.requestAnimationFrame(run);
        }
    };
}

function normalizedPointer(event, element) {
    const rect = element.getBoundingClientRect();

    if (rect.width === 0 || rect.height === 0) {
        return null;
    }

    return {
        x: (event.clientX - rect.left) / rect.width - 0.5,
        y: (event.clientY - rect.top) / rect.height - 0.5,
    };
}

function bindParallax(root) {
    // Depth factors arrive as data-depth attributes (readable markup); they
    // are copied into custom properties so CSS calc() can consume them.
    const layers = Array.from(root.querySelectorAll('[data-depth]'));

    layers.forEach((layer) => {
        const depth = Number.parseFloat(layer.dataset.depth);

        if (Number.isFinite(depth)) {
            layer.style.setProperty('--depth', String(depth));
        }
    });

    const reset = () => {
        root.classList.remove('is-parallaxing');
        root.style.removeProperty('--par-x');
        root.style.removeProperty('--par-y');
    };

    const onMove = frameThrottle((event) => {
        if (event.pointerType === 'touch') {
            return;
        }

        const point = normalizedPointer(event, root);

        if (point === null) {
            return;
        }

        root.classList.add('is-parallaxing');
        root.style.setProperty('--par-x', `${(point.x * PARALLAX_TRAVEL).toFixed(2)}px`);
        root.style.setProperty('--par-y', `${(point.y * PARALLAX_TRAVEL * 0.7).toFixed(2)}px`);
    });

    root.addEventListener('pointermove', onMove, { passive: true });
    root.addEventListener('pointerleave', reset, { passive: true });
    root.addEventListener('pointercancel', reset, { passive: true });

    return () => {
        root.removeEventListener('pointermove', onMove);
        root.removeEventListener('pointerleave', reset);
        root.removeEventListener('pointercancel', reset);
        reset();
        layers.forEach((layer) => layer.style.removeProperty('--depth'));
    };
}

function bindTilt(element) {
    const reset = () => {
        element.classList.remove('is-tilting');
        element.style.removeProperty('--rx');
        element.style.removeProperty('--ry');
        element.style.removeProperty('--gx');
        element.style.removeProperty('--gy');
    };

    const onMove = frameThrottle((event) => {
        if (event.pointerType === 'touch') {
            return;
        }

        const point = normalizedPointer(event, element);

        if (point === null) {
            return;
        }

        element.classList.add('is-tilting');
        element.style.setProperty('--rx', `${(-point.y * MAX_TILT * 2).toFixed(2)}deg`);
        element.style.setProperty('--ry', `${(point.x * MAX_TILT * 2).toFixed(2)}deg`);
        element.style.setProperty('--gx', `${((point.x + 0.5) * 100).toFixed(1)}%`);
        element.style.setProperty('--gy', `${((point.y + 0.5) * 100).toFixed(1)}%`);
    });

    element.addEventListener('pointermove', onMove, { passive: true });
    element.addEventListener('pointerleave', reset, { passive: true });
    element.addEventListener('pointercancel', reset, { passive: true });

    return () => {
        element.removeEventListener('pointermove', onMove);
        element.removeEventListener('pointerleave', reset);
        element.removeEventListener('pointercancel', reset);
        reset();
    };
}

function bindGlow(element) {
    const reset = () => {
        element.style.removeProperty('--gx');
        element.style.removeProperty('--gy');
    };

    const onMove = frameThrottle((event) => {
        if (event.pointerType === 'touch') {
            return;
        }

        const point = normalizedPointer(event, element);

        if (point === null) {
            return;
        }

        element.style.setProperty('--gx', `${((point.x + 0.5) * 100).toFixed(1)}%`);
        element.style.setProperty('--gy', `${((point.y + 0.5) * 100).toFixed(1)}%`);
    });

    element.addEventListener('pointermove', onMove, { passive: true });
    element.addEventListener('pointerleave', reset, { passive: true });

    return () => {
        element.removeEventListener('pointermove', onMove);
        element.removeEventListener('pointerleave', reset);
        reset();
    };
}
