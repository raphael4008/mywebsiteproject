import apiClient from './apiClient.js';

document.addEventListener('DOMContentLoaded', () => {
    const agreementForm = document.getElementById('agreementForm');

    if (agreementForm) {
        agreementForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const formData = new FormData(agreementForm);
            const agreementData = Object.fromEntries(formData.entries());

            try {
                const response = await apiClient.request('/agreements', 'POST', agreementData);

                if (response.success) {
                    alert('Rental agreement generated successfully!');
                    window.location.href = response.downloadUrl; // Redirect to download the agreement
                }
            } catch (error) {
                console.error('Error generating rental agreement:', error);
                alert('Failed to generate rental agreement. Please try again.');
            }
        });
    }
});