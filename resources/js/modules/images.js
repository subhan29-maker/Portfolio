/**
 * Swaps in a placeholder when an image fails to load.
 *
 * The Blade image component already checks the filesystem, so this only covers
 * files that exist but cannot be decoded. Images that failed before this module
 * ran are detected via the `complete` / `naturalWidth` combination.
 */
export function initImageFallbacks() {
    const swap = (image) => {
        const fallback = image.dataset.fallback;

        if (!fallback || image.dataset.fallbackApplied === 'true') {
            return;
        }

        image.dataset.fallbackApplied = 'true';
        image.src = fallback;
    };

    const handleError = (event) => {
        const image = event.target;

        if (image instanceof HTMLImageElement) {
            swap(image);
        }
    };

    document.addEventListener('error', handleError, true);

    document.querySelectorAll('img[data-fallback]').forEach((image) => {
        if (image.complete && image.naturalWidth === 0) {
            swap(image);
        }
    });
}
