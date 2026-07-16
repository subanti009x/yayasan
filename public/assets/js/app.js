const mobileToggle = document.querySelector('[data-mobile-toggle]');
const mobileMenu = document.querySelector('[data-mobile-menu]');

if (mobileToggle && mobileMenu) {
    mobileToggle.addEventListener('click', () => {
        const isOpen = !mobileMenu.classList.contains('hidden');
        mobileMenu.classList.toggle('hidden', isOpen);
        mobileToggle.setAttribute('aria-expanded', String(!isOpen));
    });
}

document.querySelectorAll('a[href^="#"]').forEach((link) => {
    link.addEventListener('click', (event) => {
        const target = document.querySelector(link.getAttribute('href'));

        if (!target) {
            return;
        }

        event.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});

document.querySelectorAll('[data-google-form]').forEach((form) => {
    const successMessage = form.querySelector('[data-form-success]');
    const frame = form.querySelector('[data-google-form-frame]');
    const honeypot = form.querySelector('[data-form-honeypot]');
    let submitted = false;

    form.addEventListener('submit', (event) => {
        if (honeypot && honeypot.value.trim() !== '') {
            event.preventDefault();
            return;
        }

        if (!form.checkValidity()) {
            event.preventDefault();
            form.reportValidity();
            return;
        }

        submitted = true;

        if (successMessage) {
            successMessage.classList.add('hidden');
        }
    });

    if (frame) {
        frame.addEventListener('load', () => {
            if (!submitted || !successMessage) {
                return;
            }

            successMessage.classList.remove('hidden');
            form.reset();
            submitted = false;
        });
    }
});
