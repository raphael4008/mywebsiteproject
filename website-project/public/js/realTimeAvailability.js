import apiClient from './apiClient.js';

document.addEventListener('DOMContentLoaded', () => {
    const availabilityButtons = document.querySelectorAll('.check-availability-btn');

    availabilityButtons.forEach(button => {
        button.addEventListener('click', async () => {
            const listingId = button.dataset.listingId;

            if (listingId) {
                try {
                    button.textContent = 'Checking...';
                    const response = await apiClient.request(`/listings/${listingId}/availability`);

                    if (response.available) {
                        button.textContent = 'Available';
                        button.classList.remove('btn-outline-danger');
                        button.classList.add('btn-outline-success');
                    } else {
                        button.textContent = 'Not Available';
                        button.classList.remove('btn-outline-success');
                        button.classList.add('btn-outline-danger');
                    }
                } catch (error) {
                    console.error('Error checking availability:', error);
                    button.textContent = 'Error';
                }
            }
        });
    });
});