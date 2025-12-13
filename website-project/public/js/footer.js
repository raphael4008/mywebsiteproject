import { setLanguage } from './lang.js';
import { loadAndCacheHTML } from './html-loader.js';

document.addEventListener('DOMContentLoaded', async () => {
    const footer = document.querySelector('footer');
    if (!footer) return;

    const footerHtml = await loadAndCacheHTML('includes/footer.html', footer, 'footerHTML');

    if (footerHtml) {
        // Apply language after footer is loaded
        const preferredLanguage = localStorage.getItem('preferredLanguage') || 'en';
        setLanguage(preferredLanguage);
    }
});
