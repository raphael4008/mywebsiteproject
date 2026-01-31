import { setLanguage } from './lang.js';

export function initFooter() {
    const footer = document.querySelector('#footer-container');
    if (!footer) return;

    // Apply language after footer is loaded
    const preferredLanguage = localStorage.getItem('preferredLanguage') || 'en';
    setLanguage(preferredLanguage);
}

