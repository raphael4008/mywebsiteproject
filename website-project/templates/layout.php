<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ title }}</title>
    <link rel="stylesheet" href="{{ basePath }}/dist/styles.css">
    <?php if ($title === 'HouseHunter - Find Your Dream Home'): ?>
    <link rel="stylesheet" href="{{ basePath }}/css/home.css">
    <?php endif; ?>
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

    <footer id="footer-container" class="bg-dark text-white pt-5 pb-4">
    <div class="container py-4">
        <div class="row gy-4">
            <div class="col-lg-4 col-md-6">
                <h5 class="mb-3 fw-bold text-white"><i class="fas fa-home text-primary me-2"></i>HouseHunter</h5>
                <p class="small text-muted mb-4" data-translate="Discover Your Next Home Across Kenya. Listings in all major cities.">Discover Your Next Home Across Kenya. Listings in all major cities. We make finding your dream home simple, secure, and fast.</p>
                <div class="social-links">
                    <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <h5 class="mb-3" data-translate="Portals">Portals</h5>
                <ul class="list-unstyled">
                    <li><a href="/owner/dashboard" data-translate="Owner Portal">Owner Portal</a></li>
                    <li><a href="/admin/dashboard" data-translate="Admin Dashboard">Admin Dashboard</a></li>
                    <li><a href="/agents" data-translate="Agent Directory">Agent Directory</a></li>
                </ul>
            </div>
            <div class="col-lg-4 col-md-6">
                <h5 class="mb-3" data-translate="Newsletter">Newsletter</h5>
                <p class="small text-muted">Subscribe to get the latest listings and market news.</p>
                <form class="footer-newsletter d-flex gap-2">
                    <input type="email" class="form-control" placeholder="Your email">
                    <button type="submit" class="btn btn-primary">Join</button>
                </form>
            </div>
            <div class="col-lg-2 col-md-6">
                <h5 class="mb-3" data-translate="Legal">Legal</h5>
                <ul class="list-unstyled">
                    <li><a href="/terms" data-translate="Terms">Terms of Service</a></li>
                    <li><a href="/privacy-policy" data-translate="Privacy">Privacy Policy</a></li>
                    <li><a href="/contact" data-translate="Support">Support</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom mt-4">
            <div class="container d-flex flex-wrap justify-content-between align-items-center">
                <p class="small mb-0 text-muted">&copy; 2025 HouseHunter. All rights reserved.</p>
                <p class="small mb-0 text-muted">Designed with <i class="fas fa-heart text-danger"></i> in Kenya</p>
            </div>
        </div>
    </div>
</footer>

    <button id="backToTopBtn" class="back-to-top" title="Go to top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        window.basePath = '{{ basePath }}';
    </script>
    <script src="{{ basePath }}/dist/bundle.js"></script>
    <?php if ($title === 'HouseHunter - Find Your Dream Home'): ?>
    <script src="{{ basePath }}/js/home.js"></script>
    <?php endif; ?>
</body>
</html>
