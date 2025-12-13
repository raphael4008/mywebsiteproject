import { setLanguage } from './lang.js';
import { loadAndCacheHTML } from './html-loader.js';
import apiClient from './apiClient.js';

document.addEventListener('DOMContentLoaded', async () => {
    const navElement = document.querySelector('.navbar');
    if (!navElement) return;

    const navHtml = await loadAndCacheHTML('includes/navbar.html', navElement, 'navbarHTML');

    if (navHtml) {
        updateAuthLinks();
        initializeThemeToggle();
        setActiveNavLink();
    }
});

async function updateAuthLinks() {
    const authLinksContainer = document.getElementById('auth-links');
    if (!authLinksContainer) return;

    const token = localStorage.getItem('token');

    if (token) {
        // User is logged in
        try {
            const user = await apiClient.request('/users/me');
            authLinksContainer.innerHTML = `
                <li class="nav-item">
                    <a class="nav-link" href="profile" data-translate="Profile">Welcome, ${user.data.username}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" id="logout-button" data-translate="Logout">Logout</a>
                </li>
            `;
            document.getElementById('logout-button').addEventListener('click', (e) => {
                e.preventDefault();
                localStorage.removeItem('token');
                window.location.href = '/login';
            });
        } catch (error) {
            console.error('Failed to get user profile', error);
            authLinksContainer.innerHTML = `
                <li class="nav-item">
                    <a class="nav-link" href="profile" data-translate="Profile">Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" id="logout-button" data-translate="Logout">Logout</a>
                </li>
            `;
            document.getElementById('logout-button').addEventListener('click', (e) => {
                e.preventDefault();
                localStorage.removeItem('token');
                window.location.href = '/login';
            });
        }
    } else {
        // User is logged out
        authLinksContainer.innerHTML = `
            <li class="nav-item"><a class="nav-link" href="login" data-translate="Login">Login</a></li>
            <li class="nav-item"><a class="nav-link" href="register" data-translate="Register">Register</a></li>
        `;
    }

    // Re-apply language to the newly added elements
    const preferredLanguage = localStorage.getItem('preferredLanguage') || 'en';
    setLanguage(preferredLanguage);
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

    // Set initial theme from storage
    const savedTheme = localStorage.getItem('theme') || 'light';
    setTheme(savedTheme);
}

function setActiveNavLink() {
    const currentPage = window.location.pathname;
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link, .navbar-nav .dropdown-item');

    navLinks.forEach(link => {
        const linkPage = link.getAttribute('href');

        if ((currentPage === '/' && linkPage === '/') || (linkPage !== '/' && currentPage.endsWith(linkPage))) {
            link.classList.add('active');
            const dropdown = link.closest('.dropdown');
            if (dropdown) {
                dropdown.querySelector('.dropdown-toggle').classList.add('active');
            }
        }
    });
}