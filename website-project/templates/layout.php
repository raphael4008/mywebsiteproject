<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ title }}</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Roboto:wght@400;500&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ basePath }}/css/styles.css">
    <?php if ($title === 'HouseHunter - Find Your Dream Home'): ?>
    <link rel="stylesheet" href="{{ basePath }}/css/home.css">
    <?php
endif; ?>
</head>

<body>
    <header id="navbar-container">
        <nav class="navbar navbar-expand-lg navbar-light fixed-top main-nav">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="{{ basePath }}/">
                    <i class="fas fa-home text-primary me-2"></i>House Hunting
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto align-items-center">
                        <li class="nav-item"><a class="nav-link" href="{{ basePath }}/">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ basePath }}/listings">Listings</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ basePath }}/about">About</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ basePath }}/contact">Contact</a></li>
                        <div id="auth-links" class="d-flex align-items-center ms-lg-3 gap-2">
                            <!-- Auth links injected by JS -->
                        </div>
                        <li class="nav-item ms-lg-3">
                            <button id="theme-toggle" class="btn btn-link nav-link p-0"><i
                                    class="fas fa-moon"></i></button>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="flex-shrink-0">
        {{ content }}
    </main>

    <footer id="footer-container" class="bg-dark text-white pt-5 mt-auto">
        <div class="container py-4">
            <div class="row gy-4">
                <div class="col-lg-4 col-md-6">
                    <h5 class="mb-3 fw-bold text-white"><i class="fas fa-home text-primary me-2"></i>HouseHunter</h5>
                    <p class="small text-muted mb-4">Discover Your Next Home Across Kenya. Listings in all major cities.
                        We make finding your dream home simple, secure, and fast.</p>
                    <div class="social-links">
                        <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5 class="mb-3">Portals</h5>
                    <ul class="list-unstyled">
                        <li><a href="{{ basePath }}/owner/dashboard">Owner Portal</a></li>
                        <li><a href="{{ basePath }}/admin/dashboard">Admin Dashboard</a></li>
                        <li><a href="{{ basePath }}/agents">Agent Directory</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h5 class="mb-3">Newsletter</h5>
                    <p class="small text-muted">Subscribe to get the latest listings and market news.</p>
                    <form class="footer-newsletter d-flex gap-2">
                        <input type="email" class="form-control" placeholder="Your email">
                        <button type="submit" class="btn btn-primary">Join</button>
                    </form>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5 class="mb-3">Legal</h5>
                    <ul class="list-unstyled">
                        <li><a href="{{ basePath }}/terms">Terms of Service</a></li>
                        <li><a href="{{ basePath }}/privacy-policy">Privacy Policy</a></li>
                        <li><a href="{{ basePath }}/contact">Support</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom mt-4 pt-3 border-top border-secondary">
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
    <script src="{{ basePath }}/js/main.js?v=<?php echo time(); ?>" type="module"></script>
</body>

</html>