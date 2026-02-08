<!-- Hero Section -->
<div class="container-fluid hero-section"
    style="position: relative; height: 60vh; background: url('{{ basePath }}/images/b.jpg') no-repeat center center; background-size: cover;">
    <div class="hero-overlay"
        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5);">
    </div>
    <div class="container hero-content text-center"
        style="position: relative; z-index: 2; top: 50%; transform: translateY(-50%); color: white;">
        <h1 class="display-4 fw-bold">Your Next Home Awaits</h1>
        <p class="lead">Discover the perfect place to live with our powerful and intuitive search tools.</p>
        <div class="mt-4">
            <a href="{{ basePath }}/listings" class="btn btn-primary btn-lg me-2">Explore Listings</a>
            <a href="{{ basePath }}/about" class="btn btn-outline-light btn-lg">Learn More</a>
        </div>
    </div>
</div>

<!-- AI-Powered Search Section -->
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Search with Natural Language</h2>
                    <form id="ai-search-form" class="d-flex">
                        <input type="text" id="ai-search-input" class="form-control form-control-lg"
                            placeholder="e.g., 'A spacious 2-bedroom apartment near Yaya Centre'">
                        <button type="submit" id="ai-search-btn" class="btn btn-primary btn-lg ms-2">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Featured Properties Section -->
<div class="container my-5">
    <h2 class="text-center mb-4">Featured Properties</h2>
    <div class="row g-4" id="featured-properties-container">
        <!-- Featured properties will be dynamically loaded here -->
    </div>
</div>

<!-- How It Works Section -->
<div class="container-fluid bg-light py-5">
    <div class="container">
        <h2 class="text-center mb-5">Why Choose Us?</h2>
        <div class="row text-center">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <i class="fas fa-home fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Extensive Listings</h5>
                        <p class="card-text">A wide variety of properties to suit every need and budget.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <i class="fas fa-user-shield fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Verified Agents</h5>
                        <p class="card-text">Connect with trusted and verified real estate agents.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Dedicated Support</h5>
                        <p class="card-text">Our team is here to assist you every step of the way.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Call to Action Section -->
<div class="container my-5">
    <div class="row">
        <div class="col text-center">
            <div class="card shadow-lg">
                <div class="card-body">
                    <h2 class="card-title">List Your Property with Us</h2>
                    <p class="card-text">Reach a wide audience of potential buyers and renters by listing your property
                        on our platform.</p>
                    <a href="{{ basePath }}/register" class="btn btn-primary btn-lg">Become a Partner</a>
                </div>
            </div>
        </div>
    </div>
</div>