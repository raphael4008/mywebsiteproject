const loadedTranslations = {};

import { BASE_URL } from './config.js';

async function fetchTranslations(lang) {
    if (loadedTranslations[lang]) {
        return loadedTranslations[lang];
    }

    try {
        const response = await fetch(`${BASE_URL}lang/${lang}.json`);
        if (!response.ok) {
            throw new Error(`Failed to fetch translations for ${lang}`);
        }
        const translations = await response.json();
        loadedTranslations[lang] = translations;
        return translations;
    } catch (error) {
        console.error(error);
        return {}; // Return empty object on error
    }
}

export async function setLanguage(lang, scope = document) {
    const translations = await fetchTranslations(lang);
    if (Object.keys(translations).length === 0) return;

    scope.querySelectorAll('[data-translate]').forEach(element => {
        const key = element.dataset.translate;
        if (translations[key]) {
            if (element.placeholder) {
                element.placeholder = translations[key];
            } else {
                element.textContent = translations[key];
            }
        }
    });

    localStorage.setItem('preferredLanguage', lang);
    const languageSelect = document.getElementById('language-select');
    if (languageSelect && languageSelect.value !== lang) {
        languageSelect.value = lang;
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    const languageSelect = document.getElementById('language-select');
    if (languageSelect) {
        languageSelect.addEventListener('change', async (e) => {
            await setLanguage(e.target.value);
        });
    }

    const preferredLanguage = localStorage.getItem('preferredLanguage') || 'en';
    await setLanguage(preferredLanguage);
});

