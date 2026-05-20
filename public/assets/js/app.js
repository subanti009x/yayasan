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
    let submitted = false;

    form.addEventListener('submit', () => {
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

// Dynamic Article Views Counter for Static Site (Production)
document.addEventListener('DOMContentLoaded', () => {
    const NAMESPACE = 'yayasan-sekolah-cendekia';
    const API_BASE = `https://api.counterapi.dev/v1/${NAMESPACE}`;

    function updateViewsText(el, count) {
        const textEl = el.querySelector('.views-count-text');
        if (textEl) {
            textEl.textContent = `Dilihat ${count} kali`;
        }
    }

    const viewElements = document.querySelectorAll('[data-article-views-id]');
    if (viewElements.length === 0) return;

    // Determine if we are on a single article detail page
    const isArticlePage = window.location.pathname.includes('/artikel-');
    let mainArticleId = null;

    if (isArticlePage) {
        const mainArticleViewsEl = document.querySelector('article [data-article-views-id]');
        if (mainArticleViewsEl) {
            mainArticleId = mainArticleViewsEl.getAttribute('data-article-views-id');
        }
    }

    viewElements.forEach(async (el) => {
        const id = el.getAttribute('data-article-views-id');
        const staticCount = parseInt(el.getAttribute('data-article-views-count') || '0', 10);
        if (!id) return;

        const isMainArticle = (id === mainArticleId);
        const sessionKey = `viewed_article_${id}`;
        const hasCountedInSession = sessionStorage.getItem(sessionKey);

        try {
            if (isMainArticle && !hasCountedInSession) {
                // Increment view count for the main article
                const upRes = await fetch(`${API_BASE}/article_${id}/up`);
                if (upRes.ok) {
                    const data = await upRes.json();
                    const apiCount = data.count || 0;
                    
                    // If the API count is less than staticCount + 1 (e.g. counter newly initialized or reset),
                    // sync and set it to staticCount + 1 to preserve historical local views
                    if (apiCount < staticCount + 1) {
                        const setRes = await fetch(`${API_BASE}/article_${id}/set?count=${staticCount + 1}`);
                        if (setRes.ok) {
                            const setData = await setRes.json();
                            updateViewsText(el, setData.count);
                        }
                    } else {
                        updateViewsText(el, apiCount);
                    }
                }
                sessionStorage.setItem(sessionKey, 'true');
            } else {
                // Just fetch and update the display count (for list cards or secondary views)
                const res = await fetch(`${API_BASE}/article_${id}/`);
                if (res.ok) {
                    const data = await res.json();
                    const currentCount = data.count || 0;
                    if (currentCount > staticCount) {
                        updateViewsText(el, currentCount);
                    }
                }
            }
        } catch (err) {
            console.error('Failed to update/fetch views counter:', err);
            // Graceful fallback to static build count (do nothing)
        }
    });
});
