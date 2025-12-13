
const translations = {
    en: {
        // General
        'Discover Your Next Home Across Kenya': 'Discover Your Next Home Across Kenya',
        'Listings in all major cities — Nairobi, Mombasa, Kisumu, Eldoret and more. Search by filters or describe your ideal home and our AI-enabled search will try to find the best matches.': 'Listings in all major cities — Nairobi, Mombasa, Kisumu, Eldoret and more. Search by filters or describe your ideal home and our AI-enabled search will try to find the best matches.',
        'Start Searching': 'Start Searching',
        'Verified Listings': 'Verified Listings',
        'Every home is checked for authenticity and quality.': 'Every home is checked for authenticity and quality.',
        'Secure Booking': 'Secure Booking',
        'Reserve your spot with a small fee, safely and instantly.': 'Reserve your spot with a small fee, safely and instantly.',
        '24/7 Support': '24/7 Support',
        'Our team is here to help you every step of the way.': 'Our team is here to help you every step of the way.',
        'Featured Homes Across Kenya': 'Featured Homes Across Kenya',
        'What Our Clients Say': 'What Our Clients Say',
        'Are You an Owner or Admin?': 'Are You an Owner or Admin?',
        'Owner Portal': 'Owner Portal',
        'Admin Dashboard': 'Admin Dashboard',
        // Nav
        'Home': 'Home',
        'Search': 'Search',
        'Explore': 'Explore',
        'Neighborhoods': 'Neighborhoods',
        'Transport': 'Transport',
        'Agent Directory': 'Agent Directory',
        'More': 'More',
        'Services': 'Services',
        'Find a Home': 'Find a Home',
        'Reviews': 'Reviews',
        'Marketplace': 'Marketplace',
        'Terms': 'Terms',
        'About': 'About',
        'About Us': 'About Us',
        'Contact': 'Contact',
        'Contact Us': 'Contact Us',
        'Login': 'Login',
        'Register': 'Register',
        'Profile': 'Profile',
        'Logout': 'Logout',
    },
    sw: {
        // General
        'Discover Your Next Home Across Kenya': 'Gundua Nyumba Yako Inayofuata Kote Kenya',
        'Listings in all major cities — Nairobi, Mombasa, Kisumu, Eldoret and more. Search by filters or describe your ideal home and our AI-enabled search will try to find the best matches.': 'Matangazo katika miji yote mikubwa - Nairobi, Mombasa, Kisumu, Eldoret na zaidi. Tafuta kwa vichungi au eleza nyumba yako bora na utaftaji wetu unaowezeshwa na AI utajaribu kupata inayolingana zaidi.',
        'Start Searching': 'Anza Kutafuta',
        'Verified Listings': 'Matangazo Yaliyothibitishwa',
        'Every home is checked for authenticity and quality.': 'Kila nyumba hukaguliwa kwa uhalisi na ubora.',
        'Secure Booking': 'Uhifadhi Salama',
        'Reserve your spot with a small fee, safely and instantly.': 'Hifadhi nafasi yako kwa ada ndogo, salama na papo hapo.',
        '24/7 Support': 'Usaidizi wa 24/7',
        'Our team is here to help you every step of the way.': 'Timu yetu iko hapa kukusaidia kila hatua ya njia.',
        'Featured Homes Across Kenya': 'Nyumba Zilizoangaziwa Kote Kenya',
        'What Our Clients Say': 'Wateja Wetu Wanasema Nini',
        'Are You an Owner or Admin?': 'Wewe ni Mmiliki au Msimamizi?',
        'Owner Portal': 'Tovuti ya Mmiliki',
        'Admin Dashboard': 'Dashibodi ya Msimamizi',
        // Nav
        'Home': 'Nyumbani',
        'Search': 'Tafuta',
        'Explore': 'Gundua',
        'Neighborhoods': 'Mitaa',
        'Transport': 'Usafiri',
        'Agent Directory': 'Orodha ya Mawakala',
        'More': 'Zaidi',
        'Services': 'Huduma',
        'Find a Home': 'Tafuta Nyumba',
        'Reviews': 'Ukaguzi',
        'Marketplace': 'Sokoni',
        'Terms': 'Masharti',
        'About': 'Kuhusu',
        'About Us': 'Kuhusu Sisi',
        'Contact': 'Wasiliana',
        'Contact Us': 'Wasiliana Nasi',
        'Login': 'Ingia',
        'Register': 'Sajili',
        'Profile': 'Wasifu',
        'Logout': 'Toka',
    }
};

export const setLanguage = (lang) => {
    if (!translations[lang]) return;
    document.querySelectorAll('[data-translate]').forEach(element => {
        const key = element.dataset.translate;
        if (translations[lang][key]) {
            // Also check for placeholder attribute
            if (element.placeholder) {
                element.placeholder = translations[lang][key];
            } else {
                element.textContent = translations[lang][key];
            }
        }
    });
    localStorage.setItem('preferredLanguage', lang);
    const languageSelect = document.getElementById('language-select');
    if (languageSelect) languageSelect.value = lang;
};

document.addEventListener('DOMContentLoaded', () => {
    const languageSelect = document.getElementById('language-select');
    if (languageSelect) {
        languageSelect.addEventListener('change', (e) => {
            setLanguage(e.target.value);
        });
    }

    // Set initial language from storage or default to 'en'
    const preferredLanguage = localStorage.getItem('preferredLanguage') || 'en';
    setLanguage(preferredLanguage);
});
