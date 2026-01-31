import { setLanguage } from './lang.js?v=4';
import { isLoggedIn, getUser, logout } from './auth.js?v=4';

export async function initNavbar() {
    const navbarContainer = document.getElementById('navbar-container');
    if (!navbarContainer) {
        console.error('Navbar container not found');
        return;
    }

    try {
        const response = await fetch('includes/navbar.html');
        const navbarHtml = await response.text();
        navbarContainer.innerHTML = navbarHtml;
        
        // Now that the navbar is loaded, initialize its components
        await updateAuthLinks();
        initializeThemeToggle();
        setActiveNavLink();
        
        // Initialize language selector listener
        const languageSelect = document.getElementById('language-select');
        if (languageSelect) {
            const preferredLanguage = localStorage.getItem('preferredLanguage') || 'en';
            languageSelect.value = preferredLanguage;
            languageSelect.addEventListener('change', async (e) => {
                await setLanguage(e.target.value);
            });
        }
    } catch (error) {
        console.error('Failed to load navbar:', error);
    }
}

async function updateAuthLinks() {
    const authLinksContainer = document.getElementById('auth-links');
    if (!authLinksContainer) return;

    if (isLoggedIn()) {
        const user = getUser();
        const userName = user ? user.name || user.username : 'User';
        authLinksContainer.innerHTML = `
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Welcome, ${userName}
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                    <li><a class="dropdown-item" href="my-listings.php">My Listings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" id="logout-button">Logout</a></li>
                </ul>
            </li>
        `;

        document.getElementById('logout-button').addEventListener('click', (e) => {
            e.preventDefault();
            logout();
        });
    } else {
        // User is logged out
        authLinksContainer.innerHTML = `
            <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
            <li class="nav-item"><a class="nav-link btn btn-primary" href="register.php">Register</a></li>
        `;
    }

    // Re-apply language to the newly added elements
    const preferredLanguage = localStorage.getItem('preferredLanguage') || 'en';
    await setLanguage(preferredLanguage, authLinksContainer);
}

function initializeThemeToggle() {
    const themeToggle = document.getElementById('theme-toggle');
    if (!themeToggle) return;

    const setTheme = (theme) => {
        if (theme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            themeToggle.innerHTML = '☀️'; // Sun icon for light mode
        } else {
            document.documentElement.removeAttribute('data-theme');
            themeToggle.innerHTML = '🌓'; // Moon icon for dark mode
        }
        localStorage.setItem('theme', theme);
    };

    themeToggle.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        if (currentTheme === 'dark') {
            setTheme('light');
        } else {
            setTheme('dark');
        }
    });

    const savedTheme = localStorage.getItem('theme') || 'light';
    setTheme(savedTheme);
}

function setActiveNavLink() {
    const currentPage = window.location.pathname;
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link, .navbar-nav .dropdown-item');

    navLinks.forEach(link => {
        const linkHref = link.getAttribute('href');
        if (!linkHref) return;

        // Normalize paths for comparison
        const absoluteLink = new URL(linkHref, window.location.origin).pathname;
        const absolutePage = currentPage.endsWith('/') ? currentPage + 'index.php' : currentPage;

        if (absoluteLink === window.location.origin + absolutePage) {
            link.classList.add('active');
            const dropdown = link.closest('.dropdown');
            if (dropdown) {
                dropdown.querySelector('.dropdown-toggle').classList.add('active');
            }
        }
    });
}
