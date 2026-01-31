<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ title }}</title>
    <link rel="stylesheet" href="/dist/styles.css">
    <link rel="stylesheet" href="/dist/homepage.css">
</head>
<body>
    <header id="navbar-container">
        <nav class="main-nav">
            <a class="navbar-brand" href="/">House Hunting</a>
            <div class="nav-links">
                <a class="nav-link" href="/">Home</a>
                <a class="nav-link" href="/listings">Listings</a>
                <a class="nav-link" href="/about">About</a>
                <a class="nav-link" href="/contact">Contact</a>
            </div>
            <ul id="auth-links" class="navbar-nav">
                <!-- Auth links will be injected here by nav.js -->
            </ul>
            <div class="nav-extras">
                <button id="theme-toggle">🌓</button>
                <select id="language-select">
                    <option value="en">English</option>
                    <option value="es">Español</option>
                </select>
            </div>
        </nav>
    </header>

    <main>
        {{ content }}
    </main>

    <footer id="footer-container">
        <div class="footer-content">
            <p>&copy; 2024 House Hunting. All rights reserved.</p>
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </footer>

    <button id="backToTopBtn" class="back-to-top" title="Go to top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script src="/dist/bundle.js"></script>
</body>
</html>
