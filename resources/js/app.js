import { initReveal } from './modules/reveal.js';
import { initNavigation } from './modules/navigation.js';
import { initImageFallbacks } from './modules/images.js';
import { initContactForm } from './modules/contact.js';
import { initDepth } from './modules/depth.js';

/**
 * Boot the interactive parts of the site.
 *
 * Each module is independent and safe to run on every page — they exit early
 * when their markup is not present. Nothing here is required for content to be
 * readable: without JavaScript the site still renders fully.
 */
const boot = () => {
    initNavigation();
    initReveal();
    initImageFallbacks();
    initContactForm();
    initDepth();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot, { once: true });
} else {
    boot();
}
