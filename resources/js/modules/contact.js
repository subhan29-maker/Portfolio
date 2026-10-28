/**
 * Contact form ergonomics.
 *
 * The server remains the only authority on validity — this only prevents a
 * double submission and gives immediate feedback while the request is in
 * flight. Browser native validation is intentionally enabled here even though
 * the form falls back to server-side errors without JavaScript.
 */
export function initContactForm() {
    const form = document.querySelector('[data-contact-form]');

    if (!form) {
        return;
    }

    const submitButton = form.querySelector('[data-submit-button]');
    const label = submitButton?.querySelector('.btn__label');

    form.addEventListener('submit', () => {
        if (!submitButton || submitButton.disabled) {
            return;
        }

        submitButton.disabled = true;
        submitButton.setAttribute('aria-busy', 'true');

        if (label) {
            label.textContent = 'Sending…';
        }
    });
}
