// Custom JavaScript for homepage interactions and animations

document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();

            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

    // Example for a dynamic hero section CTA animation (optional, can be done with CSS)
    const heroCta = document.querySelector('.hero-ctas');
    if (heroCta) {
        // You can add more complex JS animations here if needed,
        // but AOS handles most of the scroll animations.
    }

    // Scroll-down indicator functionality (if not handled purely by CSS/AOS)
    const scrollIndicator = document.querySelector('.scroll-down-indicator');
    if (scrollIndicator) {
        scrollIndicator.addEventListener('click', function() {
            // Smooth scroll to the next section (e.g., features)
            document.querySelector('#features').scrollIntoView({
                behavior: 'smooth'
            });
        });
    }

    // Note: AOS.init() and CountUp.js (via jQuery.counterUp) are initialized in index.php
    // This file is for additional custom JS logic.
});
