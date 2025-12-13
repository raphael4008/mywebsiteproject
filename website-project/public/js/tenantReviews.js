import apiClient from './apiClient.js';

document.addEventListener('DOMContentLoaded', () => {
    const reviewForm = document.getElementById('reviewForm');
    const reviewsList = document.getElementById('reviewsList');

    if (reviewForm) {
        reviewForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const formData = new FormData(reviewForm);
            const reviewData = Object.fromEntries(formData.entries());

            try {
                const response = await apiClient.request('/reviews', 'POST', reviewData);

                if (response.success) {
                    const newReview = document.createElement('div');
                    newReview.classList.add('review-card');
                    newReview.innerHTML = `
                        <h4>${reviewData.name}</h4>
                        <p>${reviewData.comment}</p>
                        <small>Rating: ${reviewData.rating} / 5</small>
                    `;
                    reviewsList.prepend(newReview);
                    reviewForm.reset();
                }
            } catch (error) {
                console.error('Error submitting review:', error);
                alert('Failed to submit review. Please try again.');
            }
        });
    }
});