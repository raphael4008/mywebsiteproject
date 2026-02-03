import { initNavbar } from './nav.js?v=4';
import { initFooter } from './footer.js';
import { setLanguage } from './lang.js';
import { initHome } from './home.js';
import apiClient from './apiClient.js';

// Singleton IntersectionObserver for lazy-loading images across the site
let lazyImageObserver = null;
let lazyObserverInitialized = false;

export function initLazyLoading() {
    let lazyImages = Array.from(document.querySelectorAll('img.lazy'));

    if ('IntersectionObserver' in window) {
        if (!lazyImageObserver) {
            lazyImageObserver = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        const lazyImage = entry.target;
                        const dataSrc = lazyImage.dataset.src;
                        const dataSrcset = lazyImage.dataset.srcset;
                        if (dataSrc) lazyImage.src = dataSrc;
                        if (dataSrcset) lazyImage.srcset = dataSrcset;
                        lazyImage.classList.remove('lazy');
                        lazyImageObserver.unobserve(lazyImage);
                    }
                });
            }, { rootMargin: '200px 0px', threshold: 0.01 });
        }

        // Observe any lazy images not yet observed
        lazyImages.forEach((lazyImage) => {
            if (!lazyImage.dataset.observed) {
                lazyImageObserver.observe(lazyImage);
                lazyImage.dataset.observed = '1';
            }
        });
        lazyObserverInitialized = true;
    } else {
        // Fallback for browsers without IntersectionObserver
        let active = false;

        const lazyLoad = function() {
            if (active === false) {
                active = true;

                setTimeout(function() {
                    lazyImages = Array.from(document.querySelectorAll('img.lazy'));
                    lazyImages.forEach(function(lazyImage) {
                        if ((lazyImage.getBoundingClientRect().top <= window.innerHeight && lazyImage.getBoundingClientRect().bottom >= 0) && getComputedStyle(lazyImage).display !== 'none') {
                            lazyImage.src = lazyImage.dataset.src;
                            lazyImage.classList.remove('lazy');

                            lazyImages = lazyImages.filter(function(image) {
                                return image !== lazyImage;
                            });

                            if (lazyImages.length === 0) {
                                document.removeEventListener('scroll', lazyLoad);
                                window.removeEventListener('resize', lazyLoad);
                                window.removeEventListener('orientationchange', lazyLoad);
                            }
                        }
                    });

                    active = false;
                }, 200);
            }
        };

        document.addEventListener('scroll', lazyLoad);
        window.addEventListener('resize', lazyLoad);
        window.addEventListener('orientationchange', lazyLoad);
    }
}

document.addEventListener("DOMContentLoaded", async function() {
    await initNavbar();
    initFooter();
    initHome();

    // Back to Top Button Logic
    const backToTopBtn = document.getElementById('backToTopBtn');
    if (backToTopBtn && backToTopBtn.style) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTopBtn.style.display = 'block';
            } else {
                backToTopBtn.style.display = 'none';
            }
        });

        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Cookie Consent Logic
    const cookieBanner = document.createElement('div');
    cookieBanner.className = 'cookie-consent-banner';
    cookieBanner.innerHTML = `
        <p>We use cookies to improve your experience. By using our site, you agree to our use of cookies.</p>
        <button id="acceptCookies" class="btn btn-sm btn-primary">Accept</button>
    `;
    
    if (!localStorage.getItem('cookiesAccepted')) {
        document.body.appendChild(cookieBanner);
        // Small delay to allow CSS transition if added
        setTimeout(() => {
            if (cookieBanner && cookieBanner.style) {
                cookieBanner.style.display = 'flex';
            }
        }, 100);
        
        document.getElementById('acceptCookies').addEventListener('click', () => {
            localStorage.setItem('cookiesAccepted', 'true');
            if (cookieBanner && cookieBanner.style) {
                cookieBanner.style.display = 'none';
            }
        });
    }

    initLazyLoading();

    // Function to update homepage stats
    async function updateHomepageStats() {
        console.log('Fetching homepage stats...');
        const listingsStat = document.getElementById('stat-listings');
        if (!listingsStat) return; // Only run on homepage

        try {
            const stats = await apiClient.request('/stats');
            console.log('Stats received:', stats);

            const familiesStat = document.getElementById('stat-families');
            const citiesStat = document.getElementById('stat-cities');

            // Animate numbers
            const animateValue = (element, end, duration) => {
                let start = 0;
                const range = end - start;
                let current = start;
                const increment = end > start ? 1 : -1;
                const stepTime = Math.abs(Math.floor(duration / range));
                const timer = setInterval(() => {
                    current += increment;
                    element.textContent = current;
                    if (current == end) {
                        clearInterval(timer);
                    }
                }, stepTime);
            };

            if (stats.listings) {
                animateValue(listingsStat, stats.listings, 2000);
            }
            if (stats.users && familiesStat) {
                animateValue(familiesStat, stats.users, 2000);
            }
            if (stats.cities && citiesStat) {
                animateValue(citiesStat, stats.cities, 2000);
            }
        } catch (error) {
            console.error('Failed to fetch homepage stats:', error);
            // Don't overwrite the static values if the API fails
        }
    }

    updateHomepageStats();
});