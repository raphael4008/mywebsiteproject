<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Features – HouseHunter</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/features.css">
</head>
<body>
    <div id="navbar-container"></div>

    <header class="bg-primary text-white py-5 text-center" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/e.jpg') center/cover;">
        <div class="container">
            <h1 class="display-4 fw-bold">Website Features</h1>
            <p class="lead">Discover the tools that make finding a home easier.</p>
        </div>
    </header>

    <main class="container py-5">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center border-0 shadow-sm">
                    <div class="card-body">
                        <div class="feature-icon bg-primary text-white mb-3">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3 class="card-title h4">Advanced Search</h3>
                        <p class="card-text">Filter listings by location, price, property type, and more. Use our AI search to find exactly what you're looking for with natural language.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center border-0 shadow-sm">
                    <div class="card-body">
                        <div class="feature-icon bg-primary text-white mb-3">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h3 class="card-title h4">Favorites & Wishlists</h3>
                        <p class="card-text">Save your favorite properties to a wishlist so you can easily find them later. Get notifications if prices change.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center border-0 shadow-sm">
                    <div class="card-body">
                        <div class="feature-icon bg-primary text-white mb-3">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <h3 class="card-title h4">Side-by-Side Comparison</h3>
                        <p class="card-text">Select up to three properties to compare their features, amenities, and price in a simple, easy-to-read table.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center border-0 shadow-sm">
                    <div class="card-body">
                        <div class="feature-icon bg-primary text-white mb-3">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h3 class="card-title h4">Verified Listings</h3>
                        <p class="card-text">Look for the "Verified" badge to see properties that have been vetted by our team for your peace of mind.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center border-0 shadow-sm">
                    <div class="card-body">
                        <div class="feature-icon bg-primary text-white mb-3">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3 class="card-title h4">Direct Agent Contact</h3>
                        <p class="card-text">Contact property agents directly through our secure messaging system to ask questions or schedule a viewing.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center border-0 shadow-sm">
                    <div class="card-body">
                        <div class="feature-icon bg-primary text-white mb-3">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h3 class="card-title h4">Secure Online Payments</h3>
                        <p class="card-text">Pay your rent or deposit securely through our integrated payment system, with support for M-Pesa and credit cards.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div id="footer-container"></div>

    <script src="js/main.js" type="module"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            fetch('/includes/navbar.html')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('navbar-container').innerHTML = data;
                });
            fetch('/includes/footer.html')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('footer-container').innerHTML = data;
                });
        });
    </script>
