import apiClient from './apiClient.js';

function renderStars(rating) {
    let starsHtml = '';
    for (let i = 1; i <= 5; i++) {
        starsHtml += `<span class="star ${i <= rating ? 'selected' : ''}">★</span>`;
    }
    return `<div class="star-rating-display">${starsHtml}</div>`;
}

async function renderReviews() {
    const container = document.getElementById('reviewsList');
    if (!container) return; // Added check for container existence

    try {
        const reviews = await apiClient.request('/reviews');
        
        if (!reviews || reviews.length === 0) {
            container.innerHTML = '<p>No reviews have been submitted yet.</p>';
            return;
        }

        container.innerHTML = reviews.map(r => `
            <div class="testimonial-card">
                ${renderStars(r.rating)}
                <p>"${r.comment}"</p>
                <span>- ${r.reviewer}, ${new Date(r.created_at).toLocaleDateString()}</span>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error fetching reviews:', error);
        container.innerHTML = '<p>Could not load reviews. Please try again later.</p>';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    renderReviews();

    const reviewForm = document.getElementById('reviewForm');
    const responseContainer = document.getElementById('review-response');

    // Star rating logic
    const stars = document.querySelectorAll('.star-rating .star');
    const ratingInput = document.getElementById('rating');

    stars.forEach(star => {
        star.addEventListener('click', () => {
            ratingInput.value = star.dataset.value;
            stars.forEach(s => {
                s.classList.toggle('selected', s.dataset.value <= ratingInput.value);
            });
        });

        star.addEventListener('mouseover', () => {
            stars.forEach(s => {
                s.style.color = s.dataset.value <= star.dataset.value ? '#ffc107' : '#ccc';
            });
        });

        star.addEventListener('mouseout', () => {
            stars.forEach(s => {
                s.style.color = s.dataset.value <= ratingInput.value ? '#ffc107' : '#ccc';
            });
        });
    });

    if (reviewForm) {
        reviewForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitButton = reviewForm.querySelector('button[type="submit"]');
            const formData = new FormData(reviewForm);
            const data = {
                reviewer: formData.get('reviewerName'),
                comment: formData.get('comment'),
                rating: formData.get('rating'),
            };

            submitButton.disabled = true;
            submitButton.textContent = 'Submitting...';
            responseContainer.textContent = '';

            try {
                const result = await apiClient.request('/reviews', 'POST', data);
                responseContainer.innerHTML = `<span class="text-success">${result.message || 'Thank you for your review!'}</span>`;
                // Reset stars
                ratingInput.value = 0;
                stars.forEach(s => s.classList.remove('selected'));

                reviewForm.reset();
                renderReviews(); // Re-render reviews to show the new one
            } catch (error) {
                responseContainer.innerHTML = `<span class="text-danger">Error: ${error.message}</span>`;
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = 'Submit Review';
            }
        });
    }
});
