<main class="container py-5 features-page">
    <ul class="nav nav-tabs features-nav" id="featuresTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab" aria-controls="reviews" aria-selected="true" data-translate="Reviews">Reviews</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="services-tab" data-bs-toggle="tab" data-bs-target="#services" type="button" role="tab" aria-controls="services" aria-selected="false" data-translate="Our Services">Our Services</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="marketplace-tab" data-bs-toggle="tab" data-bs-target="#marketplace" type="button" role="tab" aria-controls="marketplace" aria-selected="false" data-translate="Marketplace">Marketplace</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="terms-tab" data-bs-toggle="tab" data-bs-target="#terms" type="button" role="tab" aria-controls="terms" aria-selected="false" data-translate="Terms of Service">Terms of Service</button>
        </li>
    </ul>

    <div class="tab-content" id="featuresTabContent">
        <!-- Reviews Section -->
        <div class="tab-pane fade show active" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
            <section class="text-center mb-5" data-aos="fade-up">
                <h1 class="display-4 fw-bold" data-translate="What Our Users Say">What Our Users Say</h1>
                <p class="lead text-muted" data-translate="Real feedback from happy renters and landlords.">Real feedback from happy renters and landlords.</p>
            </section>
            <section class="mb-5" data-aos="fade-up" data-aos-delay="100">
                <div class="testimonial-grid" id="reviewsList">
                    <!-- Reviews will be loaded here by the script below -->
                </div>
                <div class="text-center mt-4">
                    <button id="loadMoreReviews" class="btn btn-primary">Load More</button>
                </div>
            </section>
            <hr class="my-5">
            <section class="bg-light p-5 rounded-3" data-aos="fade-up" data-aos-delay="200">
                <h2 class="text-center mb-4" data-translate="Share Your Experience">Share Your Experience</h2>
                <form id="reviewForm" class="mx-auto" style="max-width: 600px;">
                    <div class="star-rating mb-3">
                        <span class="star" data-value="1">★</span><span class="star" data-value="2">★</span><span class="star" data-value="3">★</span><span class="star" data-value="4">★</span><span class="star" data-value="5">★</span>
                    </div>
                    <input type="hidden" name="rating" id="rating" value="0">
                    <div class="mb-3">
                        <label for="reviewerName" class="form-label" data-translate="Your Name">Your Name</label>
                        <input type="text" class="form-control" id="reviewerName" name="reviewerName" required>
                    </div>
                    <div class="mb-3">
                        <label for="reviewComment" class="form-label" data-translate="Your Review">Your Review</label>
                        <textarea class="form-control" id="reviewComment" name="reviewComment" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" data-translate="Submit Review">Submit Review</button>
                </form>
                <div id="review-response" class="mt-3 text-center"></div>
            </section>
            
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const reviewsList = document.getElementById('reviewsList');
                const loadMoreReviewsBtn = document.getElementById('loadMoreReviews');
                let page = 1;
            
                function loadReviews() {
                    fetch(`/api/reviews?page=${page}`)
                        .then(response => response.json())
                        .then(reviews => {
                            reviews.forEach(review => {
                                const reviewCard = document.createElement('div');
                                reviewCard.classList.add('testimonial-card');
            
                                let stars = '';
                                for (let i = 0; i < 5; i++) {
                                    if (i < review.rating) {
                                        stars += '<i class="fas fa-star"></i>';
                                    } else {
                                        stars += '<i class="far fa-star"></i>';
                                    }
                                }
            
                                reviewCard.innerHTML = `
                                    <div class="stars">${stars}</div>
                                    <p>"${review.comment}"</p>
                                    <h4>${review.name}</h4>
                                `;
                                reviewsList.appendChild(reviewCard);
                            });
                            page++;
                        });
                }
            
                loadReviews();
            
                loadMoreReviewsBtn.addEventListener('click', loadReviews);
            });
            </script>
                    </div>

        <!-- Services Section -->
        <div class="tab-pane fade" id="services" role="tabpanel" aria-labelledby="services-tab">
            <section class="text-center py-5" data-aos="fade-in">
                <h1 class="display-4 fw-bold" data-translate="Our Services">Our Services</h1>
                <p class="lead text-muted" data-translate="We offer a range of services to make your house hunting experience seamless and enjoyable.">We offer a range of services to make your house hunting experience seamless and enjoyable.</p>
            </section>
            <div class="row g-4 py-5 row-cols-1 row-cols-lg-3">
                <div class="col text-center" data-aos="fade-up" data-aos-delay="100">
                    <div class="p-4 border rounded shadow-sm service-card">
                        <h3 class="fs-2" data-translate="Secure Online Reservations">Secure Online Reservations</h3>
                        <p data-translate="Found your perfect home? Reserve it instantly and securely on our platform with a small, refundable fee. Your dream home will be held for you while you finalize details.">Found your perfect home? Reserve it instantly and securely on our platform with a small, refundable fee. Your dream home will be held for you while you finalize details.</p>
                    </div>
                </div>
                <div class="col text-center" data-aos="fade-up" data-aos-delay="200">
                    <div class="p-4 border rounded shadow-sm service-card">
                        <h3 class="fs-2" data-translate="Mover & Transport Services">Mover & Transport Services</h3>
                        <p data-translate="Finalize your lease and payment. Use our transport partners to move into your new home with ease.">Finalize your lease and payment. Use our transport partners to move into your new home with ease.</p>
                    </div>
                </div>
                <div class="col text-center" data-aos="fade-up" data-aos-delay="300">
                    <div class="p-4 border rounded shadow-sm service-card">
                        <h3 class="fs-2" data-translate="Cleaning Services">Cleaning Services</h3>
                        <p data-translate="Move into a sparkling clean home. Book professional pre-move or post-move cleaning.">Move into a sparkling clean home. Book professional pre-move or post-move cleaning.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Marketplace Section -->
        <div class="tab-pane fade" id="marketplace" role="tabpanel" aria-labelledby="marketplace-tab">
            <section class="text-center mb-5" data-aos="fade-in">
                <h1 class="display-4 fw-bold" data-translate="Get Settled Marketplace">Get Settled Marketplace</h1>
                <p class="lead text-muted" data-translate="Everything you need to make your new house a home.">Everything you need to make your new house a home.</p>
            </section>
            <div class="row g-4" id="marketplace-grid" data-aos="fade-up">
                </div>
        </div>

        <!-- Terms of Service Section -->
        <div class="tab-pane fade" id="terms" role="tabpanel" aria-labelledby="terms-tab">
            <section class="static-content py-5" data-aos="fade-in">
                <h1 data-translate="Terms of Service">Terms of Service</h1>
                <p data-translate="Welcome to HouseHunter. By using our services, you agree to the following terms and conditions.">Welcome to HouseHunter. By using our services, you agree to the following terms and conditions.</p>
                <p><em>Last Updated: November 17, 2025</em></p>
                <h2 data-translate="1. Acceptance of Terms">1. Acceptance of Terms</h2>
                <p data-translate="By accessing or using our website, you agree to be bound by these Terms of Service and our Privacy Policy. If you do not agree, you may not use our services.">By accessing or using our website, you agree to be bound by these Terms of Service and our Privacy Policy. If you do not agree, you may not use our services.</p>
                <h2 data-translate="2. Service Description">2. Service Description</h2>
                <p data-translate="HouseHunter provides an online platform to connect individuals looking for homes ('Users') with property owners and managers ('Owners'). We facilitate the discovery, reservation, and payment process. We are not a real estate broker and do not own or manage the properties listed on our site.">HouseHunter provides an online platform to connect individuals looking for homes ('Users') with property owners and managers ('Owners'). We facilitate the discovery, reservation, and payment process. We are not a real estate broker and do not own or manage the properties listed on our site.</p>
                <h2 data-translate="3. User Accounts & Responsibilities">3. User Accounts & Responsibilities</h2>
                <p data-translate="You must create an account to access certain features. You are responsible for maintaining the confidentiality of your account information and for all activities that occur under your account. You agree to provide accurate and complete information.">You must create an account to access certain features. You are responsible for maintaining the confidentiality of your account information and for all activities that occur under your account. You agree to provide accurate and complete information.</p>
            </section>
        </div>
    </div>
</main>
<div id="footer-container"></div>

<!-- Modal -->
<div class="modal fade" id="serviceModal" tabindex="-1" aria-labelledby="serviceModalLabel" aria-hidden="true">
    </div>

<script src="js/main.js" type="module"></script>
<script src="js/auth.js" type="module"></script>
<script src="js/nav.js" type="module"></script>
<script src="js/lang.js" type="module"></script>
<script src="js/footer.js" type="module"></script>
<script src="js/reviews.js" type="module"></script>
<script src="js/marketplace.js" type="module"></script>
