import apiClient from './apiClient.js';

async function loadReviews() {
    // Support both homepage and features page containers
    const homeContainer = document.getElementById('reviews-list');
    const featuresContainer = document.getElementById('reviewsList');
    const container = homeContainer || featuresContainer;

    if (!container) return;

    try {
        const reviews = await apiClient.request('/reviews');
        
        // If on homepage, limit to 3 reviews
        const displayReviews = homeContainer ? reviews.slice(0, 3) : reviews;

        if (displayReviews.length === 0) {
            container.innerHTML = '<div class="col-12 text-center"><p class="text-muted">No reviews yet.</p></div>';
            return;
        }

        container.innerHTML = displayReviews.map(review => `
            <div class="${homeContainer ? 'col-md-4' : 'testimonial-card'}" ${homeContainer ? 'data-aos="fade-up"' : ''}>
                <div class="card h-100 border-0 shadow-sm p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 48px; height: 48px; font-size: 1.2rem;">
                                ${review.reviewer_name ? review.reviewer_name.charAt(0).toUpperCase() : 'A'}
                            </div>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-0 fw-bold">${review.reviewer_name || 'Anonymous'}</h6>
                            <div class="text-warning small">
                                ${Array(5).fill(0).map((_, i) => i < review.rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>').join('')}
                            </div>
                        </div>
                    </div>
                    <p class="card-text text-muted small fst-italic">"${review.comment}"</p>
                </div>
            </div>
        `).join('');

    } catch (error) {
        console.error('Error loading reviews:', error);
        container.innerHTML = '<div class="col-12 text-center"><p class="text-danger">Unable to load reviews.</p></div>';
    }
}

document.addEventListener('DOMContentLoaded', loadReviews);